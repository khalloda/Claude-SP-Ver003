<?php

/**
 * Simple Database Connection Test Script
 * Standalone script to test database connection without framework dependencies
 */

// Database configuration (copy from .env file)
$host = 'p3nlmysql13plsk.secureserver.net';
$port = 3066;
$database = 'claudecode_mi';
$username = 'sp';
$password = 'Mi@SP@123';

echo "<h2>Database Connection Test</h2>";
echo "<pre>";
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
    $working_connection = $pdo1;
    $working_dsn = $dsn1;
    $pdo1 = null;
} catch (PDOException $e) {
    echo "❌ FAILED - " . $e->getMessage() . "\n";
    $working_connection = null;
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
    if (!isset($working_connection)) {
        $working_connection = $pdo2;
        $working_dsn = $dsn2;
    }
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
    if (!isset($working_connection)) {
        $working_connection = $pdo3;
        $working_dsn = $dsn3;
    }
    $pdo3 = null;
} catch (PDOException $e) {
    echo "❌ FAILED - " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Check if tables exist (if any connection worked)
echo "Test 4: Checking if database has tables\n";
if (isset($working_dsn)) {
    try {
        $pdo = new PDO($working_dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10
        ]);
        
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "⚠️  Database connected but no tables found. You need to import schema.sql\n";
            echo "   Tables needed: users, clients, products, quotes, sales_orders, invoices, etc.\n";
        } else {
            echo "✅ Database connected and has " . count($tables) . " tables:\n";
            foreach ($tables as $table) {
                echo "   - " . $table . "\n";
            }
            
            // Check if users table has data
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                $userCount = $stmt->fetchColumn();
                echo "\n👤 Users table has {$userCount} records\n";
                
                if ($userCount > 0) {
                    $stmt = $pdo->query("SELECT email, role FROM users LIMIT 5");
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo "   Sample users:\n";
                    foreach ($users as $user) {
                        echo "   - " . $user['email'] . " (" . $user['role'] . ")\n";
                    }
                }
            } catch (PDOException $e) {
                echo "⚠️  Could not check users table: " . $e->getMessage() . "\n";
            }
        }
    } catch (PDOException $e) {
        echo "❌ Could not check tables: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Could not establish any database connection\n";
}

echo "\nRecommendations:\n";
echo "================\n";

if (isset($working_dsn)) {
    if ($working_dsn === $dsn1) {
        echo "✅ Use hostname:port format in DSN (modify Database.php to use this format)\n";
    } elseif ($working_dsn === $dsn2) {
        echo "✅ Use separate port parameter in DSN (current Database.php should work)\n";
    } elseif ($working_dsn === $dsn3) {
        echo "✅ Don't specify port in DSN (modify Database.php to remove port)\n";
    }
    
    echo "\n📋 Next steps:\n";
    echo "1. If no tables exist, import schema.sql to create database structure\n";
    echo "2. If tables exist but no users, run the setup script to create admin user\n";
    echo "3. If everything looks good, try logging in at the main site\n";
} else {
    echo "❌ None of the connection methods worked. Check:\n";
    echo "   - Verify the hostname is correct\n";
    echo "   - Verify the port is open and accessible\n";
    echo "   - Check if your hosting provider requires a specific connection method\n";
    echo "   - Contact your hosting provider for MySQL connection details\n";
    echo "   - Verify the database name, username, and password are correct\n";
}

echo "</pre>";
?>