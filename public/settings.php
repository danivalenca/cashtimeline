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
    
}

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
                            
                            <div class="mb-4">
                                <label class="form-label-sm">Email Address</label>
                                <input type="email" name="email" class="form-control-ct" 
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                       placeholder="your@email.com">
                            </div>
                            
                            <button type="submit" class="btn-accent">
                                <i class="fa-solid fa-check me-2"></i>Save Contact Information
                            </button>
                        </form>
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
