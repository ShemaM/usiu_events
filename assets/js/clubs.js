// JavaScript for club-specific interactions
// e.g., AJAX filtering, search functionality
// Example: Simple search/filter for club list
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('club-search');
    const clubList = document.getElementById('club-list');
    if (!searchInput || !clubList) return;

    searchInput.addEventListener('input', function () {
        const filter = searchInput.value.toLowerCase();
        const clubs = clubList.querySelectorAll('.club-item');
        clubs.forEach(function (club) {
            const name = club.textContent.toLowerCase();
            club.style.display = name.includes(filter) ? '' : 'none';
        });
    });
});