<?php

require_once __DIR__ . '/env_load.php';
saudi_load_dotenv(__DIR__ . DIRECTORY_SEPARATOR . '.env');


function saudi_parse_postgres_database_url(string $url): PDO
{
    $parts = parse_url($url);
    if ($parts === false || empty($parts['host'])) {
        throw new InvalidArgumentException('Invalid DATABASE_URL');
    }
    $host = $parts['host'];
    $port = isset($parts['port']) ? (string) $parts['port'] : '5432';
    $path = isset($parts['path']) ? $parts['path'] : '/postgres';
    $dbname = ltrim($path, '/') ?: 'postgres';
    $user = $parts['user'] ?? 'postgres';
    $password = isset($parts['pass']) ? urldecode((string) $parts['pass']) : '';

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";
    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
}

function saudi_connect_pgsql_supabase(): PDO
{
    $host = getenv('SUPABASE_DB_HOST') ?: ($_ENV['SUPABASE_DB_HOST'] ?? 'db.xxefwtumjbfhpdysbwrf.supabase.co');
    $port = getenv('SUPABASE_DB_PORT') ?: ($_ENV['SUPABASE_DB_PORT'] ?? '5432');
    $dbname = getenv('SUPABASE_DB_NAME') ?: ($_ENV['SUPABASE_DB_NAME'] ?? 'postgres');
    $username = getenv('SUPABASE_DB_USER') ?: ($_ENV['SUPABASE_DB_USER'] ?? 'postgres');
    $password = getenv('SUPABASE_DB_PASSWORD');
    if ($password === false || $password === '') {
        $password = $_ENV['SUPABASE_DB_PASSWORD'] ?? '';
    }
    if ($password === '') {
        throw new RuntimeException(
            'Supabase: set SUPABASE_DB_PASSWORD or DATABASE_URL (postgres).'
        );
    }
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";
    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
}

try {
    $databaseUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? '');
    if (is_string($databaseUrl) && $databaseUrl !== '') {
        $scheme = parse_url($databaseUrl, PHP_URL_SCHEME);
        if (in_array($scheme, ['postgres', 'postgresql'], true)) {
            $pdo = saudi_parse_postgres_database_url($databaseUrl);
        } else {
            throw new RuntimeException('DATABASE_URL must be a postgresql:// URL for this project.');
        }
    } elseif (getenv('DB_DRIVER') === 'pgsql' || ($_ENV['DB_DRIVER'] ?? '') === 'pgsql') {
        $pdo = saudi_connect_pgsql_supabase();
    } else {
        $host = getenv('MYSQL_HOST') ?: 'localhost';
        $dbname = getenv('MYSQL_DATABASE') ?: 'saudi_arabia_db';
        $username = getenv('MYSQL_USER') ?: 'root';
        $password = getenv('MYSQL_PASSWORD');
        $password = $password !== false ? $password : '';

        $pdo = new PDO(
            "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
            $username,
            $password
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (Throwable $e) {
    $msg = $e->getMessage();
    $hint = '';
    if (stripos($msg, 'could not find driver') !== false) {
        $hint = ' · محليًا (XAMPP): فعّل في php.ini السطر extension=pdo_pgsql وأعد تشغيل Apache.';
        if (getenv('RAILWAY_ENVIRONMENT') !== false || getenv('RAILWAY_PROJECT_ID') !== false) {
            $hint = ' · على Railway: تأكد أن النشر يستخدم Dockerfile المضمن في المشروع (يثبت pdo_pgsql)، ثم أعد النشر.';
        }
    }
    die('فشل الاتصال بقاعدة البيانات: ' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . $hint);
}
