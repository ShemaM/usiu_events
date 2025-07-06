<?php
// Start session and include configuration
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USIU Campus Events</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">USIU Events</a>
            <div class="nav-links">
                <a href="index.php" class="active">Home</a>
                <a href="pages/events.php">Events</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="pages/profile.php">Profile</a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="admin/dashboard.php">Dashboard</a>
                    <?php endif; ?>
                    <a href="includes/logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
            <div class="mobile-menu">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Discover Campus Events</h1>
            <p>Find and register for upcoming events at USIU Kenya</p>
            <div class="search-bar">
                <input type="text" placeholder="Search events...">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>
    </section>

    <!-- Featured Events Section -->
    <section class="featured-events">
        <div class="container">
            <h2>Upcoming Events</h2>
            <div class="events-filter">
                <button class="filter-btn active" data-filter="all">All Events</button>
                <button class="filter-btn" data-filter="drama">Drama Club</button>
                <button class="filter-btn" data-filter="hotel">Hotel & Tourism</button>
                <button class="filter-btn" data-filter="sports">Sports</button>
            </div>
            <div id="events-container" class="events-grid">
                <!-- Events will be loaded here via AJAX -->
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading events...</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats">
        <div class="container">
            <div class="stat-card">
                <h3 id="total-events">0</h3>
                <p>Total Events</p>
            </div>
            <div class="stat-card">
                <h3 id="total-users">0</h3>
                <p>Registered Users</p>
            </div>
            <div class="stat-card">
                <h3 id="active-clubs">0</h3>
                <p>Active Clubs</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-section">
                <h3>USIU Events</h3>
                <p>Connecting students with campus activities</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="pages/events.php">All Events</a>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>events@usiu.ac.ke</p>
                <p>+254 700 000000</p>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 USIU Campus Events. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript Files -->
    <script src="assets/js/main.js"></script>
    <script>
        // Load events when page loads
        document.addEventListener('DOMContentLoaded', function() {
            fetchEvents();
            fetchStatistics();
            
            // Setup event listeners for filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    fetchEvents(this.dataset.filter);
                });
            });
        });

        // Function to fetch events via AJAX
        function fetchEvents(filter = 'all') {
            const container = document.getElementById('events-container');
            container.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i><p>Loading events...</p></div>';
            
            fetch(`api/events.php?filter=${filter}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderEvents(data.events);
                    } else {
                        container.innerHTML = `<div class="error">${data.message}</div>`;
                    }
                })
                .catch(error => {
                    container.innerHTML = `<div class="error">Failed to load events. Please try again.</div>`;
                    console.error('Error:', error);
                });
        }

        // Function to render events
        function renderEvents(events) {
            const container = document.getElementById('events-container');
            
            if (events.length === 0) {
                container.innerHTML = '<div class="no-events">No upcoming events found.</div>';
                return;
            }
            
            container.innerHTML = '';
            events.forEach(event => {
                const eventCard = document.createElement('div');
                eventCard.className = 'event-card';
                eventCard.innerHTML = `
                    <div class="event-image" style="background-image: url('${event.image_url || 'assets/images/default-event.jpg'}')"></div>
                    <div class="event-details">
                        <h3>${event.title}</h3>
                        <p class="event-date"><i class="far fa-calendar-alt"></i> ${new Date(event.event_date).toLocaleString()}</p>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> ${event.location}</p>
                        <p class="event-description">${event.description.substring(0, 100)}...</p>
                        <a href="pages/event-detail.php?id=${event.event_id}" class="btn">View Details</a>
                    </div>
                `;
                container.appendChild(eventCard);
            });
        }

        // Function to fetch statistics
        function fetchStatistics() {
            fetch('api/stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('total-events').textContent = data.total_events;
                        document.getElementById('total-users').textContent = data.total_users;
                        document.getElementById('active-clubs').textContent = data.active_clubs;
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>