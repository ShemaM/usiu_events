<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $full_name = trim($_POST['full_name'] ?? '');
    $club_affiliation = trim($_POST['club_affiliation'] ?? '');

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters long.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if ($password !== $password_confirm) {
        $errors[] = "Passwords do not match.";
    }

    // Check if username exists (using the correct column name 'username' from your schema)
    $db->query("SELECT user_id FROM users WHERE username = :username");
    $db->bind(':username', $username);
    if ($db->single()) {
        $errors[] = "Username is already taken.";
    }

    // Check if email exists (using the correct column name 'email' from your schema)
    $db->query("SELECT user_id FROM users WHERE email = :email");
    $db->bind(':email', $email);
    if ($db->single()) {
        $errors[] = "An account with this email already exists.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            $db->beginTransaction();
            
            $db->query("INSERT INTO users (username, email, password, full_name, club_affiliation, role) 
                        VALUES (:username, :email, :password, :full_name, :club_affiliation, 'student')");
            
            $db->bind(':username', $username);
            $db->bind(':email', $email);
            $db->bind(':password', $hashed_password);
            $db->bind(':full_name', $full_name);
            $db->bind(':club_affiliation', $club_affiliation);
            
            if ($db->execute()) {
                $db->commit();
                
                // Get the newly created user
                $db->query("SELECT user_id, username, role FROM users WHERE email = :email");
                $db->bind(':email', $email);
                $user = $db->single();
                
                // Set session variables
                $_SESSION['user_id'] = $user->user_id;
                $_SESSION['username'] = $user->username;
                $_SESSION['role'] = $user->role;
                
                // Set success message
                $_SESSION['success_message'] = "Registration successful! Welcome to USIU Events.";
                header("Location: index.php");
                exit();
            } else {
                $db->rollBack();
                $errors[] = "Registration failed. Please try again.";
            }
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Registration error: " . $e->getMessage());
            $errors[] = "Something went wrong. Please try again later.";
        }
    }
}

// Clear any existing success message
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - USIU Events</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'pages/header.php'; ?>
    
    <div class="container auth-container">
        <div class="auth-card">
            <h1>Create an Account</h1>
            <p>Join our community to register for campus events</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Username*</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                           required minlength="4">
                </div>
                
                <div class="form-group">
                    <label for="email">Email*</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($full_name ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="club_affiliation">Club Affiliation (if any)</label>
                    <input type="text" id="club_affiliation" name="club_affiliation" 
                           value="<?php echo htmlspecialchars($club_affiliation ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password*</label>
                    <input type="password" id="password" name="password" 
                           required minlength="8">
                    <small class="form-text">Minimum 8 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirm Password*</label>
                    <input type="password" id="password_confirm" name="password_confirm" 
                           required minlength="8">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Register
                </button>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Log in here</a></p>
                </div>
            </form>
        </div>
    </div>

    <?php include 'pages/footer.php'; ?>
</body>
</html>