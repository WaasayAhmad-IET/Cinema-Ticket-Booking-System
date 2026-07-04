<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireCustomerLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: movies.php");
    exit();
}

$show_id = (int)($_POST['show_id'] ?? 0);
$selectedSeatsRaw = $_POST['selected_seats'] ?? '';
$seatIds = array_filter(array_map('intval', explode(',', $selectedSeatsRaw)));

if ($show_id <= 0 || empty($seatIds)) {
    $_SESSION['flash_error'] = "Please select at least one seat.";
    header("Location: seats.php?show_id=" . $show_id);
    exit();
}

$customer_id = $_SESSION['customer_id'];
$paymentMethod = in_array($_POST['payment_method'] ?? '', ['Card','Cash','Mobile']) ? $_POST['payment_method'] : 'Card';

$bookedBookingIds = [];
$errors = [];

foreach ($seatIds as $seat_id) {
    // Each call to UserBookSeat creates one Booking + one SeatBooking row.
    // The PreventDoubleSeatBooking trigger will reject already-booked seats.
    $stmt = mysqli_prepare($conn, "CALL UserBookSeat(?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iii", $customer_id, $show_id, $seat_id);

    if (mysqli_stmt_execute($stmt)) {
        $newBookingId = mysqli_insert_id($conn);
        $bookedBookingIds[] = $newBookingId;
    } else {
        $errors[] = "Seat ID $seat_id could not be booked: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);
    // Clear out any pending result sets from the CALL
    while (mysqli_more_results($conn)) { mysqli_next_result($conn); }
}

// Create a Payment record for each successfully created booking
foreach ($bookedBookingIds as $bId) {
    $amountStmt = mysqli_prepare($conn, "SELECT SUM(price) AS total FROM SeatBooking WHERE booking_id = ?");
    mysqli_stmt_bind_param($amountStmt, "i", $bId);
    mysqli_stmt_execute($amountStmt);
    $amountRow = mysqli_stmt_get_result($amountStmt)->fetch_assoc();
    $amount = $amountRow['total'] ?? 0;
    mysqli_stmt_close($amountStmt);

    $payStmt = mysqli_prepare($conn, "INSERT INTO Payment (booking_id, amount, payment_method, status) VALUES (?, ?, ?, 'Success')");
    mysqli_stmt_bind_param($payStmt, "ids", $bId, $amount, $paymentMethod);
    mysqli_stmt_execute($payStmt);
    mysqli_stmt_close($payStmt);
}

if (!empty($bookedBookingIds) && empty($errors)) {
    $_SESSION['flash_success'] = count($bookedBookingIds) . " seat(s) booked successfully!";
    header("Location: booking_confirmation.php?ids=" . implode(',', $bookedBookingIds));
    exit();
} elseif (!empty($bookedBookingIds) && !empty($errors)) {
    $_SESSION['flash_error'] = "Some seats could not be booked: " . implode(' ', $errors);
    header("Location: booking_confirmation.php?ids=" . implode(',', $bookedBookingIds));
    exit();
} else {
    $_SESSION['flash_error'] = "Booking failed: " . implode(' ', $errors);
    header("Location: seats.php?show_id=" . $show_id);
    exit();
}
