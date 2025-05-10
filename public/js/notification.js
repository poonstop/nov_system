/**
 * Tabbed Notification System
 * Handles all notifications for the dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Tabbed notification system initializing...');
    
    // Initialize the notification tab system
    initNotificationSystem();
    
    // Process overdue establishments
    processOverdueEstablishments();
});

/**
 * Initialize the notification system
 */
function initNotificationSystem() {
    // Ensure we have only one notification container
    let container = document.getElementById('notification-tabs-container');
    
    if (!container) {
        console.error('Notification tab container not found in the HTML');
        return;
    }
    
    console.log('Notification tab container initialized');
    
    // Hide the original PHP alert if it exists
    const originalAlert = document.getElementById('overdue-alert');
    if (originalAlert) {
        originalAlert.style.display = 'none';
        console.log('Original PHP alert hidden');
    }
}

/**
 * Process overdue establishments from the global variable
 */
function processOverdueEstablishments() {
    // Verify data is available and valid
    if (!window.overdueEstablishments || !Array.isArray(window.overdueEstablishments)) {
        console.warn('No valid overdue establishments data found');
        return;
    }
    
    const count = window.overdueEstablishments.length;
    console.log(`Processing ${count} overdue establishments`);
    
    if (count === 0) return;
    
    // Create summary notification first
    createNotificationTab({
        title: `Attention Required: ${count} Overdue Notice${count > 1 ? 's' : ''}`,
        message: 'Establishments with pending notices for more than 48 hours (excluding weekends)',
        type: 'warning',
        link: count > 3 ? 'pending_notices.php' : null,
        isPinned: true // Keep it visible
    });
    
    // Then show individual notifications
    window.overdueEstablishments.forEach((establishment, index) => {
        // Only show first 3 individual notifications
        if (index < 3) {
            setTimeout(() => {
                createNotificationTab({
                    title: establishment.name,
                    message: `Pending notice for ${establishment.business_days_elapsed || '2+'} business days (since ${formatDate(new Date(establishment.issued_datetime))})`,
                    type: 'warning',
                    link: `view_establishment.php?id=${establishment.establishment_id}`
                });
            }, (index + 1) * 400); // Stagger notifications
        }
    });
    
    // If there are more than 3, add a "view all" notification
    if (count > 3) {
        setTimeout(() => {
            createNotificationTab({
                title: `${count - 3} More Overdue Notices`,
                message: 'Click to view all pending notices',
                type: 'info',
                link: 'pending_notices.php'
            });
        }, 1800);
    }
}

/**
 * Create and display a notification tab
 * @param {Object} options - Notification options
 */
function createNotificationTab(options) {
    const {
        title,
        message,
        type = 'info',
        link = null,
        isPinned = false
    } = options;
    
    console.log('Creating notification tab:', { title, type });
    
    // Get container
    const container = document.getElementById('notification-tabs-container');
    if (!container) {
        console.error('Notification tab container not found');
        return null;
    }
    
    // Generate unique ID
    const notificationId = 'notification-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
    
    // Determine color and icon based on type
    let bgColor, textColor, icon;
    switch (type) {
        case 'success':
            bgColor = 'bg-success';
            textColor = 'text-white';
            icon = 'fa-check-circle';
            break;
        case 'warning':
            bgColor = 'bg-warning';
            textColor = 'text-dark';
            icon = 'fa-exclamation-triangle';
            break;
        case 'danger':
            bgColor = 'bg-danger';
            textColor = 'text-white';
            icon = 'fa-exclamation-circle';
            break;
        case 'info':
        default:
            bgColor = 'bg-info';
            textColor = 'text-dark';
            icon = 'fa-info-circle';
    }
    
    // Create notification tab element
    const notification = document.createElement('div');
    notification.id = notificationId;
    notification.className = `card mb-3 shadow-sm ${isPinned ? 'border-start border-3 ' + bgColor : ''}`;
    notification.setAttribute('role', 'alert');
    
    // Create header
    const header = document.createElement('div');
    header.className = `card-header ${bgColor} ${textColor} py-2 d-flex justify-content-between align-items-center`;
    
    // Title with icon
    const titleElement = document.createElement('div');
    titleElement.className = 'me-auto d-flex align-items-center';
    titleElement.innerHTML = `<i class="fas ${icon} me-2"></i><strong>${title}</strong>`;
    
    // Close button
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close btn-close-white';
    closeButton.setAttribute('aria-label', 'Close');
    closeButton.addEventListener('click', function() {
        document.getElementById(notificationId).remove();
    });
    
    // Assemble header
    header.appendChild(titleElement);
    header.appendChild(closeButton);
    
    // Create body
    const body = document.createElement('div');
    body.className = 'card-body py-2';
    body.innerHTML = message;
    
    // Add link button if provided
    if (link) {
        const linkContainer = document.createElement('div');
        linkContainer.className = 'd-grid gap-2 mt-2';
        
        const linkButton = document.createElement('a');
        linkButton.href = link;
        linkButton.className = `btn btn-sm btn-${type === 'info' ? 'info' : type}`;
        linkButton.innerHTML = '<i class="fas fa-external-link-alt me-1"></i>View Details';
        
        linkContainer.appendChild(linkButton);
        body.appendChild(linkContainer);
    }
    
    // Assemble notification
    notification.appendChild(header);
    notification.appendChild(body);
    
    // Add to container
    container.appendChild(notification);
    
    console.log('Notification tab displayed:', notificationId);
    
    return notification;
}

/**
 * Format a date for display
 * @param {Date} date - Date object
 * @returns {string} - Formatted date string
 */
function formatDate(date) {
    if (!date || isNaN(date.getTime())) {
        return 'Unknown date';
    }
    
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    };
    
    return date.toLocaleDateString('en-US', options);
}

/**
 * Test function to trigger sample notifications
 */
function testNotification() {
    console.log('Running notification test');
    
    // Create a sequence of test notifications
    createNotificationTab({
        title: 'Information',
        message: 'This is an info notification example',
        type: 'info'
    });
    
    setTimeout(() => {
        createNotificationTab({
            title: 'Warning',
            message: 'This is a warning notification example',
            type: 'warning'
        });
    }, 500);
    
    setTimeout(() => {
        createNotificationTab({
            title: 'Success',
            message: 'This is a success notification example',
            type: 'success'
        });
    }, 1000);
    
    setTimeout(() => {
        createNotificationTab({
            title: 'Danger',
            message: 'This is a danger notification example',
            type: 'danger',
            link: '#'
        });
    }, 1500);
    
    setTimeout(() => {
        createNotificationTab({
            title: 'Pinned Notification',
            message: 'This notification is pinned with a highlight border',
            type: 'warning',
            isPinned: true
        });
    }, 2000);
}

// Public API for other scripts
window.notificationSystem = {
    show: createNotificationTab,
    test: testNotification
};