<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';

$auth = new AuthController();
$auth->logout();
header('Location: /cashtimeline/public/index');
exit;
