<?php
// Secure session config — must be set before session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();
require_once __DIR__ . '/../config.php';

function adminAuth(): void {
    if (empty($_SESSION['admin'])) {
        header('Location: /admin/');
        exit;
    }
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfVerify(): void {
    $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

// Admin brute force rate limit (file-based — works even if DB is down)
function adminRateLimit(): void {
    $ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key  = sys_get_temp_dir() . '/swypply_admin_' . hash('sha256', $ip);
    $now  = time();
    $data = file_exists($key) ? json_decode(file_get_contents($key), true) : null;

    if (!$data || $data['expires'] < $now) {
        file_put_contents($key, json_encode(['attempts' => 1, 'expires' => $now + 900]));
        return;
    }

    if ($data['attempts'] >= 5) {
        $wait = $data['expires'] - $now;
        http_response_code(429);
        die("Too many login attempts. Try again in {$wait} seconds.");
    }

    $data['attempts']++;
    file_put_contents($key, json_encode($data));
}

function adminRateLimitClear(): void {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = sys_get_temp_dir() . '/swypply_admin_' . hash('sha256', $ip);
    @unlink($key);
}
