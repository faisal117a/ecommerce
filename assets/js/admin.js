// Admin Dashboard JavaScript

// Fix stuck modal backdrops
document.addEventListener('DOMContentLoaded', function() {
    // Remove any stuck Bootstrap modal backdrops
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => backdrop.remove());
    
    // Remove modal-open class from body if present
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
    
    // Mobile sidebar toggle
    const sidebar = document.querySelector('.admin-sidebar');
    if (window.innerWidth <= 768 && sidebar) {
        // Mobile menu implementation can be added here
    }
});

// Confirm delete actions
document.addEventListener('click', function(e) {
    if (e.target.matches('.btn-outline-danger, .btn-outline-danger *')) {
        const btn = e.target.closest('.btn-outline-danger');
        if (btn && !btn.hasAttribute('onclick')) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        }
    }
});

