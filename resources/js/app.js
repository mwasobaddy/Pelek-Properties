import './bootstrap';
import Swal from 'sweetalert2';

window.Swal = Swal;

// Centralized SweetAlert2 Configuration
const SweetAlertConfig = {
    // Toast configuration
    toast: {
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        zIndex: 100000, // Extremely high z-index to appear above modals
        customClass: {
            popup: 'custom-swal-toast',
            title: 'custom-swal-title',
            timerProgressBar: 'custom-swal-progress'
        },
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    },

    // Modal configuration
    modal: {
        zIndex: 100000,
        customClass: {
            popup: 'custom-swal-modal',
            title: 'custom-swal-modal-title',
            confirmButton: 'custom-swal-confirm-btn',
            cancelButton: 'custom-swal-cancel-btn'
        }
    }
};

// Global toast instance
window.Toast = Swal.mixin(SweetAlertConfig.toast);

// Helper functions for consistent notifications
window.showSuccess = (message, timer = 3000) => {
    return window.Toast.fire({
        icon: 'success',
        title: message,
        timer: timer,
        background: '#10B981',
        color: '#ffffff',
        iconColor: '#ffffff'
    });
};

window.showError = (message, timer = 5000) => {
    return window.Toast.fire({
        icon: 'error',
        title: message,
        timer: timer,
        background: '#EF4444',
        color: '#ffffff',
        iconColor: '#ffffff'
    });
};

window.showWarning = (message, timer = 4000) => {
    return window.Toast.fire({
        icon: 'warning',
        title: message,
        timer: timer,
        background: '#F59E0B',
        color: '#ffffff',
        iconColor: '#ffffff'
    });
};

window.showInfo = (message, timer = 3000) => {
    return window.Toast.fire({
        icon: 'info',
        title: message,
        timer: timer,
        background: '#3B82F6',
        color: '#ffffff',
        iconColor: '#ffffff'
    });
};