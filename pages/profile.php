<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user details
$db->query("SELECT * FROM users WHERE user_id = :user_id");
$db->bind(':user_id', $_SESSION['user_id']);
$user = $db->single();

// Get user's upcoming registrations
$db->query("SELECT e.*, r.registration_date 
            FROM events e 
            JOIN registrations r ON e.event_id = r.event_id 
            WHERE r.user_id = :user_id AND e.event_date >= NOW() 
            ORDER BY e.event_date ASC");
$db->bind(':user_id', $_SESSION['user_id']);
$upcomingEvents = $db->resultSet();

// Get user's past registrations
$db->query("SELECT e.*, r.registration_date 
            FROM events e 
            JOIN registrations r ON e.event_id = r.event_id 
            WHERE r.user_id = :user_id AND e.event_date < NOW() 
            ORDER BY e.event_date DESC");
$db->bind(':user_id', $_SESSION['user_id']);
$pastEvents = $db->resultSet();

// Set page title
$pageTitle = "My Profile";

// Include header
include '../includes/header.php';
?>

<div class="container profile-container">
    <div class="profile-header">
        <div class="profile-image">
            <img src="<?php echo $user->profile_picture ? htmlspecialchars($user->profile_picture) : '../assets/images/default-profile.jpg'; ?>" alt="Profile Picture">
            <button id="change-picture-btn" class="btn btn-secondary">Change Picture</button>
            <input type="file" id="picture-upload" accept="image/*" style="display: none;">
        </div>
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user->full_name); ?></h1>
            <p><i class="fas fa-user"></i> <?php echo ucfirst($user->role); ?></p>
            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user->email); ?></p>
            <?php if ($user->club_affiliation): ?>
                <p><i class="fas fa-users"></i> <?php echo htmlspecialchars($user->club_affiliation); ?></p>
            <?php endif; ?>
            <p><i class="fas fa-calendar-alt"></i> Member since <?php echo date('F Y', strtotime($user->created_at)); ?></p>
            
            <div class="profile-actions">
                <button id="edit-profile-btn" class="btn btn-primary">Edit Profile</button>
                <button id="change-password-btn" class="btn btn-secondary">Change Password</button>
            </div>
        </div>
    </div>

    <div class="profile-content">
        <div class="profile-section">
            <h2>Upcoming Events</h2>
            <?php if (empty($upcomingEvents)): ?>
                <p class="no-events">You have no upcoming events. <a href="../pages/events.php">Browse events</a> to register.</p>
            <?php else: ?>
                <div class="events-grid">
                    <?php foreach ($upcomingEvents as $event): ?>
                        <div class="event-card">
                            <div class="event-image" style="background-image: url('<?php echo $event->image_url ? htmlspecialchars($event->image_url) : '../assets/images/default-event.jpg'; ?>')"></div>
                            <div class="event-details">
                                <h3><?php echo htmlspecialchars($event->title); ?></h3>
                                <p class="event-date"><i class="far fa-calendar-alt"></i> <?php echo date('M j, Y \a\t g:i a', strtotime($event->event_date)); ?></p>
                                <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event->location); ?></p>
                                <div class="event-actions">
                                    <a href="event-detail.php?id=<?php echo $event->event_id; ?>" class="btn btn-sm btn-primary">View</a>
                                    <button class="btn btn-sm btn-danger unregister-btn" data-event-id="<?php echo $event->event_id; ?>">Unregister</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-section">
            <h2>Past Events</h2>
            <?php if (empty($pastEvents)): ?>
                <p class="no-events">You haven't attended any events yet.</p>
            <?php else: ?>
                <div class="events-grid">
                    <?php foreach ($pastEvents as $event): ?>
                        <div class="event-card">
                            <div class="event-image" style="background-image: url('<?php echo $event->image_url ? htmlspecialchars($event->image_url) : '../assets/images/default-event.jpg'; ?>')"></div>
                            <div class="event-details">
                                <h3><?php echo htmlspecialchars($event->title); ?></h3>
                                <p class="event-date"><i class="far fa-calendar-alt"></i> <?php echo date('M j, Y \a\t g:i a', strtotime($event->event_date)); ?></p>
                                <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event->location); ?></p>
                                <div class="event-actions">
                                    <a href="event-detail.php?id=<?php echo $event->event_id; ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div id="edit-profile-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Edit Profile</h2>
        <form id="edit-profile-form">
            <div class="form-group">
                <label for="full-name">Full Name</label>
                <input type="text" id="full-name" name="full_name" value="<?php echo htmlspecialchars($user->full_name); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required>
            </div>
            <div class="form-group">
                <label for="club-affiliation">Club Affiliation (if any)</label>
                <input type="text" id="club-affiliation" name="club_affiliation" value="<?php echo htmlspecialchars($user->club_affiliation); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<!-- Change Password Modal -->
<div id="change-password-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Change Password</h2>
        <form id="change-password-form">
            <div class="form-group">
                <label for="current-password">Current Password</label>
                <input type="password" id="current-password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new-password">New Password</label>
                <input type="password" id="new-password" name="new_password" required minlength="8">
                <small>Minimum 8 characters</small>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm New Password</label>
                <input type="password" id="confirm-password" name="confirm_password" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile picture change
    const changePictureBtn = document.getElementById('change-picture-btn');
    const pictureUpload = document.getElementById('picture-upload');
    
    changePictureBtn.addEventListener('click', function() {
        pictureUpload.click();
    });
    
    pictureUpload.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const formData = new FormData();
            formData.append('profile_picture', this.files[0]);
            
            fetch('../api/users.php?action=update_picture', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update profile picture');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    });
    
    // Modal handling
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const changePasswordBtn = document.getElementById('change-password-btn');
    const editProfileModal = document.getElementById('edit-profile-modal');
    const changePasswordModal = document.getElementById('change-password-modal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    
    editProfileBtn.addEventListener('click', function() {
        editProfileModal.style.display = 'block';
    });
    
    changePasswordBtn.addEventListener('click', function() {
        changePasswordModal.style.display = 'block';
    });
    
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });
    
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });
    
    // Edit profile form
    const editProfileForm = document.getElementById('edit-profile-form');
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/users.php?action=update_profile', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update profile');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    }
    
    // Change password form
    const changePasswordForm = document.getElementById('change-password-form');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match');
                return;
            }
            
            const formData = new FormData(this);
            
            fetch('../api/users.php?action=change_password', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Password changed successfully');
                    changePasswordModal.style.display = 'none';
                    changePasswordForm.reset();
                } else {
                    alert(data.message || 'Failed to change password');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    }
    
    // Unregister buttons
    document.querySelectorAll('.unregister-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            
            if (confirm('Are you sure you want to unregister from this event?')) {
                fetch('../api/registrations.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ event_id: eventId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Unregistration failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        });
    });
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>