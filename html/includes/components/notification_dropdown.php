<?php
/**
 * Notification Dropdown Component
 * Bell icon with dropdown showing recent notifications
 * Include this in your main navigation header
 */

if ($isValidUser) {
    $userID = $userDetails->ID;

    // Safely get unread count with error handling
    try {
        $unreadCount = Notification::getUnreadCount($userID, $DBConn);
    } catch (Exception $e) {
        // If notification system not installed or error, default to 0
        $unreadCount = 0;
    }
?>

<style>
/* Notification bell wrapper - ensure proper positioning */
.notifications-dropdown {
    position: relative;
}

/* Notification dropdown menu */
.notification-dropdown-menu {
    width: 380px;
    max-width: 90vw;
    max-height: 600px;
    overflow: hidden;
    flex-direction: column;
    position: absolute !important;
    top: 100% !important;
    right: 0 !important;
    left: auto !important;
    margin-top: 8px !important;
    z-index: 9999 !important;
}

.notification-dropdown-menu.show {
    display: flex !important;
}

/* Badge positioning - align with header icons */
.notifications-dropdown .header-link {
    position: relative;
}

.notifications-dropdown .header-icon-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    min-width: 18px;
    height: 18px;
    padding: 2px 5px;
    font-size: 10px;
    font-weight: 600;
    line-height: 14px;
    text-align: center;
}

.notification-dropdown-header {
    padding: 16px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-dropdown-header h6 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #fff;
}

.notification-dropdown-header .mark-all-read {
    font-size: 12px;
    color: #fff;
    cursor: pointer;
    text-decoration: none;
    padding: 2px 8px;
    border-radius: 12px;
    background: rgba(255,255,255,0.2);
}

.notification-dropdown-header .mark-all-read:hover {
    background: rgba(255,255,255,0.3);
}

.notification-dropdown-body {
    flex: 1;
    overflow-y: auto;
    max-height: 400px;
}

.notification-dropdown-item {
    padding: 12px 20px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    gap: 12px;
}

.notification-dropdown-item:hover {
    background: #f9fafb;
}

.notification-dropdown-item.unread {
    background: #eff6ff;
}

.notification-dropdown-item .notif-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 20px;
    flex-shrink: 0;
}

.notification-dropdown-item .notif-icon.priority-low {
    background: #f3f4f6;
    color: #6b7280;
}

.notification-dropdown-item .notif-icon.priority-medium {
    background: #dbeafe;
    color: #3b82f6;
}

.notification-dropdown-item .notif-icon.priority-high {
    background: #fed7aa;
    color: #f97316;
}

.notification-dropdown-item .notif-icon.priority-critical {
    background: #fee2e2;
    color: #ef4444;
}

.notification-dropdown-item .notif-content {
    flex: 1;
    min-width: 0;
}

