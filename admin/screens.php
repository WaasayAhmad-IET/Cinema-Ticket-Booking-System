<?php
$pageTitle = 'Manage Screens';
require_once 'includes/admin_header.php';

$screens = mysqli_query($conn, "
    SELECT sc.*, COUNT(s.seat_id) AS seat_count
    FROM Screen sc
    LEFT JOIN Seat s ON s.screen_id = sc.screen_id
    GROUP BY sc.screen_id
    ORDER BY sc.screen_id
");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-bold mb-0">All Screens</h5>
  <a href="screen_add.php" class="btn btn-cinema"><i class="fa-solid fa-plus me-1"></i>Add Screen</a>
</div>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-cinema mb-0 align-middle">
      <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Capacity</th><th>Seats Configured</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($screens) === 0): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">No screens added yet.</td></tr>
      <?php else: ?>
        <?php while ($s = mysqli_fetch_assoc($screens)): ?>
          <tr>
            <td><?php echo (int)$s['screen_id']; ?></td>
            <td><?php echo h($s['screen_name']); ?></td>
            <td><span class="badge bg-dark"><?php echo h($s['screen_type']); ?></span></td>
            <td><?php echo (int)$s['capacity']; ?></td>
            <td><?php echo (int)$s['seat_count']; ?></td>
            <td>
              <a href="screen_edit.php?id=<?php echo (int)$s['screen_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
              <a href="seats.php?screen_id=<?php echo (int)$s['screen_id']; ?>" class="btn btn-sm btn-outline-dark"><i class="fa-solid fa-chair"></i> Seats</a>
              <a href="screen_delete.php?id=<?php echo (int)$s['screen_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this screen? This cannot be undone.');"><i class="fa-solid fa-trash"></i></a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
