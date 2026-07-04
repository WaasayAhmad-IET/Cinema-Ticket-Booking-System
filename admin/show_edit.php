<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdminLogin();

$show_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

$stmt = mysqli_prepare($conn, "SELECT * FROM Shows WHERE show_id = ?");
mysqli_stmt_bind_param($stmt, "i", $show_id);
mysqli_stmt_execute($stmt);
$show = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);

if (!$show) {
    $_SESSION['flash_error'] = "Show not found.";
    header("Location: shows.php");
    exit();
}

$movies = mysqli_query($conn, "SELECT movie_id, title FROM Movie ORDER BY title");
$screens = mysqli_query($conn, "SELECT screen_id, screen_name, screen_type FROM Screen ORDER BY screen_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = (int)($_POST['movie_id'] ?? 0);
    $screen_id = (int)($_POST['screen_id'] ?? 0);
    $show_date = $_POST['show_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $base_price = (float)($_POST['base_price'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($movie_id <= 0 || $screen_id <= 0 || $show_date === '' || $start_time === '' || $base_price <= 0) {
        $errors[] = "Please fill in all required fields correctly.";
    }

    if (empty($errors)) {
        $upd = mysqli_prepare($conn, "UPDATE Shows SET movie_id=?, screen_id=?, show_date=?, start_time=?, base_price=?, is_active=? WHERE show_id=?");
        mysqli_stmt_bind_param($upd, "iissdii", $movie_id, $screen_id, $show_date, $start_time, $base_price, $is_active, $show_id);
        if (mysqli_stmt_execute($upd)) {
            $_SESSION['flash_success'] = "Show updated successfully.";
            header("Location: shows.php");
            exit();
        } else {
            $errors[] = "Failed to update show.";
        }
        mysqli_stmt_close($upd);
    }
    $show = array_merge($show, $_POST);
}

$pageTitle = 'Edit Show';
require_once 'includes/admin_header.php';
?>

<div class="card border-0 shadow-sm" style="max-width:560px;">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-3">Edit Show</h5>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo "<li>" . h($e) . "</li>"; ?></ul></div>
    <?php endif; ?>

    <form method="POST" action="show_edit.php?id=<?php echo (int)$show_id; ?>">
      <div class="mb-3">
        <label class="form-label">Movie</label>
        <select name="movie_id" class="form-select" required>
          <?php mysqli_data_seek($movies, 0); while ($m = mysqli_fetch_assoc($movies)): ?>
            <option value="<?php echo (int)$m['movie_id']; ?>" <?php echo $show['movie_id'] == $m['movie_id'] ? 'selected' : ''; ?>><?php echo h($m['title']); ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Screen</label>
        <select name="screen_id" class="form-select" required>
          <?php mysqli_data_seek($screens, 0); while ($s = mysqli_fetch_assoc($screens)): ?>
            <option value="<?php echo (int)$s['screen_id']; ?>" <?php echo $show['screen_id'] == $s['screen_id'] ? 'selected' : ''; ?>><?php echo h($s['screen_name']); ?> (<?php echo h($s['screen_type']); ?>)</option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Show Date</label>
          <input type="date" name="show_date" class="form-control" required value="<?php echo h($show['show_date']); ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Start Time</label>
          <input type="time" name="start_time" class="form-control" required value="<?php echo h(substr($show['start_time'],0,5)); ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Base Price (Rs.)</label>
        <input type="number" step="0.01" name="base_price" class="form-control" required value="<?php echo h($show['base_price']); ?>">
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo $show['is_active'] ? 'checked' : ''; ?>>
        <label class="form-check-label" for="is_active">Active (bookable by customers)</label>
      </div>
      <button type="submit" class="btn btn-cinema">Update Show</button>
      <a href="shows.php" class="btn btn-outline-secondary">Cancel</a>
    </form>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
