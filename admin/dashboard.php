<?php
$pageTitle = 'Dashboard';
require_once 'includes/admin_header.php';

$totalMovies = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM Movie WHERE is_active=TRUE"))['c'];
$totalCustomers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM Customer WHERE is_active=TRUE"))['c'];
$totalBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM Booking WHERE status='Confirmed'"))['c'];
$totalRevenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount),0) r FROM Payment WHERE status='Success'"))['r'];

$recentBookings = mysqli_query($conn, "
    SELECT b.booking_id, c.first_name, c.last_name, m.title, sh.show_date, sh.start_time, b.status
    FROM Booking b
    JOIN Customer c ON b.customer_id = c.customer_id
    JOIN Shows sh ON b.show_id = sh.show_id
    JOIN Movie m ON sh.movie_id = m.movie_id
    ORDER BY b.booking_date DESC LIMIT 8
");
?>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="admin-stat-card stat-1">
      <div class="small text-uppercase opacity-75">Active Movies</div>
      <div class="fs-2 fw-bold"><?php echo (int)$totalMovies; ?></div>
      <i class="fa-solid fa-clapperboard"></i>
    </div>
  </div>
  <div class="col-md-3">
    <div class="admin-stat-card stat-2">
      <div class="small text-uppercase opacity-75">Customers</div>
      <div class="fs-2 fw-bold"><?php echo (int)$totalCustomers; ?></div>
      <i class="fa-solid fa-users"></i>
    </div>
  </div>
  <div class="col-md-3">
    <div class="admin-stat-card stat-3">
      <div class="small text-uppercase opacity-75">Confirmed Bookings</div>
      <div class="fs-2 fw-bold"><?php echo (int)$totalBookings; ?></div>
      <i class="fa-solid fa-ticket"></i>
    </div>
  </div>
  <div class="col-md-3">
    <div class="admin-stat-card stat-4">
      <div class="small text-uppercase opacity-75">Total Revenue</div>
      <div class="fs-2 fw-bold">Rs. <?php echo number_format($totalRevenue, 0); ?></div>
      <i class="fa-solid fa-money-bill-wave"></i>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white fw-bold">Recent Bookings</div>
  <div class="table-responsive">
    <table class="table table-cinema mb-0 align-middle">
      <thead><tr><th>#</th><th>Customer</th><th>Movie</th><th>Show</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (mysqli_num_rows($recentBookings) === 0): ?>
          <tr><td colspan="5" class="text-center text-muted py-3">No bookings yet.</td></tr>
        <?php else: ?>
          <?php while ($r = mysqli_fetch_assoc($recentBookings)): ?>
            <tr>
              <td>#<?php echo (int)$r['booking_id']; ?></td>
              <td><?php echo h($r['first_name'] . ' ' . $r['last_name']); ?></td>
              <td><?php echo h($r['title']); ?></td>
              <td><?php echo date('M j, Y g:i A', strtotime($r['show_date'] . ' ' . $r['start_time'])); ?></td>
              <td>
                <?php if ($r['status'] === 'Confirmed'): ?>
                  <span class="badge bg-success">Confirmed</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Cancelled</span>
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
