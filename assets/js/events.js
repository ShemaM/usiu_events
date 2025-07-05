
// Main JavaScript file for global scripts

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
});



// JavaScript for event-specific interactions
document.addEventListener('DOMContentLoaded', () => {
    const registrationForm = document.getElementById('registration-form');
    if (registrationForm) {
        registrationForm.addEventListener('submit', handleRegistration);
    }
});

/**
 * Handles the event registration and comment submission using AJAX.
 * @param {Event} e The form submission event.
 */
function handleRegistration(e) {
    e.preventDefault(); // Prevent the default form submission

    const form = e.target;
    const name = form.querySelector('#name').value;
    const email = form.querySelector('#email').value;
    const commentText = form.querySelector('#comment').value;
    const responseDiv = document.getElementById('form-response');

    // Basic validation
    if (!name || !email) {
        responseDiv.innerHTML = `<p class="text-red-500">Please fill in your name and email.</p>`;
        return;
    }

    responseDiv.innerHTML = `<p class="text-blue-600">Processing...</p>`;

    // --- AJAX Simulation ---
    // In a real application, this would be a fetch() call to a PHP backend.
    // e.g., fetch('/api/register_event.php', { method: 'POST', body: new FormData(form) })
    setTimeout(() => {
        // Simulate a successful response from the server
        console.log('AJAX call successful:', { name, email, commentText });
        
        responseDiv.innerHTML = `<p class="text-green-600 font-semibold">Thank you, ${name}! You are now registered. Your comment has been posted.</p>`;
        form.reset();

        // Dynamically add the new comment to the discussion section
        if (commentText) {
            const commentsSection = document.getElementById('comments-section');
            const newComment = document.createElement('div');
            newComment.className = 'flex space-x-4 animate-fade-in'; // Add a fade-in animation
            newComment.innerHTML = `
                <img src="https://placehold.co/48x48/e0e0e0/333?text=${name.charAt(0)}" alt="User" class="w-12 h-12 rounded-full">
                <div>
                    <p class="font-bold">${name}</p>
                    <p class="text-sm text-gray-500">Just now</p>
                    <p class="mt-1">${commentText}</p>
                </div>
            `;
            commentsSection.prepend(newComment); // Add to the top of the list
        }

    }, 1500); // Simulate 1.5 second network delay
}

// Add a simple fade-in animation for new comments
const style = document.createElement('style');
style.innerHTML = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out;
    }
`;
document.head.appendChild(style);
