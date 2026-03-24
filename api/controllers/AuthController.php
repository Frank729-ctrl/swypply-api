<?php
require_once __DIR__ . '/../lib/DB.php';
require_once __DIR__ . '/../lib/JWT.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/RateLimit.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../config.php';

class AuthController {
    private static function userPayload(array $user): array {
        return [
            'id'         => (int) $user['id'],
            'name'       => $user['name'],
            'email'      => $user['email'],
            'plan'       => $user['plan'],
            'ai_used'    => (int) $user['ai_used'],
            'ai_limit'   => (int) $user['ai_limit'],
            'push_token' => $user['push_token'],
        ];
    }

    public static function register(): void {
        RateLimit::check('register'); // 3 per hour per IP

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $name     = trim($body['name']     ?? '');
        $email    = trim($body['email']    ?? '');
        $password =      $body['password'] ?? '';

        if (!$name || !$email || !$password) {
            Response::error('Name, email and password are required');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Invalid email address');
        }
        if (strlen($password) < 8) {
            Response::error('Password must be at least 8 characters');
        }
        if (DB::find('SELECT id FROM users WHERE email = ?', [$email])) {
            Response::error('Email already registered');
        }

        $id    = DB::insert(
            'INSERT INTO users (name, email, password, plan, ai_used, ai_limit) VALUES (?, ?, ?, ?, ?, ?) RETURNING id',
            [$name, $email, password_hash($password, PASSWORD_DEFAULT), 'free', 0, 3]
        );
        $user  = DB::find('SELECT * FROM users WHERE id = ?', [$id]);
        $token = JWT::encode(['sub' => (int) $id, 'exp' => time() + JWT_TTL], JWT_SECRET);

        Response::json(['token' => $token, 'user' => self::userPayload($user)], 201);
    }

    public static function login(): void {
        RateLimit::check('login'); // 5 per 15 min per IP

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $email    = trim($body['email']    ?? '');
        $password =      $body['password'] ?? '';

        if (!$email || !$password) Response::error('Email and password are required');

        $user = DB::find('SELECT * FROM users WHERE email = ?', [$email]);

        // Always run password_verify even on miss — prevents timing-based user enumeration
        $hash = $user['password'] ?? '$2y$10$invaliddummyhashfortimingXXXXXXXXXXXXXXXX';
        if (!$user || !password_verify($password, $hash)) {
            Response::error('Invalid credentials', 401);
        }

        // Clear rate limit on successful login
        RateLimit::clear('login');

        $token = JWT::encode(['sub' => (int) $user['id'], 'exp' => time() + JWT_TTL], JWT_SECRET);
        Response::json(['token' => $token, 'user' => self::userPayload($user)]);
    }

    public static function me(): void {
        $user = requireAuth();
        Response::json(['user' => self::userPayload($user)]);
    }

    public static function logout(): void {
        requireAuth();
        Response::json(['message' => 'Logged out']);
    }
}
