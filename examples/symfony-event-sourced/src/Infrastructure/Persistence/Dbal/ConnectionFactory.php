<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

final class ConnectionFactory
{
    public static function create(string $databaseUrl): Connection
    {
        if (str_starts_with($databaseUrl, 'sqlite:')) {
            return self::createSqlite($databaseUrl);
        }

        $params = parse_url($databaseUrl);

        if ($params === false) {
            throw new \InvalidArgumentException(sprintf('Invalid DATABASE_URL: "%s"', $databaseUrl));
        }

        $scheme = $params['scheme'] ?? '';

        return match (true) {
            str_contains($scheme, 'pgsql'), str_contains($scheme, 'postgres') => self::createPostgres($params),
            str_contains($scheme, 'mysql') => self::createMysql($params),
            default => throw new \InvalidArgumentException(sprintf('Unsupported database scheme: "%s"', $scheme)),
        };
    }

    private static function createSqlite(string $url): Connection
    {
        if (str_contains($url, ':memory:')) {
            return DriverManager::getConnection(['driver' => 'sqlite3', 'memory' => true]);
        }

        $path = preg_replace('#^sqlite:///(.+)$#', '$1', $url) ?? $url;

        return DriverManager::getConnection(['driver' => 'sqlite3', 'path' => $path]);
    }

    /**
     * @param array<string, mixed> $params
     */
    private static function createPostgres(array $params): Connection
    {
        return DriverManager::getConnection([
            'driver' => 'pgsql',
            'host' => $params['host'] ?? 'localhost',
            'port' => $params['port'] ?? 5432,
            'dbname' => ltrim($params['path'] ?? '/wallet', '/'),
            'user' => $params['user'] ?? '',
            'password' => $params['pass'] ?? '',
        ]);
    }

    /**
     * @param array<string, mixed> $params
     */
    private static function createMysql(array $params): Connection
    {
        return DriverManager::getConnection([
            'driver' => 'mysqli',
            'host' => $params['host'] ?? 'localhost',
            'port' => $params['port'] ?? 3306,
            'dbname' => ltrim($params['path'] ?? '/wallet', '/'),
            'user' => $params['user'] ?? '',
            'password' => $params['pass'] ?? '',
        ]);
    }
}
