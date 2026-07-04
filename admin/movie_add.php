<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdminLogin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $duration = (int)($_POST['duration_minutes'] ?? 0);
    $release_date = $_POST['release_date'] ?? '';
    $director = trim($_POST['director'] ?? '');
    $rating = (float)($_POST['rating'] ?? 0);

    if ($title === '') $errors[] = "Title is required.";
    if ($genre === '') $errors[] = "Genre is required.";
    if ($duration <= 0) $errors[] = "Duration must be greater than 0.";
    if ($rating < 0 || $rating > 10) $errors[] = "Rating must be between 0 and 10.";

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO Movie (title, genre, duration_minutes, release_date, director, rating) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssissd", $title, $genre, $duration, $release_date, $director, $rating);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = "Movie added successfully.";
            header("Location: movies.php");
            exit();
        } else {
            $errors[] = "Failed to add movie.";
        }
        mysqli_stmt_close($stmt);
    }
}

$pageTitle = 'Add Movie';
require_once 'includes/admin_header.php';
?>

<div class="card border-0 shadow-sm" style="max-width:640px;">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-3">Add New Movie</h5>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo "<li>" . h($e) . "</li>"; ?></ul></div>
    <?php endif; ?>

    <form method="POST" action="movie_add.php">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required value="<?php echo h($_POST['title'] ?? ''); ?>">
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Genre</label>
          <input type="text" name="genre" class="form-control" required value="<?php echo h($_POST['genre'] ?? ''); ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Duration (minutes)</label>
          <input type="number" name="duration_minutes" class="form-control" required value="<?php echo h($_POST['duration_minutes'] ?? ''); ?>">
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Release Date</label>
          <input type="date" name="release_date" class="form-control" required value="<?php echo h($_POST['release_date'] ?? ''); ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Rating (0-10)</label>
          <input type="number" step="0.1" min="0" max="10" name="rating" class="form-control" value="<?php echo h($_POST['rating'] ?? ''); ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Director</label>
        <input type="text" name="director" class="form-control" value="<?php echo h($_POST['director'] ?? ''); ?>">
      </div>
      <button type="submit" class="btn btn-cinema">Save Movie</button>
      <a href="movies.php" class="btn btn-outline-secondary">Cancel</a>
    </form>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
