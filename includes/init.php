<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => $https,
    ]);
    ini_set('session.use_strict_mode', '1');
    if (PHP_VERSION_ID >= 80000 && getenv('FIXFLOW_SESSION') === 'database') {
        $sessionPdo = connect_database(true);
        if ($sessionPdo) {
            require_once __DIR__ . '/session-handler.php';
            session_set_save_handler(new FixflowSessionHandler($sessionPdo), true);
        }
    }
    session_start();
}

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
}

require_once __DIR__ . '/functions.php';
