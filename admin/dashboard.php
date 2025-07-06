<?php
// Start the session
session_start();

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch statistics for the dashboard
$totalUsers = getTotalUsers($pdo);
$totalEvents = getTotalEvents($pdo);
$totalRegistrations = getTotalRegistrations($pdo);
$recentEvents = getRecentEvents($pdo, 5);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - USIU Events</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>

<div class="admin-wrapper">
    <aside class="sidebar">
        <h2>USIU Admin</h2>
        <ul>
            <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            <li><a href="manage-event.php">🗓️ Manage Events</a></li>
            <li><a href="#">👥 Manage Users</a></li>
            <li><a href="#">💬 Manage Comments</a></li>
            <li><a href="../index.php" target="_blank">🌐 View Site</a></li>
            <li><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</h1>
        </div>

        <section class="stats-cards">
            <div class="card">
                <h3>Total Users</h3>
                <p><?php echo $totalUsers; ?></p>
            </div>
            <div class="card">
                <h3>Total Events</h3>
                <p><?php echo $totalEvents; ?></p>
            </div>
            <div class="card">
                <h3>Total Registrations</h3>
                <p><?php echo $totalRegistrations; ?></p>
            </div>
        </section>

        <section class="recent-activity">
            <h2>Recent Events</h2>
            <table>
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentEvents)): ?>
                        <?php foreach ($recentEvents as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['name']); ?></td>
                                <td><?php echo date('F j, Y', strtotime($event['date'])); ?></td>
                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No recent events found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

</body>
</html>