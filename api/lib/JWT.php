<?php
class JWT {
    // Minimum secret length to prevent weak-key attacks
    private const MIN_SECRET_LEN = 32;

    private static function b64e(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64d(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private static function assertSecret(string $secret): void {
        if (strlen($secret) < self::MIN_SECRET_LEN) {
            throw new RuntimeException('JWT_SECRET must be at least ' . self::MIN_SECRET_LEN . ' characters');
        }
    }

    public static function encode(array $payload, string $secret): string {
        self::assertSecret($secret);
        // Always stamp issued-at for auditability
        $payload['iat'] = $payload['iat'] ?? time();
        $header  = self::b64e(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body    = self::b64e(json_encode($payload));
        $sig     = self::b64e(hash_hmac('sha256', "$header.$body", $secret, true));
        return "$header.$body.$sig";
    }

    public static function decode(string $token, string $secret): ?array {
        self::assertSecret($secret);
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $body, $sig] = $parts;
        $expected = self::b64e(hash_hmac('sha256', "$header.$body", $secret, true));
        if (!hash_equals($expected, $sig)) return null;

        $data = json_decode(self::b64d($body), true);
        if (!is_array($data)) return null;

        // Reject expired tokens
        if (($data['exp'] ?? 0) < time()) return null;

        // Reject tokens issued in the future (clock-skew tolerance: 60 s)
        if (isset($data['iat']) && $data['iat'] > time() + 60) return null;

        return $data;
    }
}
