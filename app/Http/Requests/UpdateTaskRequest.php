<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => [
                'sometimes',
                'string',
                'in:new,in_progress,blocked,done,cancelled'
            ],
            'priority' => [
                'sometimes',
                'string',
                'in:low,normal,high,critical'
            ],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
