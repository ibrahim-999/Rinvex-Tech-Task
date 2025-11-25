<?php

namespace App\Filament\Resources\SkillResource\Pages;

use App\Actions\ArchiveSkillAction;
use App\Filament\Resources\SkillResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSkill extends ViewRecord
{
    protected static string $resource = SkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('archive')
                ->label('Archive')
                ->icon('heroicon-o-archive-box')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->is_active && !$this->record->isArchived())
                ->action(function () {
                    app(ArchiveSkillAction::class)->execute($this->record);

                    Notification::make()
                        ->title('Skill Archived')
                        ->warning()
                        ->send();

                    $this->refreshFormData(['is_active', 'archived_at']);
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
