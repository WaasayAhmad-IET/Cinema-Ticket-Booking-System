<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdminLogin();

$screen_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if screen has shows scheduled
$check = mysqli_prepare($conn, "SELECT COUNT(*) c FROM Shows WHERE screen_id = ?");
mysqli_stmt_bind_param($check, "i", $screen_id);
mysqli_stmt_execute($check);
$count = mysqli_stmt_get_result($check)->fetch_assoc()['c'];
mysqli_stmt_close($check);

if ($count > 0) {
    $_SESSION['flash_error'] = "Cannot delete this screen because it has shows scheduled on it. Remove its shows first.";
} else {
    $delSeats = mysqli_prepare($conn, "DELETE FROM Seat WHERE screen_id = ?");
    mysqli_stmt_bind_param($delSeats, "i", $screen_id);
    mysqli_stmt_execute($delSeats);
    mysqli_stmt_close($delSeats);

    $stmt = mysqli_prepare($conn, "DELETE FROM Screen WHERE screen_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $screen_id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = "Screen deleted successfully.";
    } else {
        $_SESSION['flash_error'] = "Failed to delete screen.";
    }
    mysqli_stmt_close($stmt);
}

header("Location: screens.php");
exit();
