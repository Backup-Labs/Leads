<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LeadPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'requested_service' => ['nullable', 'string', 'max:255'],
            'lead_source_id' => ['nullable', 'integer'],
            'pipeline_stage_id' => ['nullable', 'integer'],
            'assigned_to_user_id' => ['nullable', 'integer'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'priority' => ['nullable', Rule::enum(LeadPriority::class)],
            'description' => ['nullable', 'string'],
        ];
    }
}
