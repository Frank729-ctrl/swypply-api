<?php
require_once __DIR__ . '/../lib/DB.php';
require_once __DIR__ . '/../lib/JWT.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/RateLimit.php';
require_once __DIR__ . '/../lib/Mailer.php';
require_once __DIR__ . '/../mail/MailService.php';
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
            'push_token' => $user['push_token'] ?? null,
        ];
    }

    /** Generate a cryptographically random 6-digit code. */
    private static function makeCode(): string {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // ── Register ──────────────────────────────────────────────────────────────

    public static function register(): void {
        RateLimit::check('register'); // 3 per hour per IP

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $name     = trim($body['name']     ?? '');
        $email    = strtolower(trim($body['email'] ?? ''));
        $password = $body['password'] ?? '';

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

        $code    = self::makeCode();
        $expires = date('c', time() + 600); // 10 minutes

        DB::insert(
            'INSERT INTO users (name, email, password, plan, ai_used, ai_limit,
                                email_verification_code, email_verification_expires_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?) RETURNING id',
            [$name, $email, password_hash($password, PASSWORD_DEFAULT),
             'free', 0, 3, $code, $expires]
        );

        MailService::sendVerificationCode($email, $name, $code);

        Response::json([
            'message' => 'Account created. Please check your email for a verification code.',
            'email'   => $email,
        ], 201);
    }

    // ── Verify email ──────────────────────────────────────────────────────────

    public static function verifyEmail(): void {
        RateLimit::check('verify'); // uses same table, defined in RateLimit

        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $email = strtolower(trim($body['email'] ?? ''));
        $code  = trim($body['code'] ?? '');

        if (!$email || !$code) {
            Response::error('Email and code are required');
        }

        $user = DB::find('SELECT * FROM users WHERE email = ?', [$email]);

        if (!$user) {
            Response::error('Invalid email or code', 401);
        }
        if ($user['email_verified_at']) {
            // Already verified — just return a token
            $token = JWT::encode(['sub' => (int) $user['id'], 'exp' => time() + JWT_TTL], JWT_SECRET);
            Response::json(['token' => $token, 'user' => self::userPayload($user)]);
        }
        if (!hash_equals((string) $user['email_verification_code'], $code)) {
            Response::error('Invalid verification code', 401);
        }
        if (strtotime($user['email_verification_expires_at']) < time()) {
            Response::error('Code has expired. Please request a new one.', 401);
        }

        // Mark verified, clear the code
        DB::query(
            'UPDATE users SET email_verified_at = NOW(),
                              email_verification_code = NULL,
                              email_verification_expires_at = NULL
             WHERE id = ?',
            [$user['id']]
        );

        $token = JWT::encode(['sub' => (int) $user['id'], 'exp' => time() + JWT_TTL], JWT_SECRET);

        // Send welcome email (non-blocking — failure doesn't break the response)
        MailService::sendWelcome($email, $user['name']);

        Response::json(['token' => $token, 'user' => self::userPayload($user)]);
    }

    // ── Resend code ───────────────────────────────────────────────────────────

    public static function resendCode(): void {
        RateLimit::check('resend'); // limit aggressively

        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $email = strtolower(trim($body['email'] ?? ''));

        if (!$email) Response::error('Email is required');

        $user = DB::find('SELECT * FROM users WHERE email = ?', [$email]);

        // Don't leak whether email exists — always return success
        if ($user && !$user['email_verified_at']) {
            $code    = self::makeCode();
            $expires = date('c', time() + 600);

            DB::query(
                'UPDATE users SET email_verification_code = ?,
                                  email_verification_expires_at = ?
                 WHERE id = ?',
                [$code, $expires, $user['id']]
            );

            MailService::sendVerificationCode($email, $user['name'], $code);
        }

        Response::json(['message' => 'If that email is registered and unverified, a new code has been sent.']);
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    public static function login(): void {
        RateLimit::check('login'); // 5 per 15 min per IP

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $email    = strtolower(trim($body['email'] ?? ''));
        $password = $body['password'] ?? '';

        if (!$email || !$password) Response::error('Email and password are required');

        $user = DB::find('SELECT * FROM users WHERE email = ?', [$email]);

        // Always run password_verify — prevents timing-based user enumeration
        $hash = $user['password'] ?? '$2y$10$invaliddummyhashfortimingXXXXXXXXXXXXXXXX';
        if (!$user || !password_verify($password, $hash)) {
            Response::error('Invalid credentials', 401);
        }

        if (!$user['email_verified_at']) {
            Response::error('Please verify your email before logging in.', 403);
        }

        RateLimit::clear('login');

        $token = JWT::encode(['sub' => (int) $user['id'], 'exp' => time() + JWT_TTL], JWT_SECRET);
        Response::json(['token' => $token, 'user' => self::userPayload($user)]);
    }

    // ── Me / Logout ───────────────────────────────────────────────────────────

    public static function me(): void {
        $user = requireAuth();
        Response::json(['user' => self::userPayload($user)]);
    }

    public static function logout(): void {
        requireAuth();
        Response::json(['message' => 'Logged out']);
    }
}
