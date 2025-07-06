<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Check if event ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: events.php");
    exit();
}

$eventId = $_GET['id'];

// Fetch event details
$db->query("SELECT e.*, u.full_name as organizer_name 
            FROM events e 
            JOIN users u ON e.organizer_id = u.user_id 
            WHERE e.event_id = :event_id");
$db->bind(':event_id', $eventId);
$event = $db->single();

if (!$event) {
    header("Location: events.php");
    exit();
}

// Check if user is registered
$isRegistered = false;
if (isset($_SESSION['user_id'])) {
    $db->query("SELECT * FROM registrations 
                WHERE event_id = :event_id AND user_id = :user_id");
    $db->bind(':event_id', $eventId);
    $db->bind(':user_id', $_SESSION['user_id']);
    $isRegistered = $db->single() ? true : false;
}

// Fetch comments
$db->query("SELECT c.*, u.username, u.profile_picture 
            FROM comments c 
            JOIN users u ON c.user_id = u.user_id 
            WHERE c.event_id = :event_id 
            ORDER BY c.created_at DESC");
$db->bind(':event_id', $eventId);
$comments = $db->resultSet();

// Fetch registration count
$db->query("SELECT COUNT(*) as count FROM registrations WHERE event_id = :event_id");
$db->bind(':event_id', $eventId);
$registrationCount = $db->single()->count;

// Set page title
$pageTitle = $event->title;

// Include header
include '../includes/header.php';
?>

<div class="container event-detail-container">
    <div class="event-header">
        <div class="event-image">
            <img src="<?php echo $event->image_url ? htmlspecialchars($event->image_url) : '../assets/images/default-event.jpg'; ?>" alt="<?php echo htmlspecialchars($event->title); ?>">
        </div>
        <div class="event-info">
            <h1><?php echo htmlspecialchars($event->title); ?></h1>
            <div class="event-meta">
                <p><i class="far fa-calendar-alt"></i> <?php echo date('F j, Y \a\t g:i a', strtotime($event->event_date)); ?></p>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event->location); ?></p>
                <p><i class="fas fa-user-tie"></i> Organized by <?php echo htmlspecialchars($event->organizer_name); ?></p>
                <p><i class="fas fa-users"></i> <?php echo $registrationCount; ?> / <?php echo $event->capacity; ?> registered</p>
            </div>
            
            <div class="event-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($isRegistered): ?>
                        <button id="unregister-btn" class="btn btn-danger" data-event-id="<?php echo $eventId; ?>">
                            <i class="fas fa-user-minus"></i> Unregister
                        </button>
                    <?php elseif ($registrationCount < $event->capacity): ?>
                        <button id="register-btn" class="btn btn-primary" data-event-id="<?php echo $eventId; ?>">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    <?php else: ?>
                        <button class="btn btn-disabled" disabled>Event Full</button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="../login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login to Register
                    </a>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $event->organizer_id): ?>
                    <a href="../admin/manage-event.php?id=<?php echo $eventId; ?>" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Manage Event
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="event-content">
        <div class="event-description">
            <h2>About This Event</h2>
            <p><?php echo nl2br(htmlspecialchars($event->description)); ?></p>
        </div>

        <div class="event-comments">
            <h2>Comments <span id="comments-count">(<?php echo count($comments); ?>)</span></h2>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="comment-form">
                    <form id="comment-form" method="POST">
                        <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                        <textarea name="comment" id="comment-text" placeholder="Share your thoughts about this event..." required></textarea>
                        <button type="submit" class="btn btn-primary">Post Comment</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="login-prompt">
                    <a href="../login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Login</a> to leave a comment.
                </div>
            <?php endif; ?>

            <div id="comments-list" class="comments-list">
                <?php if (empty($comments)): ?>
                    <p class="no-comments">No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment" id="comment-<?php echo $comment->comment_id; ?>">
                            <div class="comment-author">
                                <img src="<?php echo $comment->profile_picture ? htmlspecialchars($comment->profile_picture) : '../assets/images/default-profile.jpg'; ?>" alt="<?php echo htmlspecialchars($comment->username); ?>">
                                <span><?php echo htmlspecialchars($comment->username); ?></span>
                            </div>
                            <div class="comment-content">
                                <p><?php echo nl2br(htmlspecialchars($comment->content)); ?></p>
                                <div class="comment-meta">
                                    <span><?php echo date('M j, Y \a\t g:i a', strtotime($comment->created_at)); ?></span>
                                    <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $comment->user_id || $_SESSION['role'] == 'admin')): ?>
                                        <button class="delete-comment-btn" data-comment-id="<?php echo $comment->comment_id; ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Registration functionality
    const registerBtn = document.getElementById('register-btn');
    const unregisterBtn = document.getElementById('unregister-btn');
    
    if (registerBtn) {
        registerBtn.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            registerForEvent(eventId);
        });
    }
    
    if (unregisterBtn) {
        unregisterBtn.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            unregisterFromEvent(eventId);
        });
    }
    
    // Comment functionality
    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            postComment();
        });
    }
    
    // Delete comment buttons
    document.querySelectorAll('.delete-comment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            deleteComment(commentId);
        });
    });
    
    function registerForEvent(eventId) {
        fetch('../api/registrations.php', {
            method: 'POST',
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
                alert(data.message || 'Registration failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
    
    function unregisterFromEvent(eventId) {
        if (!confirm('Are you sure you want to unregister from this event?')) return;
        
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
    
    function postComment() {
        const form = document.getElementById('comment-form');
        const formData = new FormData(form);
        
        fetch('../api/comments.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to post comment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
    
    function deleteComment(commentId) {
        if (!confirm('Are you sure you want to delete this comment?')) return;
        
        fetch('../api/comments.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ comment_id: commentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`comment-${commentId}`).remove();
                updateCommentsCount();
            } else {
                alert(data.message || 'Failed to delete comment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
    
    function updateCommentsCount() {
        const commentsCount = document.querySelectorAll('.comment').length;
        document.getElementById('comments-count').textContent = `(${commentsCount})`;
        
        if (commentsCount === 0) {
            const commentsList = document.getElementById('comments-list');
            commentsList.innerHTML = '<p class="no-comments">No comments yet. Be the first to comment!</p>';
        }
    }
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>