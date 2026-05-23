<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isStudent();
    }

    public function rules(): array
    {
        return [
            'exam_id' => 'required|exists:exams,id',
            'zip_file' => 'required|file|mimes:zip|max:51200', // 50MB max
        ];
    }

    public function messages(): array
    {
        return [
            'zip_file.required' => 'Veuillez sélectionner un fichier ZIP.',
            'zip_file.mimes' => 'Le fichier doit être au format ZIP.',
            'zip_file.max' => 'Le fichier ne doit pas dépasser 50 MB.',
        ];
    }
}