<?php

test('the API health endpoint is available', function () {
    $response = $this->getJson('/api/health');

    $response->assertOk();
});
