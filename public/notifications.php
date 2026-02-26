<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/controllers/NotificationController.php';

AuthController::requireAuth();
$userId = AuthController::userId();

$notificationController = new NotificationController();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'mark_read' && isset($_POST['id'])) {
        $notificationController->markAsRead((int)$_POST['id'], $userId);
        if (isset($_POST['ajax'])) {
            echo json_encode(['success' => true]);
            exit;
        }
    } elseif ($action === 'mark_all_read') {
        $notificationController->markAllAsRead($userId);
        $_SESSION['flash'] = ['msg' => 'All notifications marked as read.', 'type' => 'success'];
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        $notificationController->delete((int)$_POST['id'], $userId);
        $_SESSION['flash'] = ['msg' => 'Notification deleted.', 'type' => 'success'];
    } elseif ($action === 'delete_all') {
        $notificationController->deleteAll($userId);
        $_SESSION['flash'] = ['msg' => 'All notifications deleted.', 'type' => 'success'];
    }
    
    if (!isset($_POST['ajax'])) {
        header('Location: /cashtimeline/public/notifications');
        exit;
    }
}

$notifications = $notificationController->getRecent($userId, 50);

$pageTitle = 'Notifications';
$activePage = 'notifications';
include __DIR__ . '/partials/_head.php';
?>

<div class="app-shell">
    <?php include __DIR__ . '/partials/_nav.php'; ?>

    <div class="main-content">
        <!-- Top bar -->
        <div class="topbar">
            <div class="topbar-title">
                <i class="fa-solid fa-bell me-2" style="color:var(--accent);"></i>
                Notifications
            </div>
            <div class="topbar-actions">
                <?php if (!empty($notifications)): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="btn-ghost btn-sm" title="Mark all as read">
                        <i class="fa-solid fa-check-double"></i>
                    </button>
                </form>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete all notifications?')">
                    <input type="hidden" name="action" value="delete_all">
                    <button type="submit" class="btn-ghost btn-sm" title="Delete all">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <?php include __DIR__ . '/partials/_flash.php'; ?>

        <div class="page-body">
            <div class="container" style="max-width:900px;">

                <?php if (empty($notifications)): ?>
                    <div class="empty-state" style="padding:80px 20px;text-align:center;">
                        <i class="fa-solid fa-bell-slash" style="font-size:64px;color:var(--text-muted);opacity:0.3;margin-bottom:20px;"></i>
                        <h3 style="color:var(--text-secondary);font-size:18px;margin-bottom:8px;">No Notifications</h3>
                        <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;">
                            You're all caught up! Notifications about upcoming transactions will appear here.
                        </p>
                        <a href="/cashtimeline/public/settings" class="btn-ghost-accent">
                            <i class="fa-solid fa-gear me-2"></i>Configure Notifications
                        </a>
                    </div>
                <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($notifications as $notification): ?>
                            <?php
                                $iconClass = match($notification['type']) {
                                    'transaction_due' => 'fa-calendar-day',
                                    'recurring_due' => 'fa-repeat',
                                    'low_balance' => 'fa-triangle-exclamation',
                                    default => 'fa-bell'
                                };
                                $iconColor = match($notification['type']) {
                                    'transaction_due' => 'var(--accent)',
                                    'recurring_due' => '#8b5cf6',
                                    'low_balance' => '#ef4444',
                                    default => 'var(--text-muted)'
                                };
                                $isUnread = !$notification['is_read'];
                            ?>
                            <div class="notification-item <?= $isUnread ? 'unread' : '' ?>" 
                                 data-id="<?= $notification['id'] ?>">
                                <div class="notification-icon" style="background:<?= $iconColor ?>15;">
                                    <i class="fa-solid <?= $iconClass ?>" style="color:<?= $iconColor ?>;"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title">
                                        <?= htmlspecialchars($notification['title']) ?>
                                        <?php if ($isUnread): ?>
                                            <span class="unread-dot"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notification-message">
                                        <?= htmlspecialchars($notification['message']) ?>
                                    </div>
                                    <div class="notification-time">
                                        <?php
                                            $time = strtotime($notification['created_at']);
                                            $diff = time() - $time;
                                            if ($diff < 60) {
                                                echo 'Just now';
                                            } elseif ($diff < 3600) {
                                                echo floor($diff / 60) . ' minutes ago';
                                            } elseif ($diff < 86400) {
                                                echo floor($diff / 3600) . ' hours ago';
                                            } else {
                                                echo date('M j, Y g:i A', $time);
                                            }
                                        ?>
                                    </div>
                                </div>
                                <div class="notification-actions">
                                    <?php if ($isUnread): ?>
                                        <form method="POST" style="display:inline;" class="mark-read-form">
                                            <input type="hidden" name="action" value="mark_read">
                                            <input type="hidden" name="id" value="<?= $notification['id'] ?>">
                                            <input type="hidden" name="ajax" value="1">
                                            <button type="submit" class="btn-icon" title="Mark as read">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this notification?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $notification['id'] ?>">
                                        <button type="submit" class="btn-icon" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div><!-- end page-body -->
    </div><!-- end main-content -->
</div><!-- end app-shell -->

<style>
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.notification-item {
    display: flex;
    align-items: start;
    gap: 16px;
    padding: 16px 20px;
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
    transition: background 0.2s;
}
.notification-item:first-child {
    border-radius: 12px 12px 0 0;
}
.notification-item:last-child {
    border-bottom: none;
    border-radius: 0 0 12px 12px;
}
.notification-item:only-child {
    border-radius: 12px;
}
.notification-item:hover {
    background: var(--bg-hover);
}
.notification-item.unread {
    background: var(--accent-bg);
}
.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 16px;
}
.notification-content {
    flex: 1;
    min-width: 0;
}
.notification-title {
    font-weight: 600;
    font-size: 14px;
    color: var(--text-primary);
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.unread-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--accent);
    flex-shrink: 0;
}
.notification-message {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 4px;
}
.notification-time {
    font-size: 11px;
    color: var(--text-muted);
}
.notification-actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}
.btn-icon {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.btn-icon:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/cashtimeline/public/assets/js/app.js"></script>
<script>
// Mark as read via AJAX
document.querySelectorAll('.mark-read-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const notificationId = formData.get('id');
        
        try {
            const response = await fetch('/cashtimeline/public/notifications', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                const item = document.querySelector(`[data-id="${notificationId}"]`);
                if (item) {
                    item.classList.remove('unread');
                    this.remove();
                    
                    // Update bell count if exists
                    const bellBadge = document.querySelector('.notification-badge');
                    if (bellBadge) {
                        let count = parseInt(bellBadge.textContent) - 1;
                        if (count <= 0) {
                            bellBadge.remove();
                        } else {
                            bellBadge.textContent = count;
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    });
});
</script>
</body>
</html>
