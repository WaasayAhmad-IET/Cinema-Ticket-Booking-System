<?php
$pageTitle = 'Manage Shows';
require_once 'includes/admin_header.php';

$shows = mysqli_query($conn, "
    SELECT sh.*, m.title, sc.screen_name
    FROM Shows sh
    JOIN Movie m ON sh.movie_id = m.movie_id
    JOIN Screen sc ON sh.screen_id = sc.screen_id
    ORDER BY sh.show_date DESC, sh.start_time DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-bold mb-0">All Shows</h5>
  <a href="show_add.php" class="btn btn-cinema"><i class="fa-solid fa-plus me-1"></i>Add Show</a>
</div>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-cinema mb-0 align-middle">
      <thead><tr><th>ID</th><th>Movie</th><th>Screen</th><th>Date</th><th>Time</th><th>Base Price</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($shows) === 0): ?>
        <tr><td colspan="8" class="text-center text-muted py-3">No shows scheduled yet.</td></tr>
      <?php else: ?>
        <?php while ($s = mysqli_fetch_assoc($shows)): ?>
          <tr>
            <td><?php echo (int)$s['show_id']; ?></td>
            <td><?php echo h($s['title']); ?></td>
            <td><?php echo h($s['screen_name']); ?></td>
            <td><?php echo h($s['show_date']); ?></td>
            <td><?php echo date('g:i A', strtotime($s['start_time'])); ?></td>
            <td>Rs. <?php echo number_format($s['base_price'], 2); ?></td>
            <td><?php echo $s['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
            <td>
              <a href="show_edit.php?id=<?php echo (int)$s['show_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
              <a href="show_delete.php?id=<?php echo (int)$s['show_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate this show?');"><i class="fa-solid fa-trash"></i></a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
