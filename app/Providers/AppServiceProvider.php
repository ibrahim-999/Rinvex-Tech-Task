<?php

namespace App\Providers;

use App\Models\Skill;
use App\Observers\SkillObserver;
use App\Policies\SkillPolicy;
use App\Repositories\Contracts\SkillRepositoryInterface;
use App\Repositories\SkillRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Skill::class, SkillPolicy::class);
    }
}
