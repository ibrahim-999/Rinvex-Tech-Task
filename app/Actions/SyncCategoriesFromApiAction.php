<?php

namespace App\Actions;

use App\Integrations\PublicApisClient;
use App\Models\Skill;
use Illuminate\Support\Collection;

class SyncCategoriesFromApiAction
{
    public function __construct(
        private PublicApisClient $client
    ) {}

    public function execute(): array
    {
        $apiCategories = $this->client->getCategories();
        $existingCategories = Skill::distinct()->pluck('category')->toArray();

        $newCategories = collect($apiCategories)
            ->diff($existingCategories)
            ->values()
            ->toArray();

        return [
            'fetched' => count($apiCategories),
            'new' => $newCategories,
            'existing' => count($existingCategories),
        ];
    }

    public function getAvailableCategories(): Collection
    {
        $apiCategories = $this->client->getCategories();
        $existingCategories = Skill::distinct()->pluck('category')->toArray();

        return collect(array_unique(array_merge($apiCategories, $existingCategories)))->sort()->values();
    }
}
