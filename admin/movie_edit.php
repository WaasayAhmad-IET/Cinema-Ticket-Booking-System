<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdminLogin();

$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

$stmt = mysqli_prepare($conn, "SELECT * FROM Movie WHERE movie_id = ?");
mysqli_stmt_bind_param($stmt, "i", $movie_id);
mysqli_stmt_execute($stmt);
$movie = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);

if (!$movie) {
    $_SESSION['flash_error'] = "Movie not found.";
    header("Location: movies.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $duration = (int)($_POST['duration_minutes'] ?? 0);
    $release_date = $_POST['release_date'] ?? '';
    $director = trim($_POST['director'] ?? '');
    $rating = (float)($_POST['rating'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($title === '') $errors[] = "Title is required.";
    if ($duration <= 0) $errors[] = "Duration must be greater than 0.";

    if (empty($errors)) {
        $upd = mysqli_prepare($conn, "UPDATE Movie SET title=?, genre=?, duration_minutes=?, release_date=?, director=?, rating=?, is_active=? WHERE movie_id=?");
        mysqli_stmt_bind_param($upd, "ssissdii", $title, $genre, $duration, $release_date, $director, $rating, $is_active, $movie_id);
        if (mysqli_stmt_execute($upd)) {
            $_SESSION['flash_success'] = "Movie updated successfully.";
            header("Location: movies.php");
            exit();
        } else {
            $errors[] = "Failed to update movie.";
        }
        mysqli_stmt_close($upd);
    }
    $movie = array_merge($movie, $_POST);
}

$pageTitle = 'Edit Movie';
require_once 'includes/admin_header.php';
?>

<div class="card border-0 shadow-sm" style="max-width:640px;">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-3">Edit Movie</h5>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo "<li>" . h($e) . "</li>"; ?></ul></div>
    <?php endif; ?>

    <form method="POST" action="movie_edit.php?id=<?php echo (int)$movie_id; ?>">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required value="<?php echo h($movie['title']); ?>">
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Genre</label>
          <input type="text" name="genre" class="form-control" required value="<?php echo h($movie['genre']); ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Duration (minutes)</label>
          <input type="number" name="duration_minutes" class="form-control" required value="<?php echo h($movie['duration_minutes']); ?>">
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Release Date</label>
          <input type="date" name="release_date" class="form-control" required value="<?php echo h($movie['release_date']); ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Rating (0-10)</label>
          <input type="number" step="0.1" min="0" max="10" name="rating" class="form-control" value="<?php echo h($movie['rating']); ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Director</label>
        <input type="text" name="director" class="form-control" value="<?php echo h($movie['director']); ?>">
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo $movie['is_active'] ? 'checked' : ''; ?>>
        <label class="form-check-label" for="is_active">Active (visible to customers)</label>
      </div>
      <button type="submit" class="btn btn-cinema">Update Movie</button>
      <a href="movies.php" class="btn btn-outline-secondary">Cancel</a>
    </form>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
