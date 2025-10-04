<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->is_admin || auth()->user()->is_technician;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:2000',
            'priority' => 'sometimes|in:low,normal,high,critical',
            'location' => 'nullable|string|max:255',
            'estimated_hours' => 'nullable|integer|min:1|max:999',
            'actual_hours' => 'nullable|integer|min:1|max:999',
        ];
    }

    public function messages()
    {
        return [
            'title.string' => 'Le titre doit être une chaîne de caractères.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'priority.in' => 'Priorité invalide.',
            'estimated_hours.integer' => 'Les heures estimées doivent être un nombre entier.',
            'actual_hours.integer' => 'Les heures réelles doivent être un nombre entier.',
        ];
    }
}
