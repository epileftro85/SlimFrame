<?php
namespace App\Requests;

use App\Models\User;

class RegisterRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8',
            'csrf_token' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'First name is required',
            'name.max' => 'First name must not exceed 255 characters',
            'last_name.required' => 'Last name is required',
            'last_name.max' => 'Last name must not exceed 255 characters',
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'email.max' => 'Email address must not exceed 255 characters',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters long',
            'csrf_token.required' => 'Security token is missing'
        ];
    }

    /**
     * Additional validation for email uniqueness
     */
    public function validate(): bool
    {
        // Run base validation first
        if (!parent::validate()) {
            return false;
        }

        // Check email uniqueness
        $email = $this->input('email');
        if ($email) {
            $existing = User::where(['email' => $email], limit: 1);
            if (!empty($existing)) {
                $this->addError('email', 'This email address is already registered');
                return false;
            }
        }

        return true;
    }

    /**
     * Get sanitized registration data
     */
    public function getRegistrationData(): array
    {
        return [
            'name' => $this->input('name'),
            'last_name' => $this->input('last_name'),
            'company' => $this->input('company'),
            'website' => $this->input('website'),
            'email' => $this->input('email'),
            'password' => $this->input('password')
        ];
    }
}
