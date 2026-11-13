<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $pdo = null;
    private static string $driver;

    public static function connect(array $config): void
    {
        self::$driver = $config['driver'];

        if (self::$driver === 'sqlite') {
            self::$pdo = new PDO('sqlite:' . $config['sqlite_path']);
            self::$pdo->exec('PRAGMA foreign_keys=ON; PRAGMA busy_timeout=10000;');
        } elseif (self::$driver === 'mysql') {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $config['mysql_host'],
                (int) $config['mysql_port'],
                $config['mysql_database']
            );
            self::$pdo = new PDO($dsn, $config['mysql_user'], $config['mysql_password']);
            self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } else {
            throw new RuntimeException('Driver cơ sở dữ liệu không được hỗ trợ.');
        }

        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public static function pdo(): PDO
    {
        return self::$pdo;
    }

    public static function disconnect(): void
    {
        self::$pdo = null;
    }

    public static function driver(): string
    {
        return self::$driver;
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function one(string $sql, array $params = []): ?array
    {
        return self::run($sql, $params)->fetch() ?: null;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $values): int
    {
        $fields = array_keys($values);
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, implode(', ', $fields), $placeholders);

        self::run($sql, array_values($values));
        return (int) self::$pdo->lastInsertId();
    }

    public static function transaction(callable $callback): mixed
    {
        if (self::$driver === 'sqlite') {
            self::$pdo->exec('BEGIN IMMEDIATE');
        } else {
            self::$pdo->beginTransaction();
            self::run('SELECT id FROM roles WHERE id=1 FOR UPDATE');
        }

        try {
            $result = $callback();
            if (self::$driver === 'sqlite') {
                self::$pdo->exec('COMMIT');
            } else {
                self::$pdo->commit();
            }
            return $result;
        } catch (Throwable $e) {
            if (self::$driver === 'sqlite') {
                self::$pdo->exec('ROLLBACK');
            } elseif (self::$pdo->inTransaction()) {
                self::$pdo->rollBack();
            }
            throw $e;
        }
    }
}
