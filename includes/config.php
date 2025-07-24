<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session start
session_start();

// Base URL
define('BASE_URL', 'http://localhost/task-management-system');

// Timezone
date_default_timezone_set('UTC');

// Database connection
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_tasksystem');

// SMTP Configuration for Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'jamesweks2019@gmail.com');
define('SMTP_PASSWORD', 'bnfjkfwhiapffcwm'); // 16-char app password
define('SMTP_FROM_EMAIL', 'jamesweks2019@gmail.com'); // same as SMTP_USERNAME
define('SMTP_FROM_NAME', 'Task Management System');
define('SMTP_SECURE', 'tls'); // Critical for Gmail
define('SMTP_DEBUG', 0); // 0 = off, 1 = client messages, 2 = client and server messages. The output on the browser

// Email logging
define('EMAIL_LOG_DIR', __DIR__ . '/../email_logs');
define('EMAIL_LOG_FILE', __DIR__ . '/../email_log.txt');
define('EMAIL_ERROR_LOG', __DIR__ . '/../email_errors.log');