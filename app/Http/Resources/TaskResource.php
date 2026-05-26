<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'due_date'    => $this->due_date?->toDateString(),
            'status'      => new StatusResource($this->whenLoaded('status')),
            'assignee'    => new UserResource($this->whenLoaded('assignee')),
            'project'     => new ProjectResource($this->whenLoaded('project')),
            'created_at'  => $this->created_at->toDateTimeString(),
            'updated_at'  => $this->updated_at->toDateTimeString(),
        ];
    }
}
