
<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

uses()->beforeEach(function () {
    $this->withoutMiddleware(
        \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class
    );
});

function fakeOllamaProposal(array $proposal, int $status = 200): void
{
    Http::fake([
        '*' => Http::response([
            'message' => [
                'content' => json_encode($proposal),
            ],
        ], $status),
    ]);
}

test('an authenticated user can generate a task proposal without saving it', function () {
    $user = User::factory()->create();

    fakeOllamaProposal([
        'title' => 'Complete API tests',
        'description' => 'Write and run authentication tests.',
    ]);

    $response = $this->actingAs($user)->postJson(
        route('dashboard.ai.task-proposals.store'),
        ['prompt' => 'Help me complete my API tests']
    );

    $response->assertOk()
        ->assertJsonPath('proposal.title', 'Complete API tests')
        ->assertJsonPath(
            'proposal.description',
            'Write and run authentication tests.'
        )
        ->assertJsonPath('proposal.status', 'pending')
        ->assertJsonPath(
            'message',
            'Review the proposed task before saving.'
        );

    expect(Task::count())->toBe(0);

    Http::assertSent(function ($request) {
        return str_ends_with($request->url(), '/api/chat')
            && $request['model'] === 'llama3.2:latest'
            && $request['stream'] === false;
    });
});

test('guests cannot generate task proposals', function () {
    $response = $this->postJson(
        route('dashboard.ai.task-proposals.store'),
        ['prompt' => 'Help me plan a task']
    );

    $response->assertUnauthorized();

    Http::assertNothingSent();

    expect(Task::count())->toBe(0);
});

test('task proposal generation validates the prompt', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(
        route('dashboard.ai.task-proposals.store'),
        ['prompt' => '']
    );

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['prompt']);

    Http::assertNothingSent();

    expect(Task::count())->toBe(0);
});

test('an unavailable AI provider does not create a task', function () {
    $user = User::factory()->create();

    Http::fake([
        '*' => Http::response(['error' => 'Model unavailable'], 500),
    ]);

    $response = $this->actingAs($user)->postJson(
        route('dashboard.ai.task-proposals.store'),
        ['prompt' => 'Help me plan a task']
    );

    $response->assertStatus(503)
        ->assertJsonStructure(['message']);

    expect(Task::count())->toBe(0);
});

test('invalid AI output is rejected without creating a task', function () {
    $user = User::factory()->create();

    fakeOllamaProposal([
        'title' => str_repeat('x', 201),
        'description' => 'A description',
    ]);

    $response = $this->actingAs($user)->postJson(
        route('dashboard.ai.task-proposals.store'),
        ['prompt' => 'Help me plan a task']
    );

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['proposal']);

    expect(Task::count())->toBe(0);
});

test('malformed AI JSON is handled safely', function () {
    $user = User::factory()->create();

    Http::fake([
        '*' => Http::response([
            'message' => [
                'content' => 'this is not JSON',
            ],
        ]),
    ]);

    $response = $this->actingAs($user)->postJson(
        route('dashboard.ai.task-proposals.store'),
        ['prompt' => 'Help me plan a task']
    );

    $response->assertStatus(502)
        ->assertJsonStructure(['message']);

    expect(Task::count())->toBe(0);
});
