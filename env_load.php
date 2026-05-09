<?php
/**
 * Load key=value pairs from .env into getenv() / $_ENV (PHP does not load .env by itself).
 * Call before db.php connects. Paths are relative to this file (__DIR__).
 */
function saudi_load_dotenv(?string $path = null): void
{
    if ($path === null) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . '.env';
    }
    if (!is_readable($path)) {
        return;
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        return;
    }
    // Strip UTF-8 BOM
    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
    foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
        $line = trim($line);
        if ($line === '' || (isset($line[0]) && $line[0] === '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        $pos = strpos($line, '=');
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        if ($key === '') {
            continue;
        }
        if (
            strlen($value) >= 2
            && (
                ($value[0] === '"' && substr($value, -1) === '"')
                || ($value[0] === "'" && substr($value, -1) === "'")
            )
        ) {
            $value = substr($value, 1, -1);
        }
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    // Back-compat: bare "URL=" used Supabase JDBC-style string wrongly named
    if (empty($_ENV['DATABASE_URL'] ?? '') && !empty($_ENV['URL'] ?? '')) {
        $_ENV['DATABASE_URL'] = $_ENV['URL'];
        putenv('DATABASE_URL=' . $_ENV['DATABASE_URL']);
        $_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'];
    }
}
