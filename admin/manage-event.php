<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Authentication: Ensure user is a logged-in admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set up flash messages
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// Handle form submissions (Add, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid CSRF token. Action aborted.";
        header('Location: manage-event.php');
        exit();
    }

    $action = $_POST['action'] ?? '';

    // --- DELETE ACTION ---
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        if (deleteEvent($pdo, $id)) {
            $_SESSION['message'] = "Event deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete event.";
        }
    }
    // --- ADD/UPDATE ACTION ---
    elseif ($action === 'save') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $date = trim($_POST['date']);
        $location = trim($_POST['location']);
        $image_path = $_POST['existing_image'] ?? '';
        $upload_error = false;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $new_image_path = handleImageUpload($_FILES['image']);
            if ($new_image_path) {
                if ($id && !empty($image_path) && file_exists('../' . $image_path)) {
                    unlink('../' . $image_path);
                }
                $image_path = $new_image_path;
            } else {
                $_SESSION['error'] = "Failed to upload image.";
                $upload_error = true;
            }
        }

        if (!$upload_error) {
            if ($id) { // Update
                $stmt = $pdo->prepare("UPDATE events SET name = ?, description = ?, date = ?, location = ?, image_path = ? WHERE id = ?");
                if ($stmt->execute([$name, $description, $date, $location, $image_path, $id])) {
                    $_SESSION['message'] = "Event updated successfully!";
                } else {
                    $_SESSION['error'] = "Failed to update event.";
                }
            } else { // Add
                $stmt = $pdo->prepare("INSERT INTO events (name, description, date, location, image_path) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $description, $date, $location, $image_path])) {
                    $_SESSION['message'] = "Event added successfully!";
                } else {
                    $_SESSION['error'] = "Failed to add event.";
                }
            }
        }
    }
    
    header('Location: manage-event.php');
    exit();
}


// Handle Edit Request (to populate the form)
$edit_event = null;
if (isset($_GET['edit'])) {
    $id_to_edit = (int)$_GET['edit'];
    $edit_event = getEventById($pdo, $id_to_edit);
}

// Helper function to delete an event by ID
function deleteEvent($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    return $stmt->execute([$id]);
}

// Helper function to get an event by ID
function getEventById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Helper function to handle image upload
function handleImageUpload($file) {
    // Only allow certain file types
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Generate a unique file name
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'uploads/events/' . uniqid('event_', true) . '.' . $ext;

    // Ensure the uploads/events directory exists
    $upload_dir = dirname(__DIR__) . '/uploads/events/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Move the uploaded file
    $destination = dirname(__DIR__) . '/' . $new_filename;
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $new_filename;
    } else {
        return false;
    }
}

// Helper function to get all events
function getAllEvents($pdo) {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY date DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch all events for display
$events = getAllEvents($pdo);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - USIU Admin</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>

<div class="admin-wrapper">
    <aside class="sidebar">
        <h2>USIU Admin</h2>
        <ul>
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="manage-event.php" class="active">🗓️ Manage Events</a></li>
            <li><a href="#">👥 Manage Users</a></li>
            <li><a href="#">💬 Manage Comments</a></li>
            <li><a href="../index.php" target="_blank">🌐 View Site</a></li>
            <li><a href="../logout.php">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>Manage Events</h1>

        <?php if ($message): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="form-container">
            <h2><?php echo $edit_event ? 'Edit Event' : 'Add New Event'; ?></h2>
            <form action="manage-event.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="save">
                <?php if ($edit_event): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_event['id']; ?>">
                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($edit_event['image_path']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Event Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_event['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($edit_event['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="datetime-local" id="date" name="date" value="<?php echo htmlspecialchars($edit_event ? date('Y-m-d\TH:i', strtotime($edit_event['date'])) : ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($edit_event['location'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="image">Event Image <?php if ($edit_event) echo "(leave blank to keep current)"; ?></label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if ($edit_event && !empty($edit_event['image_path'])): ?>
                        <img src="../<?php echo htmlspecialchars($edit_event['image_path']); ?>" alt="Current Image" class="current-image">
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn"><?php echo $edit_event ? 'Update Event' : 'Add Event'; ?></button>
            </form>
        </div>

        <div class="table-container">
            <h2>Existing Events</h2>
            <table class="events-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr><td colspan="5">No events found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <img src="../<?php echo htmlspecialchars($event['image_path'] ?? 'assets/images/placeholder.png'); ?>" alt="<?php echo htmlspecialchars($event['name']); ?>">
                            </td>
                            <td><?php echo htmlspecialchars($event['name']); ?></td>
                            <td><?php echo date('M d, Y, h:i A', strtotime($event['date'])); ?></td>
                            <td><?php echo htmlspecialchars($event['location']); ?></td>
                            <td class="actions">
                                <a href="manage-event.php?edit=<?php echo $event['id']; ?>" class="btn">Edit</a>
                                <form action="manage-event.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>