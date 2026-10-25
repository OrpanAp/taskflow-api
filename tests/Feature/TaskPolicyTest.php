<?php

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('users can list and create tasks', function () {
    $user = User::factory()->create();
    $policy = new TaskPolicy();

    expect($policy->viewAny($user))->toBeTrue()
        ->and($policy->create($user))->toBeTrue();
});

test('task owners can view update and delete their tasks', function () {
    $owner = User::factory()->create();

    $task = $owner->tasks()->create([
        'title' => 'My private task',
    ]);

    $policy = new TaskPolicy();

    expect($policy->view($owner, $task))->toBeTrue()
        ->and($policy->update($owner, $task))->toBeTrue()
        ->and($policy->delete($owner, $task))->toBeTrue();
});

test('other users cannot view update or delete someone elses task', function () {
    $owner = User::factory()->create();
    $anotherUser = User::factory()->create();

    $task = $owner->tasks()->create([
        'title' => 'Private task',
    ]);

    $policy = new TaskPolicy();

    expect($policy->view($anotherUser, $task))->toBeFalse()
        ->and($policy->update($anotherUser, $task))->toBeFalse()
        ->and($policy->delete($anotherUser, $task))->toBeFalse();
});

test('users cannot restore or permanently delete tasks', function () {
    $user = User::factory()->create();

    $task = $user->tasks()->create([
        'title' => 'Protected task',
    ]);

    $policy = new TaskPolicy();

    expect($policy->restore($user, $task))->toBeFalse()
        ->and($policy->forceDelete($user, $task))->toBeFalse();
});
