// Tạo container 
function initNotificationContainer() {
    if (!document.getElementById('notification-container')) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
}

// Hiển thị thông báo
function showNotification(type, title, message, duration = 5000) {
    initNotificationContainer();
    
    const container = document.getElementById('notification-container');
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    // Icon theo loại
    let icon = 'fa-circle-check';
    if (type === 'error') icon = 'fa-circle-exclamation';
    else if (type === 'warning') icon = 'fa-triangle-exclamation';
    else if (type === 'info') icon = 'fa-circle-info';
    
    notification.innerHTML = `
        <i class="fa-solid ${icon} notification-icon"></i>
        <div class="notification-content">
            <div class="notification-title">${title}</div>
            ${message ? `<div class="notification-message">${message}</div>` : ''}
        </div>
        <button class="notification-close" onclick="closeNotification(this)">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;
    
    container.appendChild(notification);
    
    // Tự động đóng sau duration
    if (duration > 0) {
        setTimeout(() => {
            closeNotification(notification.querySelector('.notification-close'));
        }, duration);
    }
    
    return notification;
}

// Đóng thông báo
function closeNotification(button) {
    const notification = button.closest('.notification');
    if (notification) {
        notification.classList.add('fade-out');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}

// Helper functions
function showSuccess(title, message, duration) {
    return showNotification('success', title, message, duration);
}

function showError(title, message, duration) {
    return showNotification('error', title, message, duration);
}

function showInfo(title, message, duration) {
    return showNotification('info', title, message, duration);
}

function showWarning(title, message, duration) {
    return showNotification('warning', title, message, duration);
}

// Kiểm tra thông báo từ session/URL params
document.addEventListener('DOMContentLoaded', function() {
    // Kiểm tra URL params
    const urlParams = new URLSearchParams(window.location.search);
    const notificationType = urlParams.get('notification');
    const notificationTitle = urlParams.get('title');
    const notificationMessage = urlParams.get('message');
    
    if (notificationType && notificationTitle) {
        showNotification(notificationType, decodeURIComponent(notificationTitle), 
                       notificationMessage ? decodeURIComponent(notificationMessage) : '', 5000);
        // Xóa params khỏi URL
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});






















