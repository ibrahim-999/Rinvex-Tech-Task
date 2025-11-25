<?php

namespace App\Policies;

use App\Models\Skill;
use App\Models\User;

class SkillPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Skill $skill): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Skill $skill): bool
    {
        return true;
    }

    public function delete(User $user, Skill $skill): bool
    {
        return !$skill->isArchived();
    }

    public function restore(User $user, Skill $skill): bool
    {
        return true;
    }

    public function forceDelete(User $user, Skill $skill): bool
    {
        return false;
    }

    public function archive(User $user, Skill $skill): bool
    {
        return $skill->is_active && !$skill->isArchived();
    }
}
