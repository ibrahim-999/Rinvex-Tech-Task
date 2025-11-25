<?php

use App\Actions\SyncCategoriesFromApiAction;
use App\Integrations\PublicApisClient;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);


describe('PublicApisClient', function () {
    beforeEach(function () {
        Cache::forget('public_apis_categories');
    });

    it('fetches and extracts unique categories from API', function () {
        Http::fake([
            'api.publicapis.org/*' => Http::response([
                'entries' => [
                    ['Category' => 'Development'],
                    ['Category' => 'Security'],
                    ['Category' => 'Development'],
                    ['Category' => 'Finance'],
                ],
            ], 200),
        ]);

        $client = new PublicApisClient();
        $categories = $client->fetchCategories();

        expect($categories)->toBeArray()
            ->and($categories)->toContain('Development', 'Security', 'Finance')
            ->and(array_unique($categories))->toHaveCount(count($categories));
    });

    it('returns default categories when API fails', function () {
        Http::fake([
            'api.publicapis.org/*' => Http::response(null, 500),
        ]);

        $client = new PublicApisClient();
        $categories = $client->fetchCategories();

        expect($categories)->toBeArray()
            ->and($categories)->toContain('Technical', 'Communication', 'Leadership');
    });

    it('caches API response to avoid repeated calls', function () {
        Http::fake([
            'api.publicapis.org/*' => Http::response([
                'entries' => [
                    ['Category' => 'Cached'],
                ],
            ], 200),
        ]);

        $client = new PublicApisClient();

        $firstCall = $client->getCategories();
        $secondCall = $client->getCategories();

        expect($firstCall)->toBe($secondCall);
        Http::assertSentCount(1);
    });
});


describe('SyncCategoriesFromApiAction', function () {
    it('identifies new categories not yet in database', function () {
        Http::fake([
            'api.publicapis.org/*' => Http::response([
                'entries' => [
                    ['Category' => 'Technical'],
                    ['Category' => 'NewCategory'],
                    ['Category' => 'AnotherNew'],
                ],
            ], 200),
        ]);

        // Create existing skill with 'Technical' category
        Skill::factory()->create(['category' => 'Technical']);

        Cache::forget('public_apis_categories');

        $action = app(SyncCategoriesFromApiAction::class);
        $result = $action->execute();

        expect($result['fetched'])->toBe(3)
            ->and($result['new'])->toContain('NewCategory', 'AnotherNew')
            ->and($result['new'])->not->toContain('Technical');
    });
});
