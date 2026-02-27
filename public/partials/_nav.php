<?php
// _nav.php — Dark sidebar navigation
// $activePage should be set before including this file
$activePage = $activePage ?? '';
$userName   = $_SESSION['user_name'] ?? 'User';
$initials   = strtoupper(substr($userName, 0, 1));

$navItems = [
    ['href' => '/cashtimeline/public/dashboard',     'icon' => 'fa-chart-gantt',    'label' => 'Timeline',    'key' => 'dashboard'],
    ['href' => '/cashtimeline/public/accounts',      'icon' => 'fa-building-columns','label' => 'Accounts',   'key' => 'accounts'],
    ['href' => '/cashtimeline/public/transactions',  'icon' => 'fa-arrow-right-arrow-left', 'label' => 'Transactions', 'key' => 'transactions'],
    ['href' => '/cashtimeline/public/recurring',     'icon' => 'fa-rotate',          'label' => 'Recurring',   'key' => 'recurring'],
];

$settingsItems = [
    ['href' => '/cashtimeline/public/settings',      'icon' => 'fa-gear',           'label' => 'Settings',    'key' => 'settings'],
];
?>
<!-- Mobile overlay backdrop -->
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>

<aside class="sidebar" id="mainSidebar">
    <!-- Logo + close button on mobile -->
    <div class="sidebar-logo">
        <div class="logo-icon"><i class="fa-solid fa-timeline"></i></div>
        <div class="logo-text">Cash<span>Timeline</span></div>
        <button class="sidebar-close-btn" onclick="closeSidebar()" aria-label="Close menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu</div>
        <?php foreach ($navItems as $item): ?>
            <a href="<?= $item['href'] ?>"
               class="sidebar-link <?= $activePage === $item['key'] ? 'active' : '' ?>"
               onclick="closeSidebar()">
                <i class="fa-solid <?= $item['icon'] ?>"></i>
                <?= $item['label'] ?>
                <?php if (isset($item['badge']) && $item['badge'] > 0): ?>
                    <span class="notification-badge"><?= $item['badge'] ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
        
        <div class="nav-section-label mt-4">System</div>
        <?php foreach ($settingsItems as $item): ?>
            <a href="<?= $item['href'] ?>"
               class="sidebar-link <?= $activePage === $item['key'] ? 'active' : '' ?>"
               onclick="closeSidebar()">
                <i class="fa-solid <?= $item['icon'] ?>"></i>
                <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- User footer -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar"><?= htmlspecialchars($initials) ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($userName) ?></div>
            </div>
        </div>
        <a href="/cashtimeline/public/logout" class="sidebar-link mt-1" style="color:#ef4444;">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Sign out
        </a>
    </div>
</aside>
