<?php
require_once __DIR__ . '/../../config.php';

class DB {
    private static ?PDO $pdo = null;

    public static function get(): PDO {
        if (!self::$pdo) {
            // Supabase / Railway provide a full DATABASE_URL
            if (DATABASE_URL) {
                $p   = parse_url(DATABASE_URL);
                $dsn = sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    $p['host'],
                    $p['port'] ?? 5432,
                    ltrim($p['path'], '/')
                );
                $user = $p['user'] ?? '';
                $pass = $p['pass'] ?? '';
            } else {
                $dsn  = sprintf('pgsql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, DB_NAME);
                $user = DB_USER;
                $pass = DB_PASS;
            }

            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function find(string $sql, array $params = []): ?array {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    public static function findAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $sql, array $params = []): string {
        // PostgreSQL needs RETURNING id to get last insert id
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (string) ($row['id'] ?? self::get()->lastInsertId());
    }
}
