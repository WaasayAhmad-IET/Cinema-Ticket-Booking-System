<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdminLogin();

$errors = [];
$movies = mysqli_query($conn, "SELECT movie_id, title FROM Movie WHERE is_active = TRUE ORDER BY title");
$screens = mysqli_query($conn, "SELECT screen_id, screen_name, screen_type FROM Screen ORDER BY screen_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = (int)($_POST['movie_id'] ?? 0);
    $screen_id = (int)($_POST['screen_id'] ?? 0);
    $show_date = $_POST['show_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $base_price = (float)($_POST['base_price'] ?? 0);

    if ($movie_id <= 0) $errors[] = "Please select a movie.";
    if ($screen_id <= 0) $errors[] = "Please select a screen.";
    if ($show_date === '') $errors[] = "Show date is required.";
    if ($start_time === '') $errors[] = "Start time is required.";
    if ($base_price <= 0) $errors[] = "Base price must be greater than 0.";

    // Check for screen/time conflicts
    if (empty($errors)) {
        $check = mysqli_prepare($conn, "SELECT COUNT(*) c FROM Shows WHERE screen_id=? AND show_date=? AND start_time=? AND is_active=TRUE");
        mysqli_stmt_bind_param($check, "iss", $screen_id, $show_date, $start_time);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->fetch_assoc()['c'] > 0) {
            $errors[] = "This screen already has a show scheduled at that date/time.";
        }
        mysqli_stmt_close($check);
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO Shows (movie_id, screen_id, show_date, start_time, base_price) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iissd", $movie_id, $screen_id, $show_date, $start_time, $base_price);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = "Show scheduled successfully.";
            header("Location: shows.php");
            exit();
        } else {
            $errors[] = "Failed to schedule show.";
        }
        mysqli_stmt_close($stmt);
    }
}

$pageTitle = 'Add Show';
require_once 'includes/admin_header.php';
?>

<div class="card border-0 shadow-sm" style="max-width:560px;">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-3">Schedule New Show</h5>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo "<li>" . h($e) . "</li>"; ?></ul></div>
    <?php endif; ?>

    <form method="POST" action="show_add.php">
      <div class="mb-3">
        <label class="form-label">Movie</label>
        <select name="movie_id" class="form-select" required>
          <option value="">-- Select Movie --</option>
          <?php mysqli_data_seek($movies, 0); while ($m = mysqli_fetch_assoc($movies)): ?>
            <option value="<?php echo (int)$m['movie_id']; ?>"><?php echo h($m['title']); ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Screen</label>
        <select name="screen_id" class="form-select" required>
          <option value="">-- Select Screen --</option>
          <?php mysqli_data_seek($screens, 0); while ($s = mysqli_fetch_assoc($screens)): ?>
            <option value="<?php echo (int)$s['screen_id']; ?>"><?php echo h($s['screen_name']); ?> (<?php echo h($s['screen_type']); ?>)</option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Show Date</label>
          <input type="date" name="show_date" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Start Time</label>
          <input type="time" name="start_time" class="form-control" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Base Price (Rs.)</label>
        <input type="number" step="0.01" name="base_price" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-cinema">Schedule Show</button>
      <a href="shows.php" class="btn btn-outline-secondary">Cancel</a>
    </form>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
