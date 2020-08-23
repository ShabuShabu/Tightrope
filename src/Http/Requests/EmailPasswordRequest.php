<?php

namespace ShabuShabu\Tightrope\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() ? false : true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'email.required' => 'The email address is required',
            'email.email'    => 'You must provide a valid email address',
        ];
    }
}
