<?php

test('the health endpoint returns the expected JSON', function () {
    $response = $this->getJson('/api/health');

    $response
        ->assertOk()
        ->assertJson([
            'status' => 'ok',
            'application' => 'TaskFlow AI',
        ]);
});
