<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireCustomerLogin();

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customer_id = $_SESSION['customer_id'];

if ($booking_id <= 0) {
    $_SESSION['flash_error'] = "Invalid booking.";
    header("Location: history.php");
    exit();
}

$stmt = mysqli_prepare($conn, "CALL UserCancelBooking(?, ?)");
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $customer_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['flash_success'] = "Booking #$booking_id cancelled successfully.";
} else {
    $_SESSION['flash_error'] = "Could not cancel booking: " . mysqli_stmt_error($stmt);
}
mysqli_stmt_close($stmt);
while (mysqli_more_results($conn)) { mysqli_next_result($conn); }

header("Location: history.php");
exit();
