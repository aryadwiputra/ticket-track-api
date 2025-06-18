<?php

namespace App\Http\Requests\Tickets;

use App\Enums\Tickets\TicketPriorityEnum;
use App\Enums\Tickets\TicketStatusEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Assuming any authenticated user with 'tickets-update' permission can update a ticket
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(TicketStatusEnum::values())],
            'priority' => ['sometimes', 'nullable', 'string', Rule::in(TicketPriorityEnum::values())],
            'assigned_to_user_id' => ['sometimes', 'nullable', 'exists:users,id'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The ticket title is required.',
            'title.string' => 'The ticket title must be a string.',
            'title.max' => 'The ticket title may not be greater than :max characters.',
            'status.in' => 'The selected status is invalid. Valid statuses are: ' . implode(', ', TicketStatusEnum::values()) . '.',
            'priority.in' => 'The selected priority is invalid. Valid priorities are: ' . implode(', ', TicketPriorityEnum::values()) . '.',
            'assigned_to_user_id.exists' => 'The assigned user does not exist.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // Ini adalah bagian KRUSIAL untuk API.
        // Daripada redirect, kita akan melempar HttpResponseException
        // yang akan mengembalikan respons JSON dengan error validasi.
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors() // Mengandung detail error per field
        ], 422)); // 422 Unprocessable Entity adalah kode status yang umum untuk kesalahan validasi
    }

}