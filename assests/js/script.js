function showSection(sectionId) {
    // Hide all sections
    const sections = document.querySelectorAll('.section-container');
    sections.forEach(section => {
        section.classList.remove('active');
    });

    // Show the selected section
    const activeSection = document.getElementById(sectionId);
    activeSection.classList.add('active');
}

function openBooking(packageName) {
    // Set the selected package name in the booking form
    document.getElementById('package-name').value = packageName;
    // Show the booking form
    showSection('booking-form');
}

// Handle the form submission
document.getElementById('appointment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Appointment Booked!');
    showSection('home'); // Go back to home after booking
});
