<?php

namespace App\Console\Commands;

use App\Actions\SyncCategoriesFromApiAction;
use Illuminate\Console\Command;

class SyncCategoriesCommand extends Command
{
    protected $signature = 'skills:sync-categories';

    protected $description = 'Sync skill categories from Public APIs';

    public function handle(SyncCategoriesFromApiAction $action): int
    {
        $this->info('Fetching categories from API...');

        $result = $action->execute();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Fetched from API', $result['fetched']],
                ['Existing in DB', $result['existing']],
                ['New categories', count($result['new'])],
            ]
        );

        if (!empty($result['new'])) {
            $this->info('New categories available:');
            foreach ($result['new'] as $category) {
                $this->line("  - {$category}");
            }
        }

        $this->info('Sync complete!');

        return Command::SUCCESS;
    }
}
