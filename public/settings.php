<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/models/UserSettings.php';
require_once __DIR__ . '/../src/models/User.php';

AuthController::requireAuth();
$userId = AuthController::userId();

$settingsModel = new UserSettings();
$userModel = new User();
$user = $userModel->find($userId);

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_contact') {
        // Update user email and phone
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['msg' => 'Invalid email address.', 'type' => 'danger'];
        } else {
            $userModel->updateContact($userId, $email, $phone);
            $_SESSION['flash'] = ['msg' => 'Contact information updated successfully.', 'type' => 'success'];
        }
        
        header('Location: /cashtimeline/public/settings');
        exit;
    }
    
    if ($action === 'save_notifications') {
        $prefs = [
            'notifications_enabled' => isset($_POST['notifications_enabled']) ? 1 : 0,
            'notifications_email' => isset($_POST['notifications_email']) ? 1 : 0,
            'notifications_sms' => isset($_POST['notifications_sms']) ? 1 : 0,
            'notifications_in_app' => isset($_POST['notifications_in_app']) ? 1 : 0,
            'notify_transaction_due' => isset($_POST['notify_transaction_due']) ? 1 : 0,
            'notify_recurring_due' => isset($_POST['notify_recurring_due']) ? 1 : 0,
            'notify_low_balance' => isset($_POST['notify_low_balance']) ? 1 : 0,
            'notify_days_before' => (int)($_POST['notify_days_before'] ?? 1),
            'quiet_hours_start' => $_POST['quiet_hours_start'] ?? '22:00',
            'quiet_hours_end' => $_POST['quiet_hours_end'] ?? '08:00',
        ];
        
        if ($settingsModel->saveNotificationPreferences($userId, $prefs)) {
            $_SESSION['flash'] = ['msg' => 'Notification settings saved successfully.', 'type' => 'success'];
        } else {
            $_SESSION['flash'] = ['msg' => 'Failed to save settings.', 'type' => 'danger'];
        }
        
        header('Location: /cashtimeline/public/settings');
        exit;
    }
}

$prefs = $settingsModel->getNotificationPreferences($userId);

$pageTitle = 'Settings';
$activePage = 'settings';
include __DIR__ . '/partials/_head.php';
?>

