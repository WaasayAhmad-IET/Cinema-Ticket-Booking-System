<?php
$pageTitle = 'All Bookings';
require_once 'includes/admin_header.php';

// Uses AdminBookingHistory view, joined for booking_id-level cancel action
$bookings = mysqli_query($conn, "
    SELECT b.booking_id, c.first_name, c.last_name, m.title, sh.show_date, sh.start_time, b.status, b.booking_date
    FROM Booking b
    JOIN Customer c ON b.customer_id = c.customer_id
    JOIN Shows sh ON b.show_id = sh.show_id
    JOIN Movie m ON sh.movie_id = m.movie_id
    ORDER BY b.booking_date DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-bold mb-0">All Bookings</h5>
</div>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-cinema mb-0 align-middle">
      <thead><tr><th>#</th><th>Customer</th><th>Movie</th><th>Show Date/Time</th><th>Booked On</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($bookings) === 0): ?>
        <tr><td colspan="7" class="text-center text-muted py-3">No bookings found.</td></tr>
      <?php else: ?>
        <?php while ($b = mysqli_fetch_assoc($bookings)): ?>
          <tr>
            <td>#<?php echo (int)$b['booking_id']; ?></td>
            <td><?php echo h($b['first_name'] . ' ' . $b['last_name']); ?></td>
            <td><?php echo h($b['title']); ?></td>
            <td><?php echo date('M j, Y g:i A', strtotime($b['show_date'] . ' ' . $b['start_time'])); ?></td>
            <td><?php echo date('M j, Y g:i A', strtotime($b['booking_date'])); ?></td>
            <td>
              <?php if ($b['status'] === 'Confirmed'): ?>
                <span class="badge bg-success">Confirmed</span>
              <?php else: ?>
                <span class="badge bg-secondary">Cancelled</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($b['status'] === 'Confirmed'): ?>
                <a href="booking_cancel.php?id=<?php echo (int)$b['booking_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this booking?');">Cancel</a>
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

<?php require_once 'includes/admin_footer.php'; ?>
