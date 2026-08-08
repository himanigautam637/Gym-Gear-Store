function openModal(id) {
    document.getElementById(id).classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

// Close modal when clicking outside the box
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
    }
});

function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this? This cannot be undone.');
}

// Auto-dismiss alert banners after 4 seconds
document.addEventListener('DOMContentLoaded', function () {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (a) {
        setTimeout(function () {
            a.style.transition = 'opacity 0.4s ease';
            a.style.opacity = '0';
            setTimeout(function () { a.remove(); }, 400);
        }, 4000);
    });
});