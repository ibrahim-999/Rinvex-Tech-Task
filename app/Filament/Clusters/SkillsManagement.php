<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use App\Models\Skill;

class SkillsManagement extends Cluster
{
    protected static ?string $navigationLabel = 'Skills';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) Skill::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = Skill::count();

        if ($count === 0) {
            return 'gray';
        }

        return $count > 10 ? 'success' : 'warning';
    }
}
