<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdminLogin();

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "CALL AdminCancelBooking(?)");
mysqli_stmt_bind_param($stmt, "i", $booking_id);
if (mysqli_stmt_execute($stmt)) {
    $_SESSION['flash_success'] = "Booking #$booking_id cancelled.";
} else {
    $_SESSION['flash_error'] = "Failed to cancel booking.";
}
mysqli_stmt_close($stmt);
while (mysqli_more_results($conn)) { mysqli_next_result($conn); }

header("Location: bookings.php");
exit();
