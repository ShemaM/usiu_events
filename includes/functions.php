<?php
// General Utility Functions
/**
 * Gets the total count of users from the database.
 * @param PDO $pdo The PDO database connection object.
 * @return int Total number of users.
 */
function getTotalUsers(PDO $pdo): int {
    $stmt = $pdo->query("SELECT COUNT(id) FROM users");
    return (int) $stmt->fetchColumn();
}

/**
 * Gets the total count of events from the database.
 * @param PDO $pdo The PDO database connection object.
 * @return int Total number of events.
 */
function getTotalEvents(PDO $pdo): int {
    $stmt = $pdo->query("SELECT COUNT(id) FROM events");
    return (int) $stmt->fetchColumn();
}

/**
 * Gets the total count of registrations from the database.
 * @param PDO $pdo The PDO database connection object.
 * @return int Total number of registrations.
 */
function getTotalRegistrations(PDO $pdo): int {
    $stmt = $pdo->query("SELECT COUNT(id) FROM registrations");
    return (int) $stmt->fetchColumn();
}

/**
 * Gets a specified number of recent events.
 * @param PDO $pdo The PDO database connection object.
 * @param int $limit The number of recent events to fetch.
 * @return array An array of recent events.
 */
function getRecentEvents(PDO $pdo, int $limit = 5): array {
    $stmt = $pdo->prepare("SELECT name, date, location FROM events ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Redirect to another page
function redirect($url) {
    header("Location: " . APP_URL . '/' . ltrim($url, '/'));
    exit();
}

// Flash message helper
function flash($name = '', $message = '', $class = 'alert alert-success') {
    if(!empty($name)) {
        if(!empty($message) && empty($_SESSION[$name])) {
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } elseif(empty($message) && !empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
            echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

// Generate CSRF token
function generateCsrfToken() {
    if(empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Password hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Format date for display
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y \a\t g:i a');
}

// Handle file uploads
function uploadFile($file, $targetDir = UPLOAD_DIR) {
    // Check for errors
    if($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }

    // Check file size
    if($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File too large. Maximum size allowed is ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB');
    }

    // Check file type
    $fileType = mime_content_type($file['tmp_name']);
    if(!in_array($fileType, ALLOWED_FILE_TYPES)) {
        throw new Exception('Invalid file type. Only ' . implode(', ', ALLOWED_FILE_TYPES) . ' are allowed');
    }

    // Create upload directory if it doesn't exist
    if(!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $targetPath = $targetDir . $filename;

    // Move the file
    if(!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to move uploaded file');
    }

    return $filename;
}
?>