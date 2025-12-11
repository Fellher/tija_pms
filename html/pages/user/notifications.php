<?php
/**
 * User Notification Center
 * View and manage all notifications
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

$userID = $userDetails->ID;

// Get filter from URL
$statusFilter = isset($_GET['filter']) ? Utility::clean_string($_GET['filter']) : 'all';
$priorityFilter = isset($_GET['priority']) ? Utility::clean_string($_GET['priority']) : '';

// Get notification statistics
$unreadCount = Notification::getUnreadCount($userID, $DBConn);

?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="ri-notification-3-line me-2"></i>
                        Notifications
                    </h2>
                    <p class="text-muted">
                        <span id="unreadBadge" class="badge bg-primary"><?php echo $unreadCount; ?></span>
                        unread notifications
                    </p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-secondary" id="markAllReadBtn">
                        <i class="ri-check-double-line"></i> Mark All as Read
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="refreshBtn">
                        <i class="ri-refresh-line"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="text-muted me-2">Filter:</span>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="statusFilter" id="filterAll" value="all" <?php echo $statusFilter === 'all' ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-secondary" for="filterAll">All</label>

                            <input type="radio" class="btn-check" name="statusFilter" id="filterUnread" value="unread" <?php echo $statusFilter === 'unread' ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-secondary" for="filterUnread">Unread</label>

                            <input type="radio" class="btn-check" name="statusFilter" id="filterRead" value="read" <?php echo $statusFilter === 'read' ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-secondary" for="filterRead">Read</label>
                        </div>

                        <span class="text-muted ms-3 me-2">Priority:</span>
                        <select class="form-select form-select-sm w-auto" id="priorityFilter">
                            <option value="">All Priorities</option>
                            <option value="low" <?php echo $priorityFilter === 'low' ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo $priorityFilter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo $priorityFilter === 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="critical" <?php echo $priorityFilter === 'critical' ? 'selected' : ''; ?>>Critical</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            <div id="notificationsContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading notifications...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Load More -->
    <div class="row mt-3" id="loadMoreContainer" style="display: none;">
        <div class="col-12 text-center">
            <button type="button" class="btn btn-outline-primary" id="loadMoreBtn">
                <i class="ri-arrow-down-line"></i> Load More
            </button>
        </div>
    </div>
</div>

<style>
.notification-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.notification-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.notification-item.unread {
    background: #eff6ff;
    border-left: 4px solid #6c5ce7;
}

.notification-item.read {
    opacity: 0.8;
}

.notification-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 24px;
    flex-shrink: 0;
}

.notification-icon.priority-low {
    background: #f3f4f6;
    color: #6b7280;
}

.notification-icon.priority-medium {
    background: #dbeafe;
    color: #3b82f6;
}

.notification-icon.priority-high {
    background: #fed7aa;
    color: #f97316;
}

.notification-icon.priority-critical {
    background: #fee2e2;
    color: #ef4444;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    margin-bottom: 4px;
    color: #1f2937;
}

.notification-body {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 8px;
}

.notification-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 12px;
    color: #9ca3af;
}

.notification-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 64px;
    color: #d1d5db;
    margin-bottom: 16px;
}

.empty-state h4 {
    color: #6b7280;
    margin-bottom: 8px;
}

.empty-state p {
    color: #9ca3af;
}
</style>

<script>
let currentOffset = 0;
const limit = 20;
let hasMore = true;
let isLoading = false;

document.addEventListener('DOMContentLoaded', function() {
    // Load initial notifications
    loadNotifications();

    // Filter change handlers
    document.querySelectorAll('input[name="statusFilter"]').forEach(radio => {
        radio.addEventListener('change', function() {
            currentOffset = 0;
            hasMore = true;
            loadNotifications();
        });
    });

    document.getElementById('priorityFilter').addEventListener('change', function() {
        currentOffset = 0;
        hasMore = true;
        loadNotifications();
    });

    // Mark all as read
    document.getElementById('markAllReadBtn').addEventListener('click', markAllAsRead);

    // Refresh
    document.getElementById('refreshBtn').addEventListener('click', function() {
        currentOffset = 0;
        hasMore = true;
        loadNotifications();
    });

    // Load more
    document.getElementById('loadMoreBtn').addEventListener('click', function() {
        loadNotifications(true);
    });
});

function loadNotifications(append = false) {
    if (isLoading) return;
    isLoading = true;

    const statusFilter = document.querySelector('input[name="statusFilter"]:checked').value;
    const priorityFilter = document.getElementById('priorityFilter').value;

    if (!append) {
        document.getElementById('notificationsContainer').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading notifications...</p>
            </div>
        `;
    }

    const params = new URLSearchParams({
        limit: limit,
        offset: append ? currentOffset : 0
    });

    if (statusFilter !== 'all') {
        params.append('status', statusFilter);
    }

    if (priorityFilter) {
        params.append('priority', priorityFilter);
    }

    fetch(`<?php echo $base; ?>php/scripts/notifications/get_user_notifications.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (!append) {
                    currentOffset = 0;
                }

                renderNotifications(data.notifications, append);

                // Update unread badge
                document.getElementById('unreadBadge').textContent = data.unreadCount;

                // Handle load more button
                if (data.notifications.length < limit) {
                    hasMore = false;
                    document.getElementById('loadMoreContainer').style.display = 'none';
                } else {
                    hasMore = true;
                    document.getElementById('loadMoreContainer').style.display = 'block';
                }

                currentOffset += data.notifications.length;
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            showError('Failed to load notifications');
        })
        .finally(() => {
            isLoading = false;
        });
}

function renderNotifications(notifications, append) {
    const container = document.getElementById('notificationsContainer');

    if (!append) {
        container.innerHTML = '';
    }

    if (notifications.length === 0 && !append) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="ri-notification-off-line"></i>
                <h4>No Notifications</h4>
                <p>You're all caught up! No notifications to display.</p>
            </div>
        `;
        return;
    }

    notifications.forEach(notif => {
        const notifElement = createNotificationElement(notif);
        container.appendChild(notifElement);
    });
}

function createNotificationElement(notif) {
    const div = document.createElement('div');
    div.className = `notification-item ${notif.status}`;
    div.dataset.notificationId = notif.notificationID;

    const priorityClass = `priority-${notif.priority}`;
    const icon = notif.icon || 'ri-notification-line';

    div.innerHTML = `
        <div class="d-flex gap-3">
            <div class="notification-icon ${priorityClass}">
                <i class="${icon}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-title">${notif.title}</div>
                <div class="notification-body">${notif.body}</div>
                <div class="notification-meta">
                    <span><i class="ri-time-line"></i> ${notif.timeAgo}</span>
                    ${notif.moduleName ? `<span><i class="ri-folder-line"></i> ${notif.moduleName}</span>` : ''}
                    ${notif.priority === 'high' || notif.priority === 'critical' ?
                        `<span class="notification-badge" style="background: #fee2e2; color: #dc2626;">${notif.priority.toUpperCase()}</span>` : ''}
                </div>
            </div>
            ${notif.status === 'unread' ? '<div class="ms-2"><span class="badge bg-primary">New</span></div>' : ''}
        </div>
    `;

    div.addEventListener('click', function() {
        handleNotificationClick(notif);
    });

    return div;
}

function handleNotificationClick(notif) {
    if (notif.status === 'unread') {
        markAsRead(notif.notificationID);
    }

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
            const notifElement = document.querySelector(`[data-notification-id="${notificationID}"]`);
            if (notifElement) {
                notifElement.classList.remove('unread');
                notifElement.classList.add('read');
                const newBadge = notifElement.querySelector('.badge.bg-primary');
                if (newBadge) {
                    newBadge.remove();
                }
            }

            // Update unread count
            document.getElementById('unreadBadge').textContent = data.unreadCount;
        }
    })
    .catch(error => {
        console.error('Error marking as read:', error);
    });
}

function markAllAsRead() {
    if (!confirm('Mark all notifications as read?')) {
        return;
    }

    fetch('<?php echo $base; ?>php/scripts/notifications/mark_all_read.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('All notifications marked as read', 'success');
            // Reload notifications
            currentOffset = 0;
            hasMore = true;
            loadNotifications();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to mark all as read', 'error');
    });
}

function showToast(message, type = 'info') {
    // Use your existing toast notification system
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
    } else {
        // Fallback: simple alert if toast helper is unavailable
        alert(message);
    }
}

function showError(message) {
    document.getElementById('notificationsContainer').innerHTML = `
        <div class="alert alert-danger">
            <i class="ri-error-warning-line me-2"></i>
            ${message}
        </div>
    `;
}
</script>

