<?php

namespace App\Filament\Widgets;

use App\Models\Skill;
use Filament\Widgets\ChartWidget;

class ProficiencyDistribution extends ChartWidget
{
    protected ?string $heading = 'Proficiency Distribution';

    protected ?string $pollingInterval = '30s';

    protected ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $distribution = Skill::whereNotNull('proficiency_level')
            ->selectRaw('proficiency_level, COUNT(*) as count')
            ->groupBy('proficiency_level')
            ->orderBy('proficiency_level')
            ->pluck('count', 'proficiency_level')
            ->toArray();

        $labels = [
            1 => 'Beginner',
            2 => 'Elementary',
            3 => 'Intermediate',
            4 => 'Advanced',
            5 => 'Expert',
        ];

        $data = [];
        foreach ($labels as $level => $label) {
            $data[] = $distribution[$level] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Skills',
                    'data' => $data,
                    'backgroundColor' => [
                        '#ef4444',
                        '#f97316',
                        '#eab308',
                        '#22c55e',
                        '#3b82f6',
                    ],
                ],
            ],
            'labels' => array_values($labels),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
