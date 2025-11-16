<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Mailer;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show login page. If logged in, go to feed.
     */
    public function showLogin()
    {
        if (Session::get('user')) {
            header('Location: /feed');
            exit;
        }
        $this->view('auth/login.php');
    }

    /**
     * Show register page. If logged in, go to feed.
     */
    public function showRegister()
    {
        if (Session::get('user')) {
            header('Location: /feed');
            exit;
        }
        $this->view('auth/register.php');
    }

    /**
     * Registration handler
     */
    public function register()
    {
        $name            = trim($_POST['name'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Field check
        if ($name === '' || $email === '' || $password === '' || $passwordConfirm === '') {
            Session::set('error', 'Please fill in all fields.');
            header('Location: /register');
            exit;
        }

        // Email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::set('error', 'Invalid email address.');
            header('Location: /register');
            exit;
        }

        // Password match
        if ($password !== $passwordConfirm) {
            Session::set('error', 'Passwords do not match.');
            header('Location: /register');
            exit;
        }

        // Password length
        if (strlen($password) < 6) {
            Session::set('error', 'Password must be at least 6 characters.');
            header('Location: /register');
            exit;
        }

        // Duplicate email check
        if (User::findByEmail($email)) {
            Session::set('error', 'A user with that email already exists.');
            header('Location: /register');
            exit;
        }

        // Create user
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        User::create($name, $email, $hashed);

        // (Optional) welcome email — updated branding to SayoNet
        try {
            Mailer::send(
                $email,
                'Welcome to SayoNet',
                "Hello $name,\n\nThanks for registering at SayoNet! We're happy to have you.\n\n— SayoNet Team"
            );
        } catch (\Throwable $e) {
            error_log('Mail error: ' . $e->getMessage());
        }

        Session::set('success', 'Registration successful. Please log in.');
        header('Location: /login');
        exit;
    }

    /**
     * Login handler
     */
    public function login()
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            Session::set('error', 'Please enter email and password.');
            header('Location: /login');
            exit;
        }

        $user = User::findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            Session::set('user', [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email']
            ]);

            header('Location: /feed');
            exit;
        }

        Session::set('error', 'Invalid email or password.');
        header('Location: /login');
        exit;
    }

    /**
     * Logout
     */
    public function logout()
    {
        Session::destroy();
        header('Location: /login');
        exit;
    }
}
