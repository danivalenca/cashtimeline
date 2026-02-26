<?php
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: /cashtimeline/public/dashboard');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';

$auth  = new AuthController();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($ok) {
        header('Location: /cashtimeline/public/dashboard');
        exit;
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="CashTimeline">
    <link rel="manifest" href="/cashtimeline/public/manifest.json">
    <link rel="apple-touch-icon" href="/cashtimeline/public/assets/icons/icon-192.png">
    <title>Login — CashTimeline</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="/cashtimeline/public/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon"><i class="fa-solid fa-timeline"></i></div>
            <h1>Cash<span>Timeline</span></h1>
            <p>Personal finance, visualized over time</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 mb-3" style="font-size:13px;border-radius:8px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
                <label class="form-label-sm">Email address</label>
                <input type="email" name="email" class="form-control-ct" placeholder="you@email.com" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label-sm">Password</label>
                <input type="password" name="password" class="form-control-ct" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-accent w-100 justify-content-center mb-3" style="height:42px;">
                Sign in
            </button>
        </form>

        <div class="text-center" style="font-size:13px;color:#6b7280;">
            No account? <a href="/cashtimeline/public/register" class="auth-link">Create one</a>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
