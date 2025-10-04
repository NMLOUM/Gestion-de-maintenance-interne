<?php
// app/Http/Requests/StoreTicketRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'service_id' => 'required|exists:services,id',
            'category_id' => 'required|exists:categories,id',
            'priority' => 'required|in:low,normal,high,critical',
            'location' => 'nullable|string|max:255',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'service_id.required' => 'Le service est obligatoire.',
            'category_id.required' => 'La catégorie est obligatoire.',
            'priority.required' => 'La priorité est obligatoire.',
            'attachments.max' => 'Vous ne pouvez pas joindre plus de 5 fichiers.',
            'attachments.*.max' => 'Chaque fichier ne peut pas dépasser 10 MB.',
            'attachments.*.mimes' => 'Format de fichier non autorisé.',
        ];
    }
}
