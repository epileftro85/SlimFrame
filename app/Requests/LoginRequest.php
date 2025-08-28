<?php
namespace App\Requests;

class LoginRequest extends Request
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'password' => 'required',
            'remember' => 'in:on,', // Allow 'on' or empty
            'csrf_token' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'email.max' => 'Email address must not exceed 255 characters',
            'password.required' => 'Password is required',
            'remember.in' => 'Invalid remember me value',
            'csrf_token.required' => 'Security token is missing'
        ];
    }

    /**
     * Get login credentials
     */
    public function getCredentials(): array
    {
        return [
            'email' => $this->input('email'),
            'password' => $this->input('password'),
            'remember' => $this->input('remember') === 'on'
        ];
    }
}
