<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workspace>
 */
class WorkspaceFactory extends Factory
{
    protected $model = Workspace::class;

    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->company(),
            'description' => fake()->sentence(),
            'owner_id'    => User::factory(),
        ];
    }

    /**
     * Mirror the application behaviour: the owner is also attached as a
     * member with the 'owner' role once the workspace exists.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Workspace $workspace) {
            $workspace->members()->syncWithoutDetaching([
                $workspace->owner_id => ['role' => 'owner'],
            ]);
        });
    }

    /**
     * Set a specific user as the owner (and member) of the workspace.
     */
    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['owner_id' => $user->id]);
    }
}
