<?php

namespace Database\Factories;

use App\Models\Status;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Status>
 */
class StatusFactory extends Factory
{
    protected $model = Status::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name'         => fake()->randomElement(['To Do', 'In Progress', 'Review', 'Done']),
            'color'        => fake()->hexColor(),
            'position'     => fake()->numberBetween(0, 5),
        ];
    }

    /**
     * Place the status inside an existing workspace.
     */
    public function forWorkspace(Workspace $workspace): static
    {
        return $this->state(fn () => ['workspace_id' => $workspace->id]);
    }
}
