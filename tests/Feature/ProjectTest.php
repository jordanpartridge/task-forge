<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->lead = User::factory()->create();
});

it('creates a project with basic attributes', function () {
    $project = Project::factory()->create([
        'name' => 'Test Project',
        'owner_id' => $this->owner->id,
        'lead_id' => $this->lead->id,
    ]);


    expect($project)
        ->name->not->toBeNull()
        ->slug->not->toBeNull()
        ->status->toBe('active')
        ->priority->toBe('medium')
        ->owner_id->toBe($this->owner->id)
        ->lead_id->toBe($this->lead->id);
})->group('projects');