.notification-dropdown-item .notif-title {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.notification-dropdown-item .notif-body {
    font-size: 12px;
    color: #6b7280;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.notification-dropdown-item .notif-time {
    font-size: 11px;
    color: #9ca3af;
    margin-top: 4px;
}

.notification-dropdown-footer {
    padding: 12px 20px;
    border-top: 1px solid #e5e7eb;
    text-align: center;
    background: #f9fafb;
}

.notification-dropdown-footer a {
    color: #667eea;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
}

.notification-dropdown-footer a:hover {
    color: #5568d3;
    text-decoration: underline;
}

.notification-dropdown-empty {
    padding: 40px 20px;
    text-align: center;
    color: #9ca3af;
}

.notification-dropdown-empty i {
    font-size: 48px;
    margin-bottom: 8px;
    display: block;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .notification-dropdown-menu {
        width: 320px;
        right: -20px !important;
    }
}

@media (max-width: 480px) {
    .notification-dropdown-menu {
        width: 280px;
        max-height: 500px;
    }
}

/* Animation for dropdown */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-dropdown-menu.show {
    animation: slideDown 0.2s ease-out;
}

/* Ensure dropdown stays within viewport */
.notification-dropdown-menu {
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 12px;
}
</style>

<!-- Start::header-link|dropdown-toggle -->
<a href="javascript:void(0);" class="header-link" id="notificationBell">
    <i class="ri-notification-3-line header-link-icon animate-bell"></i>
    <span class="badge bg-success rounded-pill header-icon-badge pulse pulse-success" id="notificationBadge" style="<?php echo $unreadCount == 0 ? 'display: none;' : ''; ?>">
        <?php echo $unreadCount; ?>
    </span>
</a>
<!-- End::header-link|dropdown-toggle -->

<!-- Start::main-header-dropdown -->
<div class="main-header-dropdown dropdown-menu dropdown-menu-end notification-dropdown-menu" id="notificationDropdownMenu" data-popper-placement="none">
        <div class="notification-dropdown-header">
            <h6>Notifications</h6>
            <a href="#" class="mark-all-read" id="markAllReadLink">Mark all as read</a>
        </div>
        <div class="dropdown-divider"></div>
        <div class="notification-dropdown-body" id="notificationDropdownBody">
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
        <div class="notification-dropdown-footer">
            <a href="<?php echo $base; ?>html/?s=user&p=notifications">View all notifications</a>
        </div>
</div>
<!-- End::main-header-dropdown -->

<script>
(function() {
    const bell = document.getElementById('notificationBell');
    const menu = document.getElementById('notificationDropdownMenu');
    const body = document.getElementById('notificationDropdownBody');
    const badge = document.getElementById('notificationBadge');
    const markAllLink = document.getElementById('markAllReadLink');

    let isOpen = false;
    let notificationsLoaded = false;

    // Toggle dropdown
    bell.addEventListener('click', function(e) {
        e.stopPropagation();

        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (isOpen && !menu.contains(e.target)) {
            closeDropdown();
        }
    });

    // Mark all as read
    markAllLink.addEventListener('click', function(e) {
        e.preventDefault();
        markAllAsRead();
    });

    function openDropdown() {
        menu.classList.add('show');
        isOpen = true;

        // Ensure dropdown stays within viewport
        adjustDropdownPosition();

        if (!notificationsLoaded) {
            loadNotifications();
        }
    }

    function adjustDropdownPosition() {
        // Get dropdown dimensions and position
        const rect = menu.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        // Check if dropdown goes off-screen to the right
        if (rect.right > viewportWidth) {
            menu.style.right = '0';
            menu.style.left = 'auto';
        }

        // Check if dropdown goes off-screen at the bottom
        if (rect.bottom > viewportHeight) {
            const maxHeight = viewportHeight - rect.top - 20;
            menu.style.maxHeight = maxHeight + 'px';
        }
    }

    function closeDropdown() {
        menu.classList.remove('show');
        isOpen = false;
    }

    function loadNotifications() {
        body.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

        fetch('<?php echo $base; ?>php/scripts/notifications/get_user_notifications.php?limit=10')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderNotifications(data.notifications);
                    updateBadge(data.unreadCount);
                    notificationsLoaded = true;
                } else {
                    showError('Failed to load notifications');
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                showError('Failed to load notifications');
            });
    }

    function renderNotifications(notifications) {
        if (notifications.length === 0) {
            body.innerHTML = `
                <div class="notification-dropdown-empty">
                    <i class="ri-notification-off-line"></i>
                    <div>No notifications</div>
                </div>
            `;
            return;
        }

        body.innerHTML = '';

        notifications.forEach(notif => {
            const item = document.createElement('div');
            item.className = `notification-dropdown-item ${notif.status}`;
            item.dataset.notificationId = notif.notificationID;

            const icon = notif.icon || 'ri-notification-line';
            const priorityClass = `priority-${notif.priority}`;

            // Strip HTML tags from body for dropdown
            const bodyText = notif.body.replace(/<[^>]*>/g, '');

            item.innerHTML = `
                <div class="notif-icon ${priorityClass}">
                    <i class="${icon}"></i>
                </div>
                <div class="notif-content">
                    <div class="notif-title">${notif.title}</div>
                    <div class="notif-body">${bodyText}</div>
                    <div class="notif-time">${notif.timeAgo}</div>
                </div>
            `;

            item.addEventListener('click', function() {
                handleNotificationClick(notif);
            });

            body.appendChild(item);
        });
    }

    function handleNotificationClick(notif) {
        // Mark as read if unread
        if (notif.status === 'unread') {
            markAsRead(notif.notificationID);
        }

        // Close dropdown
        closeDropdown();

        // Navigate to link if available
        const target = notif.link
            ? '<?php echo $base; ?>html/' + notif.link
            : '<?php echo $base; ?>html/?s=user&ss=notifications&p=user_notifications&notificationID=' + encodeURIComponent(notif.notificationID);
        window.location.href = target;
    }

    function markAsRead(notificationID) {
        const formData = new FormData();
        formData.append('notificationID', notificationID);

        fetch('<?php echo $base; ?>php/scripts/notifications/mark_as_read.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const item = body.querySelector(`[data-notification-id="${notificationID}"]`);
                if (item) {
                    item.classList.remove('unread');
                    item.classList.add('read');
                }

                updateBadge(data.unreadCount);
            }
        })
        .catch(error => {
            console.error('Error marking as read:', error);
        });
    }

    function markAllAsRead() {
        fetch('<?php echo $base; ?>php/scripts/notifications/mark_all_read.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI - remove unread class from all items
                body.querySelectorAll('.notification-dropdown-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    item.classList.add('read');
                });

                updateBadge(0);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function updateBadge(count) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
        }
    }

    function showError(message) {
        body.innerHTML = `
            <div class="notification-dropdown-empty">
                <i class="ri-error-warning-line"></i>
                <div>${message}</div>
            </div>
        `;
    }

    // Auto-refresh every 2 minutes
    setInterval(function() {
        if (isOpen) {
            loadNotifications();
        } else {
            // Just update the badge count
            fetch('<?php echo $base; ?>php/scripts/notifications/get_user_notifications.php?limit=1')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateBadge(data.unreadCount);
                        // Reset loaded flag if there are new notifications
                        if (data.unreadCount > 0) {
                            notificationsLoaded = false;
                        }
                    }
                })
                .catch(error => console.error('Error updating badge:', error));
        }
    }, 120000); // 2 minutes
})();
</script>

<?php
}
?>

