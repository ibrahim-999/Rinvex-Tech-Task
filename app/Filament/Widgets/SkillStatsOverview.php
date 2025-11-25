<?php

namespace App\Filament\Widgets;

use App\Models\Skill;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SkillStatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $total = Skill::count();
        $active = Skill::active()->count();
        $inactive = Skill::inactive()->count();
        $avgProficiency = round(Skill::whereNotNull('proficiency_level')->avg('proficiency_level') ?? 0, 1);

        return [
            Stat::make('Total Skills', $total)
                ->description('All registered skills')
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('primary'),

            Stat::make('Active Skills', $active)
                ->description($total > 0 ? round(($active / $total) * 100) . '% of total' : '0%')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Inactive Skills', $inactive)
                ->description($total > 0 ? round(($inactive / $total) * 100) . '% of total' : '0%')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Avg Proficiency', $avgProficiency . '/5')
                ->description('Average skill level')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('warning'),
        ];
    }
}
