<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id'  => Project::factory(),
            'status_id'   => null,
            'assigned_to' => null,
            'title'       => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'due_date'    => fake()->optional()->dateTimeBetween('now', '+1 month'),
        ];
    }

    /**
     * Place the task inside an existing project.
     */
    public function forProject(Project $project): static
    {
        return $this->state(fn () => ['project_id' => $project->id]);
    }
}
