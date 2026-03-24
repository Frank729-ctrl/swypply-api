<?php
require_once __DIR__ . '/../lib/DB.php';
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../../config.php';

class SubscriptionController {
    private const PLAN_LIMITS = ['basic' => 20, 'pro' => 9999];

    public static function verify(): void {
        $user = requireAuth();
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $reference = $body['reference'] ?? '';
        $plan      = $body['plan']      ?? '';

        if (!$reference || !isset(self::PLAN_LIMITS[$plan])) {
            Response::error('reference and plan (basic|pro) are required');
        }

        // Verify with Paystack
        $ch = curl_init("https://api.paystack.co/transaction/verify/{$reference}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . PAYSTACK_SECRET],
        ]);
        $res  = json_decode(curl_exec($ch), true);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || ($res['data']['status'] ?? '') !== 'success') {
            Response::error('Payment verification failed', 400);
        }

        $limit = self::PLAN_LIMITS[$plan];
        DB::query('UPDATE users SET plan = ?, ai_limit = ? WHERE id = ?', [$plan, $limit, $user['id']]);

        Response::json([
            'plan'     => $plan,
            'ai_limit' => $limit,
            'ai_used'  => (int) $user['ai_used'],
        ]);
    }

    public static function savePushToken(): void {
        $user  = requireAuth();
        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $token = $body['push_token'] ?? '';

        if (!$token) Response::error('push_token is required');

        DB::query('UPDATE users SET push_token = ? WHERE id = ?', [$token, $user['id']]);
        Response::json(['message' => 'Push token saved']);
    }
}
