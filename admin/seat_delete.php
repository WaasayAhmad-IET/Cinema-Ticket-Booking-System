<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdminLogin();

$seat_id = isset($_GET['seat_id']) ? (int)$_GET['seat_id'] : 0;
$screen_id = isset($_GET['screen_id']) ? (int)$_GET['screen_id'] : 0;

$stmt = mysqli_prepare($conn, "DELETE FROM Seat WHERE seat_id = ?");
mysqli_stmt_bind_param($stmt, "i", $seat_id);
if (mysqli_stmt_execute($stmt)) {
    $_SESSION['flash_success'] = "Seat removed.";
} else {
    $_SESSION['flash_error'] = "Could not remove seat (it may already have bookings).";
}
mysqli_stmt_close($stmt);

header("Location: seats.php?screen_id=$screen_id");
exit();
