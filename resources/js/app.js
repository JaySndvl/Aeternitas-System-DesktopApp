import axios from 'axios';
import Alpine from 'alpinejs';

// Set up Axios defaults
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Alpine = Alpine;

// Get CSRF token from meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Add notification manager to Alpine store
Alpine.data('notificationManager', () => ({
    open: false,
    notifications: [],
    unreadCount: 0,
    loading: false,
    
    init() {
        this.loadNotifications();
        setInterval(() => {
            if (!this.open) {
                this.loadNotifications();
            }
        }, 30000);
    },
    
    // ... rest of the functions as above
}));

// Password toggle functionality
window.togglePassword = function(fieldName) {
    const passwordInput = document.getElementById(fieldName);
    const toggleIcon = document.getElementById('toggleIcon-' + fieldName);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
};

// Add interactive effects to inputs
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('scale-105');
        });
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('scale-105');
        });
    });
});

// Form submission with AJAX (optional)
window.submitFormAjax = function(formElement, successCallback, errorCallback) {
    const formData = new FormData(formElement);
    
    axios.post(formElement.action, formData)
        .then(response => {
            if (successCallback) {
                successCallback(response);
            }
        })
        .catch(error => {
            if (errorCallback) {
                errorCallback(error);
            }
        });
};

// Start Alpine
Alpine.start();