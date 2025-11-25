<?php

namespace App\Filament\Resources;

use App\Actions\ArchiveSkillAction;
use App\Filament\Resources\SkillResource\Pages\CreateSkill;
use App\Filament\Resources\SkillResource\Pages\EditSkill;
use App\Filament\Resources\SkillResource\Pages\ListSkills;
use App\Filament\Resources\SkillResource\Pages\ViewSkill;
use App\Integrations\PublicApisClient;
use App\Models\Skill;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Count;

class SkillResource extends Resource
{
    protected static ?string $model = Skill::class;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Basic Information')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->columnSpanFull(),

                            Forms\Components\Select::make('category')
                                ->required()
                                ->options(fn () => self::getCategoryOptions())
                                ->searchable()
                                ->preload()
                                ->live(),

                            Toggle::make('is_active')
                                ->default(true)
                                ->inline(false),
                        ])
                        ->columns(2),

                    Step::make('Details')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Radio::make('proficiency_level')
                                ->options([
                                    1 => '1 - Beginner',
                                    2 => '2 - Elementary',
                                    3 => '3 - Intermediate',
                                    4 => '4 - Advanced',
                                    5 => '5 - Expert',
                                ])
                                ->visible(fn (Get $get) => $get('category') === 'Technical')
                                ->columnSpanFull(),

                            MarkdownEditor::make('description')
                                ->columnSpanFull(),

                            Textarea::make('notes')
                                ->rows(4)
                                ->columnSpanFull()
                                ->extraAttributes([
                                    'x-data' => '{ charCount: 0 }',
                                    'x-init' => 'charCount = $el.querySelector("textarea")?.value.length || 0',
                                ])
                                ->extraInputAttributes([
                                    'x-on:input' => 'charCount = $event.target.value.length',
                                ])
                                ->hint(new HtmlString(
                                    '<span x-text="charCount"></span> characters'
                                )),
                        ]),

                    Step::make('Media & Tags')
                        ->icon('heroicon-o-paper-clip')
                        ->schema([
                            FileUpload::make('attachments')
                                ->multiple()
                                ->directory('skill-attachments')
                                ->maxFiles(5)
                                ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword'])
                                ->columnSpanFull(),

                            Repeater::make('tags')
                                ->schema([
                                    TextInput::make('value')
                                        ->required()
                                        ->maxLength(50)
                                        ->label('Tag'),
                                ])
                                ->addActionLabel('Add Tag')
                                ->defaultItems(0)
                                ->collapsible()
                                ->columnSpanFull(),
                        ]),
                ])
                    ->columnSpanFull()
                    ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->summarize(Count::make()->label('Total')),

                TextColumn::make('category')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('proficiency_level')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? str_repeat('★', $state) . str_repeat('☆', 5 - $state) : '-')
                    ->label('Proficiency')
                    ->summarize(Average::make()->label('Avg')),

                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable()
                    ->label('Active'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->options(fn () => Skill::distinct()->pluck('category', 'category')->toArray()),

                Filter::make('proficiency_level')
                    ->form([
                        Select::make('min_proficiency')
                            ->options([
                                1 => '≥ 1 - Beginner',
                                2 => '≥ 2 - Elementary',
                                3 => '≥ 3 - Intermediate',
                                4 => '≥ 4 - Advanced',
                                5 => '≥ 5 - Expert',
                            ]),
                    ])
                    ->query(fn (Builder $query, array $data) =>
                    $query->when($data['min_proficiency'], fn ($q, $level) => $q->where('proficiency_level', '>=', $level))
                    ),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
            ])
            ->groups([
                Group::make('category')
                    ->collapsible(),
                Group::make('proficiency_level')
                    ->label('Proficiency')
                    ->collapsible(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (Skill $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Skill $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Skill $record) => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Skill $record) {
                        $record->update(['is_active' => !$record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? 'Skill Activated' : 'Skill Deactivated')
                            ->success()
                            ->send();
                    }),
                Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Archive Skill')
                    ->modalDescription('Are you sure you want to archive this skill? This will deactivate it and log the action.')
                    ->visible(fn (Skill $record) => $record->is_active && !$record->isArchived())
                    ->action(function (Skill $record) {
                        app(ArchiveSkillAction::class)->execute($record);
                        Notification::make()
                            ->title('Skill Archived')
                            ->body("'{$record->name}' has been archived successfully.")
                            ->warning()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No skills yet')
            ->emptyStateDescription('Create your first skill to get started.')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('name')
                            ->weight('bold'),
                        TextEntry::make('category')
                            ->badge(),
                        IconEntry::make('is_active')
                            ->boolean()
                            ->label('Status'),
                        TextEntry::make('proficiency_level')
                            ->formatStateUsing(fn ($state) => $state ? str_repeat('★', $state) . str_repeat('☆', 5 - $state) : 'Not set')
                            ->label('Proficiency'),
                    ])
                    ->columns(2),

                Section::make('Description')
                    ->schema([
                        TextEntry::make('description')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Tags')
                    ->schema([
                        RepeatableEntry::make('tags')
                            ->schema([
                                TextEntry::make('value')
                                    ->badge()
                                    ->hiddenLabel(),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => !empty($record->tags)),

                Section::make('Attachments')
                    ->schema([
                        ImageEntry::make('attachments')
                            ->stacked()
                            ->limit(3)
                            ->circular(),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => !empty($record->attachments)),

                Section::make('Metadata')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                        TextEntry::make('archived_at')
                            ->dateTime()
                            ->visible(fn ($record) => $record->archived_at),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSkills::route('/'),
            'create' => CreateSkill::route('/create'),
            'view' => ViewSkill::route('/{record}'),
            'edit' => EditSkill::route('/{record}/edit'),
        ];
    }

    private static function getCategoryOptions(): array
    {
        $client = app(PublicApisClient::class);
        $categories = $client->getCategories();

        return array_combine($categories, $categories);
    }
}
