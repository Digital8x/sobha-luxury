<?php
/**
 * SOBHA REALTY LUXURY — DATABASE & CRM CONFIGURATION
 * For cPanel MySQL / Local Development
 */

// Prevent direct script access if included improperly
if (!defined('SOBHA_INIT')) {
    define('SOBHA_INIT', true);
}

/**
 * Lightweight Zero-Dependency .env Loader
 * Safely parses .env files in root or current directory without exposing to version control.
 */
function loadSobhaEnv($envPath = null) {
    if ($envPath === null) {
        $possiblePaths = [
            dirname(__DIR__) . '/.env',
            __DIR__ . '/.env',
            dirname(__DIR__) . '/.env.local'
        ];
        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_readable($path)) {
                $envPath = $path;
                break;
            }
        }
    }

    if (!$envPath || !file_exists($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments and empty lines
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Strip surrounding quotes if present
            $len = strlen($value);
            if ($len >= 2 && (
                ($value[0] === '"' && $value[$len - 1] === '"') ||
                ($value[0] === "'" && $value[$len - 1] === "'")
            )) {
                $value = substr($value, 1, -1);
            }

            if (!empty($key)) {
                if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }
}

// Automatically load .env on initialization
loadSobhaEnv();

/**
 * Safe helper to read environment variable with fallback
 */
function getSobhaEnv($key, $default = null) {
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    return $default;
}

// Database Credentials loaded strictly from environment (.env)
define('DB_HOST', getSobhaEnv('DB_HOST', 'localhost'));
define('DB_NAME', getSobhaEnv('DB_NAME', 'sobha_luxury_crm'));
define('DB_USER', getSobhaEnv('DB_USER', 'root'));
define('DB_PASS', getSobhaEnv('DB_PASS', ''));
define('DB_CHARSET', getSobhaEnv('DB_CHARSET', 'utf8mb4'));

// Admin Configuration & Fallback Access
define('DEFAULT_ADMIN_EMAILS', getSobhaEnv('ADMIN_EMAILS', 'info@sobha-luxury.com'));
define('FALLBACK_ADMIN_USER', getSobhaEnv('FALLBACK_ADMIN_USER', ''));
define('FALLBACK_ADMIN_PASS', getSobhaEnv('FALLBACK_ADMIN_PASS', ''));

// SMTP Environment Defaults (Overrides or falls back to crm_settings table)
define('ENV_SMTP_HOST', getSobhaEnv('SMTP_HOST', ''));
define('ENV_SMTP_PORT', getSobhaEnv('SMTP_PORT', ''));
define('ENV_SMTP_SECURE', getSobhaEnv('SMTP_SECURE', ''));
define('ENV_SMTP_USER', getSobhaEnv('SMTP_USER', ''));
define('ENV_SMTP_PASS', getSobhaEnv('SMTP_PASS', ''));
define('ENV_SMTP_FROM_EMAIL', getSobhaEnv('SMTP_FROM_EMAIL', ''));
define('ENV_SMTP_FROM_NAME', getSobhaEnv('SMTP_FROM_NAME', ''));

// Session Security Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Lax');

    // Enable secure cookies if HTTPS
    if ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

/**
 * Returns a shared PDO instance
 */
function getDbConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log silently and fail gracefully
            error_log("[Sobha CRM DB Error] " . $e->getMessage());
            return null;
        }
    }
    return $pdo;
}

/**
 * Fetches setting from crm_settings table with fallback
 */
function getCrmSetting($key, $default = '') {
    $pdo = getDbConnection();
    if (!$pdo) return $default;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM crm_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}
