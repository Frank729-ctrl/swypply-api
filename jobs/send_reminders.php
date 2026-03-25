<?php
/**
 * Subscription expiry reminder cron job.
 *
 * Run daily via cron:
 *   0 9 * * * php /path/to/swypply-api/jobs/send_reminders.php >> /var/log/swypply_reminders.log 2>&1
 *
 * Sends ONE reminder email exactly 14 days before subscription expires.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/lib/DB.php';
require_once __DIR__ . '/../api/lib/Mailer.php';
require_once __DIR__ . '/../api/mail/MailService.php';

$windowStart = date('Y-m-d 00:00:00', strtotime('+14 days'));
$windowEnd   = date('Y-m-d 23:59:59', strtotime('+14 days'));

$users = DB::findAll(
    "SELECT id, name, email, plan
     FROM users
     WHERE plan IN ('basic', 'pro')
       AND subscription_expires_at BETWEEN ? AND ?",
    [$windowStart, $windowEnd]
);

foreach ($users as $user) {
    try {
        MailService::sendSubscriptionExpiring(
            $user['email'],
            $user['name'],
            ucfirst($user['plan']),
            14
        );
        echo date('Y-m-d H:i:s') . " [OK] Sent 14-day reminder to {$user['email']}\n";
    } catch (Throwable $e) {
        echo date('Y-m-d H:i:s') . " [ERR] {$user['email']}: {$e->getMessage()}\n";
    }
}
