<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isProfessor();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'deadline' => 'required|date|after:now',
            'allowed_extensions' => 'required|array|min:1',
            'allowed_extensions.*' => 'string|in:.php,.html,.css,.js,.py,.java,.c,.cpp,.h,.txt,.xml,.json',
            'excluded_filenames' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'deadline.after' => 'La date limite doit être dans le futur.',
            'allowed_extensions.required' => 'Sélectionnez au moins une extension.',
        ];
    }
}