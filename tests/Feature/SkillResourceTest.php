<?php

use App\Filament\Resources\SkillResource;
use App\Filament\Resources\SkillResource\Pages\CreateSkill;
use App\Filament\Resources\SkillResource\Pages\EditSkill;
use App\Filament\Resources\SkillResource\Pages\ListSkills;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    Notification::fake();
});


describe('Skill Resource Pages', function () {
    it('can render the list page', function () {
        $this->get(SkillResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can render the create page', function () {
        $this->get(SkillResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can render the view page', function () {
        $skill = Skill::factory()->create();

        $this->get(SkillResource::getUrl('view', ['record' => $skill]))
            ->assertSuccessful();
    });

    it('can render the edit page', function () {
        $skill = Skill::factory()->create();

        $this->get(SkillResource::getUrl('edit', ['record' => $skill]))
            ->assertSuccessful();
    });
});


describe('List Skills Table', function () {
    it('displays skills in the table', function () {
        $skills = Skill::factory()->count(3)->create();

        livewire(ListSkills::class)
            ->assertCanSeeTableRecords($skills);
    });

    it('can search skills by name', function () {
        $laravelSkill = Skill::factory()->create(['name' => 'Laravel Development']);
        $otherSkill = Skill::factory()->create(['name' => 'Project Management']);

        livewire(ListSkills::class)
            ->searchTable('Laravel')
            ->assertCanSeeTableRecords([$laravelSkill])
            ->assertCanNotSeeTableRecords([$otherSkill]);
    });
});


describe('Create Skill Form', function () {
    it('can create a skill with valid data', function () {
        livewire(CreateSkill::class)
            ->fillForm([
                'name' => 'New Testing Skill',
                'category' => 'Technical',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('skills', [
            'name' => 'New Testing Skill',
            'category' => 'Technical',
        ]);
    });

    it('validates required name field', function () {
        livewire(CreateSkill::class)
            ->fillForm([
                'name' => '',
                'category' => 'Technical',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    });

    it('validates unique skill name', function () {
        Skill::factory()->create(['name' => 'Existing Skill']);

        livewire(CreateSkill::class)
            ->fillForm([
                'name' => 'Existing Skill',
                'category' => 'Technical',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);
    });
});


describe('Edit Skill Form', function () {
    it('can update a skill', function () {
        $skill = Skill::factory()->create(['name' => 'Old Name']);

        livewire(EditSkill::class, ['record' => $skill->getRouteKey()])
            ->fillForm(['name' => 'Updated Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($skill->fresh()->name)->toBe('Updated Name');
    });
});


describe('Table Actions', function () {
    it('can toggle skill active status via table action', function () {
        $skill = Skill::factory()->active()->create();

        livewire(ListSkills::class)
            ->callTableAction('toggle_active', $skill);

        expect($skill->fresh()->is_active)->toBeFalse();
    });

    it('can archive a skill from table', function () {
        $skill = Skill::factory()->active()->create();

        livewire(ListSkills::class)
            ->callTableAction('archive', $skill);

        expect($skill->fresh())
            ->is_active->toBeFalse()
            ->archived_at->not->toBeNull();
    });

    it('archive action is hidden for already archived skills', function () {
        $archivedSkill = Skill::factory()->archived()->create();

        livewire(ListSkills::class)
            ->assertTableActionHidden('archive', $archivedSkill);
    });
});
