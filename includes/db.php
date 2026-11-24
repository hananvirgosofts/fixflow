<?php

function pdo_options()
{
    return [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
}

function connect_database($quiet = false)
{
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = pdo_options();
    if (DB_SSL) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
    }
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        try {
            $pdo->exec("SET time_zone = '+05:00'");
        } catch (PDOException $ignored) {
            // Named zone tables are optional; the offset still works on XAMPP.
        }
        return $pdo;
    } catch (PDOException $e) {
        if ($quiet) {
            return null;
        }
        fail_database($e->getMessage());
    }
}

function db()
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = connect_database(false);
    }
    return $pdo;
}

function explain_db_error($message)
{
    $lower = strtolower($message);
    if (strpos($lower, 'unknown database') !== false) {
        return 'The fixflow database has not been created yet. Open install.php to set it up.';
    }
    if (strpos($lower, 'access denied') !== false) {
        return 'MySQL refused the username or password. Check includes/config.php, or the DB_USER and DB_PASS variables on Vercel.';
    }
    if (strpos($lower, 'connection refused') !== false || strpos($lower, "can't connect") !== false || strpos($lower, 'no such file') !== false) {
        return 'MySQL is not running. Start MySQL from XAMPP, then reload this page.';
    }
    if (strpos($lower, 'base table or view not found') !== false || strpos($lower, "doesn't exist") !== false) {
        return 'The FixFlow tables are missing. Open install.php to create them.';
    }
    return 'FixFlow could not connect to MySQL.';
}

function fail_database($detail)
{
    http_response_code(503);
    $safe = htmlspecialchars((string) $detail, ENT_QUOTES, 'UTF-8');
    $why = htmlspecialchars(explain_db_error($detail), ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Database unavailable · FixFlow</title>';
    echo '<link rel="stylesheet" href="assets/css/style.css">';
    echo '</head><body class="public"><main class="install-wrap" id="content">';
    echo '<a class="wordmark" href="index.php"><span class="mark"></span>FixFlow</a>';
    echo '<h1>Database unavailable</h1>';
    echo '<p>' . $why . '</p>';
    echo '<p><a class="ff-btn" href="install.php">Open installation</a></p>';
    echo '<details class="demo"><summary>Technical detail</summary><p>' . $safe . '</p></details>';
    echo '</main></body></html>';
    exit;
}
