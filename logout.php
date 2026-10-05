<?php
require 'auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
check_csrf();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
