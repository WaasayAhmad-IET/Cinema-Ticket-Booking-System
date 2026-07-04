<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireCustomerLogin();
$pageTitle = 'My Bookings';

$customer_id = $_SESSION['customer_id'];

// UserBookingHistory view doesn't include booking_id or customer_id filter directly usable,
// so join Booking again to get booking_id for cancel actions, scoped to this customer.
$sql = "
    SELECT b.booking_id, c.first_name, c.last_name, m.title, sh.show_date, sh.start_time,
           CONCAT(s.seat_row, s.seat_number) AS seat_number, sb.price, b.status
    FROM Booking b
    JOIN Customer c ON b.customer_id = c.customer_id
    JOIN Shows sh ON b.show_id = sh.show_id
    JOIN Movie m ON sh.movie_id = m.movie_id
    JOIN SeatBooking sb ON sb.booking_id = b.booking_id
    JOIN Seat s ON s.seat_id = sb.seat_id
    WHERE b.customer_id = ?
    ORDER BY b.booking_date DESC
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

include 'includes/header.php';
?>

<div class="container py-4">
  <h3 class="fw-bold mb-4"><i class="fa-solid fa-ticket me-2"></i>My Bookings</h3>

  <div class="table-responsive">
    <table class="table table-cinema align-middle bg-white shadow-sm rounded">
      <thead>
        <tr>
          <th>Booking #</th>
          <th>Movie</th>
          <th>Show Date</th>
          <th>Time</th>
          <th>Seat</th>
          <th>Price</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">You have no bookings yet. <a href="movies.php">Browse movies</a></td></tr>
        <?php else: ?>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <?php
              $showDateTime = strtotime($row['show_date'] . ' ' . $row['start_time']);
              $canCancel = ($row['status'] === 'Confirmed') && ($showDateTime > time());
            ?>
            <tr>
              <td>#<?php echo (int)$row['booking_id']; ?></td>
              <td><?php echo h($row['title']); ?></td>
              <td><?php echo date('M j, Y', $showDateTime); ?></td>
              <td><?php echo date('g:i A', $showDateTime); ?></td>
              <td><?php echo h($row['seat_number']); ?></td>
              <td>Rs. <?php echo number_format($row['price'], 2); ?></td>
              <td>
                <?php if ($row['status'] === 'Confirmed'): ?>
                  <span class="badge bg-success">Confirmed</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Cancelled</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($canCancel): ?>
                  <a href="cancel_booking.php?id=<?php echo (int)$row['booking_id']; ?>"
                     class="btn btn-outline-danger btn-sm"
                     onclick="return confirm('Cancel this booking?');">Cancel</a>
                <?php else: ?>
                  <span class="text-muted small">&mdash;</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
