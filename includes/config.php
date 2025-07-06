<?php
// Application Configuration Settings

// Database Configuration

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'secret');
define('DB_NAME', 'campuseventmanagement');

// Application Settings
define('APP_NAME', 'USIU Campus Events');
define('APP_ROOT', dirname(dirname(__FILE__)));
define('APP_URL', 'http://localhost/campus-events'); // Replace with your project URL

// Email Configuration (for notifications)
define('EMAIL_FROM', 'events@usiu.ac.ke');
define('EMAIL_HOST', 'smtp.usiu.ac.ke'); // Replace with your SMTP host
define('EMAIL_USERNAME', 'events@usiu.ac.ke');
define('EMAIL_PASSWORD', 'email_password_here');
define('EMAIL_PORT', 587);

// Session Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Error Reporting (set to 0 in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Timezone Setting
date_default_timezone_set('Africa/Nairobi');

// Security Settings
define('ENCRYPTION_KEY', 'your-secure-key-here'); // For password encryption

// File Upload Settings
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_DIR', APP_ROOT . '/assets/uploads/');

// Include the functions file
require_once 'functions.php';
?>