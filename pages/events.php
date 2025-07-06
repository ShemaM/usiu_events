<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Set page title
$pageTitle = "All Events";

// Include header
include '../includes/header.php';
?>

<div class="container">
    <div class="events-header">
        <h1>Upcoming Campus Events</h1>
        <div class="events-controls">
            <div class="search-control">
                <input type="text" id="events-search" placeholder="Search events...">
                <button id="search-btn"><i class="fas fa-search"></i></button>
            </div>
            <div class="sort-control">
                <label for="sort-by">Sort by:</label>
                <select id="sort-by">
                    <option value="date-asc">Date (Oldest First)</option>
                    <option value="date-desc" selected>Date (Newest First)</option>
                    <option value="title-asc">Title (A-Z)</option>
                    <option value="title-desc">Title (Z-A)</option>
                </select>
            </div>
        </div>
    </div>

    <div class="events-container">
        <div id="loading-spinner" class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading events...</p>
        </div>
        <div id="events-list" class="events-grid"></div>
        <div id="no-events" class="no-events" style="display: none;">
            <i class="fas fa-calendar-times"></i>
            <p>No events found matching your criteria.</p>
        </div>
        <div id="pagination" class="pagination" style="display: none;">
            <button id="prev-page" class="btn"><i class="fas fa-chevron-left"></i> Previous</button>
            <span id="page-info">Page 1 of 1</span>
            <button id="next-page" class="btn">Next <i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let totalPages = 1;
    let currentSearch = '';
    let currentSort = 'date-desc';

    // Load initial events
    loadEvents();

    // Search functionality
    document.getElementById('search-btn').addEventListener('click', function() {
        currentSearch = document.getElementById('events-search').value;
        currentPage = 1;
        loadEvents();
    });

    document.getElementById('events-search').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            currentSearch = this.value;
            currentPage = 1;
            loadEvents();
        }
    });

    // Sort functionality
    document.getElementById('sort-by').addEventListener('change', function() {
        currentSort = this.value;
        currentPage = 1;
        loadEvents();
    });

    // Pagination
    document.getElementById('prev-page').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadEvents();
        }
    });

    document.getElementById('next-page').addEventListener('click', function() {
        if (currentPage < totalPages) {
            currentPage++;
            loadEvents();
        }
    });

    function loadEvents() {
        const loadingSpinner = document.getElementById('loading-spinner');
        const eventsList = document.getElementById('events-list');
        const noEvents = document.getElementById('no-events');
        const pagination = document.getElementById('pagination');

        loadingSpinner.style.display = 'flex';
        eventsList.style.display = 'none';
        noEvents.style.display = 'none';
        pagination.style.display = 'none';

        fetch(`../api/events.php?page=${currentPage}&search=${encodeURIComponent(currentSearch)}&sort=${currentSort}`)
            .then(response => response.json())
            .then(data => {
                loadingSpinner.style.display = 'none';

                if (data.success && data.events.length > 0) {
                    eventsList.style.display = 'grid';
                    renderEvents(data.events);

                    // Update pagination
                    totalPages = data.total_pages;
                    document.getElementById('page-info').textContent = `Page ${currentPage} of ${totalPages}`;
                    
                    if (totalPages > 1) {
                        pagination.style.display = 'flex';
                        document.getElementById('prev-page').disabled = currentPage <= 1;
                        document.getElementById('next-page').disabled = currentPage >= totalPages;
                    }
                } else {
                    noEvents.style.display = 'flex';
                }
            })
            .catch(error => {
                loadingSpinner.style.display = 'none';
                console.error('Error loading events:', error);
                noEvents.style.display = 'flex';
                noEvents.innerHTML = `<i class="fas fa-exclamation-triangle"></i><p>Error loading events. Please try again.</p>`;
            });
    }

    function renderEvents(events) {
        const eventsList = document.getElementById('events-list');
        eventsList.innerHTML = '';

        events.forEach(event => {
            const eventCard = document.createElement('div');
            eventCard.className = 'event-card';
            eventCard.innerHTML = `
                <div class="event-image" style="background-image: url('${event.image_url || '../assets/images/default-event.jpg'}')">
                    ${event.registration_count >= event.capacity ? '<span class="event-full">FULL</span>' : ''}
                </div>
                <div class="event-details">
                    <h3>${event.title}</h3>
                    <p class="event-meta">
                        <span class="event-date"><i class="far fa-calendar-alt"></i> ${new Date(event.event_date).toLocaleDateString()}</span>
                        <span class="event-location"><i class="fas fa-map-marker-alt"></i> ${event.location}</span>
                    </p>
                    <p class="event-organizer"><i class="fas fa-user-tie"></i> ${event.organizer_name}</p>
                    <p class="event-description">${event.description.substring(0, 100)}...</p>
                    <div class="event-stats">
                        <span class="event-registrations"><i class="fas fa-users"></i> ${event.registration_count}/${event.capacity}</span>
                        <a href="event-detail.php?id=${event.event_id}" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            `;
            eventsList.appendChild(eventCard);
        });
    }
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>