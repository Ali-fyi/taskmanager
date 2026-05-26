<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'  => ['required', 'integer', 'exists:projects,id'],
            'title'       => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status_id'   => ['nullable', 'integer', 'exists:statuses,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'due_date'    => ['nullable', 'date'],
        ];
    }
}
