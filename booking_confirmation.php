<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireCustomerLogin();
$pageTitle = 'Booking Confirmation';

$idsRaw = $_GET['ids'] ?? '';
$ids = array_filter(array_map('intval', explode(',', $idsRaw)));

$bookings = [];
if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $sql = "
        SELECT b.booking_id, b.booking_date, b.status, m.title, sh.show_date, sh.start_time,
               CONCAT(s.seat_row, s.seat_number) AS seat_label, sb.price, sc.screen_name,
               p.payment_method, p.amount
        FROM Booking b
        JOIN Shows sh ON b.show_id = sh.show_id
        JOIN Movie m ON sh.movie_id = m.movie_id
        JOIN Screen sc ON sh.screen_id = sc.screen_id
        JOIN SeatBooking sb ON sb.booking_id = b.booking_id
        JOIN Seat s ON s.seat_id = sb.seat_id
        LEFT JOIN Payment p ON p.booking_id = b.booking_id
        WHERE b.booking_id IN ($placeholders) AND b.customer_id = ?
    ";
    $stmt = mysqli_prepare($conn, $sql);
    $allTypes = $types . 'i';
    $allParams = array_merge($ids, [$_SESSION['customer_id']]);
    mysqli_stmt_bind_param($stmt, $allTypes, ...$allParams);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
    mysqli_stmt_close($stmt);
}

include 'includes/header.php';
?>

<div class="container py-4">
  <div class="text-center mb-4">
    <i class="fa-solid fa-circle-check fa-3x text-success mb-2"></i>
    <h3 class="fw-bold">Booking Confirmed!</h3>
    <p class="text-muted">Your e-ticket details are below.</p>
  </div>

  <?php if (empty($bookings)): ?>
    <div class="alert alert-warning text-center">No booking details found.</div>
  <?php else: ?>
    <?php $total = array_sum(array_column($bookings, 'price')); ?>
    <div class="card mx-auto" style="max-width:600px;">
      <div class="card-body">
        <h5 class="fw-bold mb-1"><?php echo h($bookings[0]['title']); ?></h5>
        <p class="text-muted mb-3">
          <?php echo date('D, M j, Y', strtotime($bookings[0]['show_date'])); ?> &bull;
          <?php echo date('g:i A', strtotime($bookings[0]['start_time'])); ?> &bull;
          <?php echo h($bookings[0]['screen_name']); ?>
        </p>
        <table class="table">
          <thead><tr><th>Booking #</th><th>Seat</th><th>Status</th><th class="text-end">Price</th></tr></thead>
          <tbody>
          <?php foreach ($bookings as $b): ?>
            <tr>
              <td>#<?php echo (int)$b['booking_id']; ?></td>
              <td><?php echo h($b['seat_label']); ?></td>
              <td><span class="badge bg-success"><?php echo h($b['status']); ?></span></td>
              <td class="text-end">Rs. <?php echo number_format($b['price'], 2); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr><th colspan="3" class="text-end">Total Paid</th><th class="text-end">Rs. <?php echo number_format($total, 2); ?></th></tr>
          </tfoot>
        </table>
        <p class="small text-muted mb-0">Payment Method: <?php echo h($bookings[0]['payment_method'] ?? 'N/A'); ?></p>
      </div>
    </div>
  <?php endif; ?>

  <div class="text-center mt-4">
    <a href="movies.php" class="btn btn-outline-secondary me-2">Browse More Movies</a>
    <a href="history.php" class="btn btn-cinema">View My Bookings</a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
