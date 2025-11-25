<?php

namespace App\Actions;

use App\Models\Skill;
use App\Models\SkillActivity;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ArchiveSkillAction
{
    public function execute(Skill $skill): Skill
    {
        $skill->update([
            'is_active' => false,
            'archived_at' => now(),
        ]);

        SkillActivity::create([
            'skill_id' => $skill->id,
            'action' => 'archived',
            'payload' => [
                'archived_at' => now()->toDateTimeString(),
                'previous_status' => true,
            ],
            'user_id' => Auth::id(),
        ]);

        $this->sendDatabaseNotification($skill);

        return $skill->fresh();
    }

    private function sendDatabaseNotification(Skill $skill): void
    {
        $user = Auth::user();

        if ($user) {
            Notification::make()
                ->title('Skill Archived')
                ->body("The skill '{$skill->name}' has been archived.")
                ->icon('heroicon-o-archive-box')
                ->warning()
                ->sendToDatabase($user);
        }
    }
}
