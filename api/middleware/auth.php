<?php
require_once __DIR__ . '/../lib/JWT.php';
require_once __DIR__ . '/../lib/DB.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../../config.php';

function requireAuth(): array {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.+)/i', $header, $m)) {
        Response::error('Unauthenticated', 401);
    }

    // Strip any whitespace that might sneak in
    $token = trim($m[1]);

    // Reject obviously malformed tokens before hitting JWT decode
    if (substr_count($token, '.') !== 2) {
        Response::error('Token invalid or expired', 401);
    }

    $payload = JWT::decode($token, JWT_SECRET);
    if (!$payload) Response::error('Token invalid or expired', 401);

    // `sub` must be a positive integer
    $id = filter_var($payload['sub'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) Response::error('Token invalid or expired', 401);

    $user = DB::find('SELECT * FROM users WHERE id = ?', [$id]);
    if (!$user) Response::error('User not found', 401);

    // Block unverified accounts from accessing protected routes
    if (!$user['email_verified_at']) {
        Response::error('Email not verified', 403);
    }

    return $user;
}
