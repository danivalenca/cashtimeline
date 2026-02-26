<?php
// _head.php — Common <head> block for all protected pages
// $pageTitle should be set before including this partial
$pageTitle = $pageTitle ?? 'CashTimeline';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="CashTimeline">
    <meta name="theme-color" content="#6366f1">
    <meta name="description" content="Personal finance tracker — timelines, accounts and recurring transactions.">
    <title><?= htmlspecialchars($pageTitle) ?> — CashTimeline</title>
    <!-- PWA -->
    <link rel="manifest" href="/cashtimeline/public/manifest.json">
    <link rel="apple-touch-icon" href="/cashtimeline/public/assets/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/cashtimeline/public/assets/icons/icon-192.png">
    <!-- Fonts & libs -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="/cashtimeline/public/assets/css/app.css" rel="stylesheet">
</head>
<body>
<!-- Service Worker registration -->
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/cashtimeline/public/sw.js', { scope: '/cashtimeline/public/' })
        .catch(err => console.warn('SW registration failed:', err));
    });
  }
</script>
<!-- Mobile topbar (hamburger + logo, hidden on desktop) -->
<div class="mobile-topbar" id="mobileTopbar">
    <button class="hamburger-btn" onclick="openSidebar()" aria-label="Open menu">
        <i class="fa-solid fa-bars"></i>
    </button>
    <span class="mobile-logo-text">Cash<span>Timeline</span></span>
</div>
