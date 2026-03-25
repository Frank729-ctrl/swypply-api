<?php
require_once __DIR__ . '/../lib/Mailer.php';

/**
 * MailService — renders templates and dispatches emails.
 * All public methods are static for easy calling from controllers.
 */
class MailService {

    // ── Auth ─────────────────────────────────────────────────────────────────

    public static function sendVerificationCode(string $email, string $name, string $code): void {
        $html = self::render('verification_code', [
            'name' => $name,
            'code' => $code,
        ]);
        self::send($email, $name, 'Your Swypply verification code', $html);
    }

    public static function sendWelcome(string $email, string $name): void {
        $html = self::render('welcome', ['name' => $name]);
        self::send($email, $name, "Welcome to Swypply, {$name}!", $html);
    }

    // ── Subscriptions ────────────────────────────────────────────────────────

    public static function sendSubscriptionConfirmed(string $email, string $name, string $plan): void {
        $html = self::render('subscription_confirmed', [
            'name' => $name,
            'plan' => ucfirst($plan),
        ]);
        self::send($email, $name, "You're on the {$plan} plan — welcome!", $html);
    }

    public static function sendSubscriptionExpiring(string $email, string $name, string $plan, int $daysLeft): void {
        $html = self::render('subscription_expiring', [
            'name'     => $name,
            'plan'     => ucfirst($plan),
            'daysLeft' => $daysLeft,
        ]);
        $subject = $daysLeft === 1
            ? "Your Swypply subscription expires tomorrow"
            : "Your Swypply subscription expires in {$daysLeft} days";
        self::send($email, $name, $subject, $html);
    }

    // ── Notifications ────────────────────────────────────────────────────────

    public static function sendNotification(string $email, string $name, string $subject, string $message, string $ctaText = 'Open Swypply', string $ctaUrl = ''): void {
        $html = self::render('notification', [
            'name'    => $name,
            'message' => $message,
            'ctaText' => $ctaText,
            'ctaUrl'  => $ctaUrl ?: (defined('APP_URL') ? APP_URL : 'https://swypply.com'),
        ]);
        self::send($email, $name, $subject, $html);
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private static function render(string $template, array $vars = []): string {
        $file = __DIR__ . "/templates/{$template}.php";
        if (!file_exists($file)) {
            throw new RuntimeException("Mail template not found: {$template}");
        }
        extract($vars);
        ob_start();
        include $file;
        return ob_get_clean();
    }

    private static function send(string $toEmail, string $toName, string $subject, string $html): void {
        try {
            (new Mailer())->send($toEmail, $toName, $subject, $html);
        } catch (Throwable $e) {
            // Log but don't crash the request if email fails
            error_log("[Swypply Mail] Failed to send '{$subject}' to {$toEmail}: " . $e->getMessage());
        }
    }
}
