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

    $payload = JWT::decode($m[1], JWT_SECRET);
    if (!$payload) Response::error('Token invalid or expired', 401);

    $user = DB::find('SELECT * FROM users WHERE id = ?', [$payload['sub']]);
    if (!$user) Response::error('User not found', 401);

    return $user;
}
