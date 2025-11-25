<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        Skill::factory(5)->withPredefinedSkills()->create();

        Skill::factory(10)->create();
    }
}
