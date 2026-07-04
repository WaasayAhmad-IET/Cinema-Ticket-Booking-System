<?php
require_once 'includes/auth.php';
session_unset();
session_destroy();
session_start();
$_SESSION['flash_success'] = "You have been logged out.";
header("Location: login.php");
exit();
