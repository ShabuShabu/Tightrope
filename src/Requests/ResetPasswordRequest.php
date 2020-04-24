<?php

namespace ShabuShabu\Tightrope\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'confirmed'],
            'password_confirmation' => ['required_with:password'],
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
            'token.required'                      => 'The request was invalid due to a missing token',
            'email.required'                      => 'The email address is required',
            'email.email'                         => 'You must provide a valid email address',
            'password.required'                   => 'You must provide a password',
            'password.confirmed'                  => 'The password and the confirmation do not match',
            'password_confirmation.required_with' => 'You must confirm the password',
        ];
    }
}
