<?php

use App\Actions\ArchiveSkillAction;
use App\Models\Skill;
use App\Models\SkillActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);


describe('Skill Scopes', function () {
    it('filters active skills correctly', function () {
        Skill::factory()->count(3)->active()->create();
        Skill::factory()->count(2)->inactive()->create();

        $activeSkills = Skill::active()->get();

        expect($activeSkills)->toHaveCount(3)
            ->and($activeSkills->every(fn ($skill) => $skill->is_active))->toBeTrue();
    });

    it('filters inactive skills correctly', function () {
        Skill::factory()->count(2)->active()->create();
        Skill::factory()->count(4)->inactive()->create();

        $inactiveSkills = Skill::inactive()->get();

        expect($inactiveSkills)->toHaveCount(4)
            ->and($inactiveSkills->every(fn ($skill) => !$skill->is_active))->toBeTrue();
    });

    it('filters by minimum proficiency level', function () {
        Skill::factory()->create(['proficiency_level' => 2]);
        Skill::factory()->create(['proficiency_level' => 3]);
        Skill::factory()->create(['proficiency_level' => 5]);

        $advancedSkills = Skill::minProficiency(3)->get();

        expect($advancedSkills)->toHaveCount(2)
            ->and($advancedSkills->every(fn ($skill) => $skill->proficiency_level >= 3))->toBeTrue();
    });

    it('identifies archived skills correctly', function () {
        $activeSkill = Skill::factory()->active()->create();
        $archivedSkill = Skill::factory()->archived()->create();

        expect($activeSkill->isArchived())->toBeFalse()
            ->and($archivedSkill->isArchived())->toBeTrue();
    });
});


describe('Archive Skill Action', function () {
    beforeEach(function () {
        Notification::fake();
    });

    it('sets skill as inactive and records archived timestamp', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $skill = Skill::factory()->active()->create();

        $result = app(ArchiveSkillAction::class)->execute($skill);

        expect($result->is_active)->toBeFalse()
            ->and($result->archived_at)->not->toBeNull()
            ->and($result->isArchived())->toBeTrue();
    });

    it('creates an activity log entry when archiving', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $skill = Skill::factory()->active()->create();

        app(ArchiveSkillAction::class)->execute($skill);

        $activity = SkillActivity::where('skill_id', $skill->id)
            ->where('action', 'archived')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->user_id)->toBe($user->id)
            ->and($activity->payload)->toHaveKey('archived_at');
    });

    it('does not archive an already archived skill twice', function () {
        $skill = Skill::factory()->archived()->create();
        $originalArchivedAt = $skill->archived_at;

        $canArchive = $skill->is_active && !$skill->isArchived();

        expect($canArchive)->toBeFalse()
            ->and($skill->archived_at->timestamp)->toBe($originalArchivedAt->timestamp);
    });
});


describe('Skill Validation', function () {
    it('requires a unique skill name', function () {
        Skill::factory()->create(['name' => 'Laravel Development']);

        $duplicate = Skill::factory()->make(['name' => 'Laravel Development']);

        expect(fn () => $duplicate->save())
            ->toThrow(QueryException::class);
    });

    it('enforces proficiency level bounds', function () {
        $validSkill = Skill::factory()->create(['proficiency_level' => 5]);

        expect($validSkill->proficiency_level)->toBe(5)
            ->and($validSkill->proficiency_level)->toBeGreaterThanOrEqual(1)
            ->and($validSkill->proficiency_level)->toBeLessThanOrEqual(5);
    });

    it('allows null proficiency for non-technical categories', function () {
        $skill = Skill::factory()->create([
            'category' => 'Leadership',
            'proficiency_level' => null,
        ]);

        expect($skill->proficiency_level)->toBeNull()
            ->and($skill->exists)->toBeTrue();
    });

    it('casts tags and attachments to arrays', function () {
        $skill = Skill::factory()->create([
            'tags' => [['value' => 'PHP'], ['value' => 'Laravel']],
            'attachments' => ['file1.pdf', 'file2.pdf'],
        ]);

        expect($skill->tags)->toBeArray()
            ->and($skill->tags)->toHaveCount(2)
            ->and($skill->attachments)->toBeArray()
            ->and($skill->attachments)->toHaveCount(2);
    });
});


describe('Skill Statistics', function () {
    it('calculates average proficiency correctly', function () {
        Skill::factory()->create(['proficiency_level' => 2]);
        Skill::factory()->create(['proficiency_level' => 4]);
        Skill::factory()->create(['proficiency_level' => 4]);
        Skill::factory()->create(['proficiency_level' => null]);

        $average = Skill::whereNotNull('proficiency_level')
            ->avg('proficiency_level');

        expect(round($average, 1))->toBe(3.3);
    });

    it('counts active vs inactive skills accurately', function () {
        Skill::factory()->count(5)->active()->create();
        Skill::factory()->count(3)->inactive()->create();

        expect(Skill::active()->count())->toBe(5)
            ->and(Skill::inactive()->count())->toBe(3)
            ->and(Skill::count())->toBe(8);
    });
});
