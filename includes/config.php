<?php
/**
 * FixFlow configuration.
 * For XAMPP the defaults are host localhost, user root, and an empty password.
 * Change these three lines if your MySQL account is different.
 */

date_default_timezone_set('Asia/Karachi');

function env_value($key, $default)
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

define('DB_HOST', env_value('DB_HOST', 'localhost'));
define('DB_PORT', env_value('DB_PORT', '3306'));
define('DB_NAME', env_value('DB_NAME', 'fixflow'));
define('DB_USER', env_value('DB_USER', 'root'));
define('DB_PASS', env_value('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');
define('DB_SSL', env_value('DB_SSL', '0') === '1');

define('APP_NAME', 'FixFlow');
define('UNIVERSITY', 'Thal University Bhakkar');
define('DEMO_PASSWORD', 'Fixflow@123');
define('MAX_UPLOAD', 2 * 1024 * 1024);
define('PER_PAGE', 8);
