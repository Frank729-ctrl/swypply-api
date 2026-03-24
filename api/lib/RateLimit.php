<?php
require_once __DIR__ . '/DB.php';
require_once __DIR__ . '/Response.php';

class RateLimit {
    // [max_attempts, window_seconds]
    private const RULES = [
        'login'    => [5,  15 * 60],  // 5 tries per 15 min
        'register' => [3,  60 * 60],  // 3 per hour
        'admin'    => [5,  15 * 60],  // 5 per 15 min
        'api'      => [120, 60],      // 120 requests per minute (general)
    ];

    private static function ip(): string {
        // Support proxies/Cloudflare
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return '0.0.0.0';
    }

    public static function check(string $rule): void {
        [$max, $window] = self::RULES[$rule] ?? [60, 60];
        $hash = hash('sha256', $rule . ':' . self::ip());
        $now  = time();

        // Purge expired records (runs occasionally to avoid table bloat)
        if (random_int(1, 20) === 1) {
            DB::query('DELETE FROM rate_limits WHERE expires_at < ?', [$now]);
        }

        $row = DB::find('SELECT attempts, expires_at FROM rate_limits WHERE id_hash = ?', [$hash]);

        if (!$row) {
            DB::insert(
                'INSERT INTO rate_limits (id_hash, attempts, expires_at) VALUES (?, 1, ?) RETURNING id_hash',
                [$hash, $now + $window]
            );
            return;
        }

        // Window expired — reset
        if ($row['expires_at'] < $now) {
            DB::query(
                'UPDATE rate_limits SET attempts = 1, expires_at = ? WHERE id_hash = ?',
                [$now + $window, $hash]
            );
            return;
        }

        if ((int) $row['attempts'] >= $max) {
            $wait = $row['expires_at'] - $now;
            header('Retry-After: ' . $wait);
            Response::error("Too many attempts. Try again in {$wait}s.", 429);
        }

        DB::query('UPDATE rate_limits SET attempts = attempts + 1 WHERE id_hash = ?', [$hash]);
    }

    public static function clear(string $rule): void {
        $hash = hash('sha256', $rule . ':' . self::ip());
        DB::query('DELETE FROM rate_limits WHERE id_hash = ?', [$hash]);
    }
}
