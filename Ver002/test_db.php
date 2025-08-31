<?php

/**
 * Database Connection Test Script
 * Use this to debug database connection issues
 */

// Load environment variables
require_once __DIR__ . '/app/config/Env.php';
App\Config\Env::load();

// Get database configuration
$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$database = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

echo "Database Connection Test\n";
echo "========================\n";
echo "Host: " . $host . "\n";
echo "Port: " . $port . "\n";
echo "Database: " . $database . "\n";
echo "Username: " . $username . "\n";
echo "Password: " . (empty($password) ? 'NOT SET' : 'SET (' . strlen($password) . ' chars)') . "\n\n";

// Test 1: Try connection with port in hostname
echo "Test 1: Connection with port in hostname\n";
$dsn1 = "mysql:host={$host}:{$port};dbname={$database};charset=utf8mb4";
try {
    $pdo1 = new PDO($dsn1, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    echo "✅ SUCCESS - Connected with hostname:port format\n";
    $pdo1 = null;
} catch (PDOException $e) {
    echo "❌ FAILED - " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Try connection with separate port parameter
echo "Test 2: Connection with separate port parameter\n";
$dsn2 = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
try {
    $pdo2 = new PDO($dsn2, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    echo "✅ SUCCESS - Connected with separate port parameter\n";
    $pdo2 = null;
} catch (PDOException $e) {
    echo "❌ FAILED - " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Try connection without port (use default)
echo "Test 3: Connection without port specification\n";
$dsn3 = "mysql:host={$host};dbname={$database};charset=utf8mb4";
try {
    $pdo3 = new PDO($dsn3, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    echo "✅ SUCCESS - Connected without port specification\n";
    $pdo3 = null;
} catch (PDOException $e) {
    echo "❌ FAILED - " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Check if tables exist (if any connection worked)
echo "Test 4: Checking if database has tables\n";
$successful_dsn = null;
foreach ([$dsn1, $dsn2, $dsn3] as $dsn) {
    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10
        ]);
        $successful_dsn = $dsn;
        break;
    } catch (PDOException $e) {
        // Try next DSN
    }
}

if ($successful_dsn) {
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "⚠️  Database connected but no tables found. You need to import schema.sql\n";
        } else {
            echo "✅ Database connected and has tables: " . implode(', ', $tables) . "\n";
        }
    } catch (PDOException $e) {
        echo "❌ Could not check tables: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Could not establish any database connection\n";
}

echo "\nRecommendations:\n";
echo "================\n";

if ($successful_dsn === $dsn1) {
    echo "✅ Use hostname:port format in DSN\n";
} elseif ($successful_dsn === $dsn2) {
    echo "✅ Use separate port parameter in DSN\n";
} elseif ($successful_dsn === $dsn3) {
    echo "✅ Don't specify port in DSN\n";
} else {
    echo "❌ Check your database credentials and server settings\n";
    echo "   - Verify the hostname is correct\n";
    echo "   - Verify the port is open and accessible\n";
    echo "   - Check if your hosting provider requires a specific connection method\n";
    echo "   - Contact your hosting provider for MySQL connection details\n";
}