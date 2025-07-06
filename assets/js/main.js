document.addEventListener('DOMContentLoaded', () => {
    const eventsContainer = document.getElementById('events-container');

    // Function to fetch and display events (AJAX GET Request)
    const loadEvents = async () => {
        try {
            const response = await fetch('api/get_events.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const events = await response.json();

            // Clear the loading message
            eventsContainer.innerHTML = ''; 

            if (events.length === 0) {
                eventsContainer.innerHTML = '<p>No upcoming events found.</p>';
                return;
            }

            // Create and append event cards
            events.forEach(event => {
                const eventCard = document.createElement('div');
                eventCard.className = 'event-card';
                eventCard.innerHTML = `
                    <h3>${event.title}</h3>
                    <p>${new Date(event.event_date).toLocaleString()}</p>
                    <p>${event.location}</p>
                    <a href="event.html?id=${event.id}">View Details</a>
                `;
                eventsContainer.appendChild(eventCard);
            });

        } catch (error) {
            eventsContainer.innerHTML = `<p style="color: red;">Failed to load events. ${error}</p>`;
        }
    };

    // Initial load of events
    loadEvents();
});

// You would add functions for posting comments and registering for events here
// Example: postComment(eventId, userId, commentText) using a POST fetch request