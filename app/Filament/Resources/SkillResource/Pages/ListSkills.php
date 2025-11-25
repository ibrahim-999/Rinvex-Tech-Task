<?php

namespace App\Filament\Resources\SkillResource\Pages;

use App\Actions\SyncCategoriesFromApiAction;
use App\Filament\Resources\SkillResource;
use App\Filament\Widgets\ProficiencyDistribution;
use App\Filament\Widgets\SkillStatsOverview;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSkills extends ListRecords
{
    protected static string $resource = SkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync_categories')
                ->label('Sync Categories')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $result = app(SyncCategoriesFromApiAction::class)->execute();

                    Notification::make()
                        ->title('Categories Synced')
                        ->body("Fetched {$result['fetched']} categories. " . count($result['new']) . " new categories available.")
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SkillStatsOverview::class,
            ProficiencyDistribution::class,
        ];
    }
}
