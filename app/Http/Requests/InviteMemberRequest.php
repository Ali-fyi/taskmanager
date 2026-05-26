<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation fine (owner only) est vérifiée dans le controller via Policy
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L\'adresse email est requise.',
            'email.email'    => 'L\'adresse email n\'est pas valide.',
            'email.exists'   => 'Aucun compte n\'existe avec cette adresse email.',
        ];
    }
}
