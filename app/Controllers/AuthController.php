<?php
namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\User;
use App\Config\Auth;
use App\Requests\RegisterRequest;
use App\Requests\LoginRequest;
use App\Utils\Response;

class AuthController extends Controller
{
	public function register(): void
    {
        $request = new RegisterRequest();

        // Validate the request
        if (!$request->validate()) {
            if (Response::wantsJson()) {
                Response::validationErrors($request->errors(), 'Registration validation failed');
            } else {
                Response::htmlError('Please correct the following errors:', 400, $request->errors());
            }
            return;
        }

        // Verify CSRF token
        if (!$this->verify_csrf($request->input('csrf_token'))) {
            if (Response::wantsJson()) {
                Response::error('Security token validation failed', [], 403);
            } else {
                Response::htmlError('Security token validation failed', 403);
            }
            return;
        }

        try {
            $user = User::createSecure($request->getRegistrationData());
            Auth::login((int)$user->id, false);

            if (Response::wantsJson()) {
                Response::success('Registration successful', ['redirect' => '/dashboard']);
            } else {
                Response::redirect('/dashboard', 'Registration successful! Welcome to your dashboard.');
            }
        } catch (\Throwable $e) {
            if (Response::wantsJson()) {
                Response::error('Registration failed: ' . $e->getMessage(), [], 400);
            } else {
                Response::htmlError('Registration failed: ' . $e->getMessage(), 400);
            }
        }
    }

	public function doLogin(): void
    {
        $this->redirectIfAuthenticated();

        $request = new LoginRequest();

        // Validate the request
        if (!$request->validate()) {
            if (Response::wantsJson()) {
                Response::validationErrors($request->errors(), 'Login validation failed');
            } else {
                Response::htmlError('Please correct the following errors:', 400, $request->errors());
            }
            return;
        }

        // Verify CSRF token
        if (!$this->verify_csrf($request->input('csrf_token'))) {
            if (Response::wantsJson()) {
                Response::error('Security token validation failed', [], 403);
            } else {
                Response::htmlError('Security token validation failed', 403);
            }
            return;
        }

        $credentials = $request->getCredentials();
        $users = User::where(['email' => $credentials['email']], limit: 1);
        $user = $users[0] ?? null;

        if (!$user || !$user->verifyPassword($credentials['password'])) {
            if (Response::wantsJson()) {
                Response::error('Invalid email or password', [], 401);
            } else {
                Response::htmlError('Invalid email or password', 401);
            }
            return;
        }

        Auth::login((int)$user->id, $credentials['remember']);

        if (Response::wantsJson()) {
            Response::success('Login successful', ['redirect' => '/dashboard']);
        } else {
            Response::redirect('/dashboard', 'Login successful! Welcome back.');
        }
    }
}
