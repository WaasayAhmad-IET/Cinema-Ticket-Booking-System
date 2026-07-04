<?php
$pageTitle = 'Manage Movies';
require_once 'includes/admin_header.php';

$movies = mysqli_query($conn, "SELECT * FROM Movie ORDER BY movie_id DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-bold mb-0">All Movies</h5>
  <a href="movie_add.php" class="btn btn-cinema"><i class="fa-solid fa-plus me-1"></i>Add Movie</a>
</div>

<div class="card border-0 shadow-sm">
  <div class="table-responsive">
    <table class="table table-cinema mb-0 align-middle">
      <thead><tr><th>ID</th><th>Title</th><th>Genre</th><th>Duration</th><th>Release</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (mysqli_num_rows($movies) === 0): ?>
        <tr><td colspan="8" class="text-center text-muted py-3">No movies added yet.</td></tr>
      <?php else: ?>
        <?php while ($m = mysqli_fetch_assoc($movies)): ?>
          <tr>
            <td><?php echo (int)$m['movie_id']; ?></td>
            <td><?php echo h($m['title']); ?></td>
            <td><?php echo h($m['genre']); ?></td>
            <td><?php echo (int)$m['duration_minutes']; ?> min</td>
            <td><?php echo h($m['release_date']); ?></td>
            <td><i class="fa-solid fa-star text-warning"></i> <?php echo h($m['rating']); ?></td>
            <td><?php echo $m['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
            <td>
              <a href="movie_edit.php?id=<?php echo (int)$m['movie_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
              <a href="movie_delete.php?id=<?php echo (int)$m['movie_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete/deactivate this movie?');"><i class="fa-solid fa-trash"></i></a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
