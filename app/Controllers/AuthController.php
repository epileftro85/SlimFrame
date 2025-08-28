<?php
namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\User;
use App\Config\Auth;

class AuthController extends Controller
{
	public function register(): void
    {
        $data = [
            'name'       => $_POST['name'] ?? '',
            'last_name'  => $_POST['lastname'] ?? '',
            'email'      => $_POST['email'] ?? '',
            'password'   => $_POST['password'] ?? '',
        ];

		if (!$this->verify_csrf($_POST['csrf_token'] ?? '')) {
			http_response_code(403);
			echo 'CSRF token validation failed';
			return;
		}

        try {
            $user = User::createSecure($data);
            Auth::login((int)$user->id, false);
            header('Location: /dashboard');
        } catch (\Throwable $e) {
            http_response_code(400);
            echo 'Registration error: ' . $e->getMessage();
        }
    }

	public function doLogin(): void
    {
        $this->redirectIfAuthenticated();

        $email = $_POST['email'] ?? '';
        $pass  = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';

		if (!$this->verify_csrf($_POST['csrf_token'] ?? '')) {
			http_response_code(403);
			echo 'CSRF token validation failed';
			return;
		}

        $users = User::where(['email' => $email], limit: 1);
        $user = $users[0] ?? null;

        if (!$user || !$user->verifyPassword($pass)) {
            http_response_code(401);
            echo 'Invalid credentials';
            return;
        }

        Auth::login((int)$user->id, $remember);
        header('Location: /dashboard');
    }
}
