<?php
$pageTitle = 'Customers';
require_once 'includes/admin_header.php';

$customers = mysqli_query($conn, "
    SELECT c.customer_id, c.first_name, c.last_name, c.email, c.phone, c.registration_date, c.is_active,
           COUNT(b.booking_id) AS total_bookings
    FROM Customer c
    LEFT JOIN Booking b ON c.customer_id = b.customer_id
    GROUP BY c.customer_id
    ORDER BY c.registration_date DESC
");
?>

<h5 class="fw-bold mb-3">All Customers</h5>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-cinema mb-0 align-middle">
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Registered</th><th>Total Bookings</th><th>Status</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($customers) === 0): ?>
        <tr><td colspan="7" class="text-center text-muted py-3">No customers registered yet.</td></tr>
      <?php else: ?>
        <?php while ($c = mysqli_fetch_assoc($customers)): ?>
          <tr>
            <td><?php echo (int)$c['customer_id']; ?></td>
            <td><?php echo h($c['first_name'] . ' ' . $c['last_name']); ?></td>
            <td><?php echo h($c['email']); ?></td>
            <td><?php echo h($c['phone']); ?></td>
            <td><?php echo date('M j, Y', strtotime($c['registration_date'])); ?></td>
            <td><span class="badge bg-dark"><?php echo (int)$c['total_bookings']; ?></span></td>
            <td><?php echo $c['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
