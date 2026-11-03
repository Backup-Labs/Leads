<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lead_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'scheduled_at' => ['required', 'date'],
            'assigned_to_user_id' => ['nullable', 'integer'],
        ];
    }
}
