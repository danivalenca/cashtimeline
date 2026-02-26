<?php
// _flash.php — Displays and clears session flash messages
if (!empty($_SESSION['flash'])):
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $typeMap = ['success' => 'flash-success', 'danger' => 'flash-danger', 'warning' => 'flash-warning'];
    $cls = $typeMap[$flash['type']] ?? 'flash-success';
?>
<div class="flash-bar <?= $cls ?> d-flex align-items-center gap-2">
    <?php if ($flash['type'] === 'success'): ?>
        <i class="fa-solid fa-circle-check"></i>
    <?php elseif ($flash['type'] === 'danger'): ?>
        <i class="fa-solid fa-circle-xmark"></i>
    <?php else: ?>
        <i class="fa-solid fa-triangle-exclamation"></i>
    <?php endif; ?>
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>
