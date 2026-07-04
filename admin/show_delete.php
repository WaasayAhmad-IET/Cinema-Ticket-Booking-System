<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdminLogin();

$show_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "UPDATE Shows SET is_active = FALSE WHERE show_id = ?");
mysqli_stmt_bind_param($stmt, "i", $show_id);
if (mysqli_stmt_execute($stmt)) {
    $_SESSION['flash_success'] = "Show deactivated successfully.";
} else {
    $_SESSION['flash_error'] = "Failed to deactivate show.";
}
mysqli_stmt_close($stmt);

header("Location: shows.php");
exit();
