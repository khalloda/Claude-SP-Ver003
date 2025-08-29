<?php

/**
 * File: app/config/Database.php
 * Purpose: Secure database connection manager with proper error handling
 * Depends on: App\Config\Config
 * Notes: Uses PDO with prepared statements only; no credential exposure
 */

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static array $queryLog = [];
    private static bool $logQueries = false;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }

        return self::$instance;
    }

    private static function connect(): void
    {
        $config = Config::get('database');
        
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

        try {
            self::$instance = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );

            self::$logQueries = Config::get('app.debug', false);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            
            if (Config::get('app.debug')) {
                throw new \RuntimeException("Database connection failed: " . $e->getMessage());
            } else {
                throw new \RuntimeException("Database connection failed. Please check configuration.");
            }
        }
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $startTime = microtime(true);
        
        try {
            $stmt = self::getInstance()->prepare($sql);
            $stmt->execute($params);
            
            if (self::$logQueries) {
                self::$queryLog[] = [
                    'sql' => $sql,
                    'params' => $params,
                    'time' => microtime(true) - $startTime
                ];
            }
            
            return $stmt;
            
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage() . " SQL: " . $sql);
            
            if (Config::get('app.debug')) {
                throw new \RuntimeException("Query failed: " . $e->getMessage() . " SQL: " . $sql);
            } else {
                throw new \RuntimeException("A database error occurred. Please try again.");
            }
        }
    }

    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    public static function rollback(): bool
    {
        return self::getInstance()->rollBack();
    }

    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    public static function transaction(callable $callback)
    {
        self::beginTransaction();
        
        try {
            $result = $callback();
            self::commit();
            return $result;
        } catch (\Exception $e) {
            self::rollback();
            throw $e;
        }
    }

    public static function getQueryLog(): array
    {
        return self::$queryLog;
    }

    public static function clearQueryLog(): void
    {
        self::$queryLog = [];
    }

    // Utility methods for common operations
    public static function select(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function selectOne(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function insert(string $table, array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn($field) => ":{$field}", $fields);
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        self::query($sql, $data);
        return (int) self::lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $fields = array_keys($data);
        $setClause = implode(', ', array_map(fn($field) => "{$field} = :{$field}", $fields));
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        return self::query($sql, array_merge($data, $whereParams))->rowCount();
    }

    public static function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return self::query($sql, $whereParams)->rowCount();
    }
}