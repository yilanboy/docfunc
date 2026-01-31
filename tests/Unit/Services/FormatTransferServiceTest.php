<?php

use App\Services\FormatTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->formatTransferService = $this->app->make(FormatTransferService::class);
});

it('will return empty array, if not pass the tag json string', function () {
    expect($this->formatTransferService->tagsJsonToTagIdsArray())
        ->toBeEmpty();
});

it('can transform tag json to tag ids array', function () {
    $tagsJson = json_encode([
        ['id' => 1, 'name' => 'PHP'],
        ['id' => 2, 'name' => 'Laravel'],
    ]);

    expect($this->formatTransferService->tagsJsonToTagIdsArray($tagsJson))
        ->toBe([1, 2]);
});
