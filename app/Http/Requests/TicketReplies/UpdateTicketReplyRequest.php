<?php

namespace App\Http\Requests\TicketReplies;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTicketReplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Assuming any authenticated user with 'ticket-replies-update' permission can update a reply
        // You might add logic here to check if the user owns the reply or has a specific role
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
            'content' => ['sometimes', 'required', 'string', 'min:10'],
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
            'content.required' => 'Reply content is required.',
            'content.string' => 'Reply content must be a string.',
            'content.min' => 'Reply content must be at least :min characters.',
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
