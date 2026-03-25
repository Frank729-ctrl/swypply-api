<?php
// Never expose PHP errors or stack traces to clients
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/lib/Response.php';
require_once __DIR__ . '/lib/RateLimit.php';

// ── Security headers ──────────────────────────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
// HSTS — enforces HTTPS for 1 year (only effective over HTTPS)
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// CORS — allow mobile app from any origin
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Block non-JSON POST bodies (prevents content-type sniffing attacks)
if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH'])) {
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (!str_contains($ct, 'application/json')) {
        http_response_code(415);
        echo json_encode(['message' => 'Content-Type must be application/json']);
        exit;
    }
}

// Global rate limit: 120 requests per minute per IP
RateLimit::check('api');

$method   = $_SERVER['REQUEST_METHOD'];
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path     = trim(preg_replace('#^/api/?#', '', $uri), '/');
$segments = $path !== '' ? explode('/', $path) : [];
$resource = $segments[0] ?? '';
$id       = isset($segments[1]) ? (int) $segments[1] : null;

// GET /api/health
if ($resource === 'health') {
    Response::json(['status' => 'ok']);
}

// /api/auth/*
if ($resource === 'auth') {
    require_once __DIR__ . '/controllers/AuthController.php';
    match ($segments[1] ?? '') {
        'register'     => AuthController::register(),
        'login'        => AuthController::login(),
        'me'           => AuthController::me(),
        'logout'       => AuthController::logout(),
        'verify-email' => AuthController::verifyEmail(),
        'resend-code'  => AuthController::resendCode(),
        default        => Response::error('Not found', 404),
    };
}

// /api/applications[/{id}]
if ($resource === 'applications') {
    require_once __DIR__ . '/controllers/ApplicationController.php';
    if ($method === 'GET')                        ApplicationController::index();
    if ($method === 'POST')                       ApplicationController::store();
    if ($method === 'DELETE' && $id !== null)     ApplicationController::destroy($id);
    Response::error('Method not allowed', 405);
}

// /api/subscription/*
if ($resource === 'subscription') {
    require_once __DIR__ . '/controllers/SubscriptionController.php';
    match ($segments[1] ?? '') {
        'verify'     => SubscriptionController::verify(),
        'push-token' => SubscriptionController::savePushToken(),
        default      => Response::error('Not found', 404),
    };
}

Response::error('Not found', 404);
