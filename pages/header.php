<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : ''; ?>USIU Campus Events</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="../index.php" class="logo">USIU Events</a>
            <div class="nav-links" id="nav-links">
                <a href="../index.php" <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'class="active"' : ''; ?>>Home</a>
                <a href="events.php" <?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'class="active"' : ''; ?>>Events</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'class="active"' : ''; ?>>Profile</a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="../admin/dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a>
                    <?php endif; ?>
                    <a href="../includes/logout.php">Logout</a>
                <?php else: ?>
                    <a href="../login.php" <?php echo basename($_SERVER['PHP_SELF']) === 'login.php' ? 'class="active"' : ''; ?>>Login</a>
                    <a href="../register.php" <?php echo basename($_SERVER['PHP_SELF']) === 'register.php' ? 'class="active"' : ''; ?>>Register</a>
                <?php endif; ?>
            </div>
            <div class="mobile-menu" id="mobile-menu">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php
    if (isset($_SESSION['flash_message'])) {
        echo '<div class="flash-message ' . $_SESSION['flash_message_class'] . '">' . $_SESSION['flash_message'] . '</div>';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_message_class']);
    }
    ?>

    <!-- Main Content -->
    <main class="main-content"></main>