<div class="app-shell">
    <?php include __DIR__ . '/partials/_nav.php'; ?>

    <div class="main-content">
        <!-- Top bar -->
        <div class="topbar">
            <div class="topbar-title">
                <i class="fa-solid fa-gear me-2" style="color:var(--accent);"></i>
                Settings
            </div>
        </div>

        <?php include __DIR__ . '/partials/_flash.php'; ?>

        <div class="page-body">
            <div class="container" style="max-width:900px;">
                
                <!-- Contact Information Card -->
                <div class="ct-card mb-4">
                    <div class="ct-card-header">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-address-card me-2" style="color:var(--accent);"></i>
                            Contact Information
                        </h5>
                    </div>
                    <div class="ct-card-body">
                        <form method="POST" action="/cashtimeline/public/settings">
                            <input type="hidden" name="action" value="save_contact">
                            
                            <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px;">
                                Required for email and SMS notifications. Your information is private and will only be used for notifications.
                            </p>
                            
                            <div class="mb-3">
                                <label class="form-label-sm">Email Address</label>
                                <input type="email" name="email" class="form-control-ct" 
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                       placeholder="your@email.com">
                                <small style="font-size:11px;color:var(--text-muted);display:block;margin-top:4px;">
                                    Used for email notifications and account recovery
                                </small>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label-sm">Phone Number</label>
                                <input type="tel" name="phone" class="form-control-ct" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                       placeholder="+1 234 567 8900">
                                <small style="font-size:11px;color:var(--text-muted);display:block;margin-top:4px;">
                                    Include country code (e.g., +1 for US/Canada). Used for SMS notifications.
                                </small>
                            </div>
                            
                            <button type="submit" class="btn-accent">
                                <i class="fa-solid fa-check me-2"></i>Save Contact Information
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Notification Settings Card -->
                <div class="ct-card mb-4">
                    <div class="ct-card-header">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-bell me-2" style="color:var(--accent);"></i>
                            Notification Preferences
                        </h5>
                    </div>
                    <div class="ct-card-body">
                        <form method="POST" action="/cashtimeline/public/settings">
                            <input type="hidden" name="action" value="save_notifications">
                            
                            <!-- Master Toggle -->
                            <div class="mb-4 pb-3 border-bottom">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="notificationsEnabled" 
                                           name="notifications_enabled" value="1" 
                                           <?= $prefs['enabled'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notificationsEnabled">
                                        <strong>Enable Notifications</strong>
                                        <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                                            Turn all notifications on or off
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Notification Channels -->
                            <div class="mb-4">
                                <h6 class="mb-3" style="font-size:14px;color:var(--text-primary);">
                                    <i class="fa-solid fa-paper-plane me-2" style="color:var(--accent);font-size:13px;"></i>
                                    Notification Channels
                                </h6>
                                
                                <div class="form-check form-switch mb-3">
                                    <input type="checkbox" class="form-check-input" id="notifyInApp" 
                                           name="notifications_in_app" value="1" 
                                           <?= $prefs['in_app_enabled'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notifyInApp">
                                        <i class="fa-solid fa-bell" style="color:var(--accent);width:20px;"></i>
                                        In-App Notifications
                                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                            Show notifications within the app
                                        </div>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input type="checkbox" class="form-check-input" id="notifyEmail" 
                                           name="notifications_email" value="1" 
                                           <?= $prefs['email_enabled'] ? 'checked' : '' ?>
                                           <?= empty($user['email']) ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="notifyEmail">
                                        <i class="fa-solid fa-envelope" style="color:#3b82f6;width:20px;"></i>
                                        Email Notifications
                                        <?php if (empty($user['email'])): ?>
                                            <span class="badge" style="background:#ef4444;font-size:9px;padding:2px 6px;margin-left:6px;">EMAIL REQUIRED</span>
                                        <?php endif; ?>
                                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                            <?= empty($user['email']) 
                                                ? 'Add your email address above to enable' 
                                                : 'Receive notifications via email' ?>
                                        </div>
                                    </label>
                                </div>

                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="notifySMS" 
                                           name="notifications_sms" value="1" 
                                           <?= $prefs['sms_enabled'] ? 'checked' : '' ?>
                                           <?= empty($user['phone']) ? 'disabled' : '' ?>>
                                    <label class="form-check-label" for="notifySMS">
                                        <i class="fa-solid fa-comment-sms" style="color:#22c55e;width:20px;"></i>
                                        SMS Notifications
                                        <?php if (empty($user['phone'])): ?>
                                            <span class="badge" style="background:#ef4444;font-size:9px;padding:2px 6px;margin-left:6px;">PHONE REQUIRED</span>
                                        <?php endif; ?>
                                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                            <?= empty($user['phone']) 
                                                ? 'Add your phone number above to enable' 
                                                : 'Receive text message notifications' ?>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Notification Types -->
                            <div class="mb-4 pb-3 border-bottom">
                                <h6 class="mb-3" style="font-size:14px;color:var(--text-primary);">
                                    <i class="fa-solid fa-list-check me-2" style="color:var(--accent);font-size:13px;"></i>
                                    What to Notify About
                                </h6>
                                
                                <div class="form-check form-switch mb-3">
                                    <input type="checkbox" class="form-check-input" id="notifyTransactions" 
                                           name="notify_transaction_due" value="1" 
                                           <?= $prefs['transaction_due'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notifyTransactions">
                                        <strong>Upcoming Transactions</strong>
                                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                            Get notified about scheduled transactions
                                        </div>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input type="checkbox" class="form-check-input" id="notifyRecurring" 
                                           name="notify_recurring_due" value="1" 
                                           <?= $prefs['recurring_due'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notifyRecurring">
                                        <strong>Recurring Transactions</strong>
                                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                            Get notified about recurring bills and income
                                        </div>
                                    </label>
                                </div>

                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="notifyLowBalance" 
                                           name="notify_low_balance" value="1" 
                                           <?= $prefs['low_balance'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notifyLowBalance">
                                        <strong>Low Balance Alerts</strong>
                                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                            Get alerted when upcoming expenses exceed your balance
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Timing Settings -->
                            <div class="mb-4">
                                <h6 class="mb-3" style="font-size:14px;color:var(--text-primary);">
                                    <i class="fa-solid fa-clock me-2" style="color:var(--accent);font-size:13px;"></i>
                                    Timing Settings
                                </h6>
                                
                                <div class="mb-3">
                                    <label class="form-label-sm">Notify me this many days before</label>
                                    <select name="notify_days_before" class="form-select-ct" style="max-width:200px;">
                                        <option value="0" <?= $prefs['days_before'] == 0 ? 'selected' : '' ?>>On the day</option>
                                        <option value="1" <?= $prefs['days_before'] == 1 ? 'selected' : '' ?>>1 day before</option>
                                        <option value="2" <?= $prefs['days_before'] == 2 ? 'selected' : '' ?>>2 days before</option>
                                        <option value="3" <?= $prefs['days_before'] == 3 ? 'selected' : '' ?>>3 days before</option>
                                        <option value="7" <?= $prefs['days_before'] == 7 ? 'selected' : '' ?>>1 week before</option>
                                    </select>
                                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                                        How early you want to be notified about upcoming transactions
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-sm">Quiet Hours Start</label>
                                        <input type="time" name="quiet_hours_start" class="form-control-ct" 
                                               value="<?= htmlspecialchars($prefs['quiet_hours_start']) ?>">
                                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                                            No notifications after this time
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-sm">Quiet Hours End</label>
                                        <input type="time" name="quiet_hours_end" class="form-control-ct" 
                                               value="<?= htmlspecialchars($prefs['quiet_hours_end']) ?>">
                                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                                            Resume notifications after this time
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Save Button -->
                            <div class="d-flex gap-2 pt-3 border-top">
                                <button type="submit" class="btn-accent">
                                    <i class="fa-solid fa-check me-2"></i>Save Notification Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="alert" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;border-radius:8px;padding:16px;">
                    <div style="display:flex;align-items:start;gap:12px;">
                        <i class="fa-solid fa-circle-info" style="color:#3b82f6;font-size:18px;margin-top:2px;"></i>
                        <div style="flex:1;">
                            <strong style="display:block;margin-bottom:4px;">Email & SMS Setup Required</strong>
                            <div style="font-size:12px;line-height:1.5;">
                                <strong>Email:</strong> Configure SMTP in <code>/config/email.php</code> or use PHP mail().<br>
                                <strong>SMS:</strong> Sign up for Twilio and configure in <code>/config/sms.php</code>.<br>
                                See <code>NOTIFICATION_SETUP.md</code> for detailed instructions.
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div><!-- end page-body -->
    </div><!-- end main-content -->
</div><!-- end app-shell -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="/cashtimeline/public/assets/js/app.js"></script>
</body>
</html>
