<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PublicApisClient
{
    private const API_URL = 'https://api.publicapis.org/entries';
    private const CACHE_KEY = 'public_apis_categories';
    private const CACHE_TTL = 3600;

    public function getCategories(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->fetchCategories();
        });
    }

    public function fetchCategories(): array
    {
        try {
            $response = Http::timeout(10)->get(self::API_URL);

            if ($response->failed()) {
                Log::warning('PublicApisClient: API request failed', [
                    'status' => $response->status(),
                ]);
                return $this->getDefaultCategories();
            }

            $data = $response->json();

            if (!isset($data['entries']) || !is_array($data['entries'])) {
                Log::warning('PublicApisClient: Invalid response structure');
                return $this->getDefaultCategories();
            }

            $categories = collect($data['entries'])
                ->pluck('Category')
                ->unique()
                ->filter()
                ->sort()
                ->values()
                ->toArray();

            return !empty($categories) ? $categories : $this->getDefaultCategories();

        } catch (\Exception $e) {
            Log::error('PublicApisClient: Exception occurred', [
                'message' => $e->getMessage(),
            ]);
            return $this->getDefaultCategories();
        }
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function getDefaultCategories(): array
    {
        return [
            'Technical',
            'Communication',
            'Leadership',
            'Design',
            'Management',
            'Analytics',
            'Development',
            'Security',
        ];
    }
}
