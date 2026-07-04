<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM Movie WHERE movie_id = ? AND is_active = TRUE");
mysqli_stmt_bind_param($stmt, "i", $movie_id);
mysqli_stmt_execute($stmt);
$movie = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);

if (!$movie) {
    $_SESSION['flash_error'] = "Movie not found.";
    header("Location: movies.php");
    exit();
}

$pageTitle = $movie['title'];

$showStmt = mysqli_prepare($conn, "
    SELECT sh.show_id, sh.show_date, sh.start_time, sh.base_price, sc.screen_name, sc.screen_type
    FROM Shows sh
    JOIN Screen sc ON sh.screen_id = sc.screen_id
    WHERE sh.movie_id = ? AND sh.is_active = TRUE AND CONCAT(sh.show_date,' ',sh.start_time) >= NOW()
    ORDER BY sh.show_date, sh.start_time
");
mysqli_stmt_bind_param($showStmt, "i", $movie_id);
mysqli_stmt_execute($showStmt);
$shows = mysqli_stmt_get_result($showStmt);

include 'includes/header.php';
?>

<div class="container py-4">
  <a href="movies.php" class="text-decoration-none">&larr; Back to Movies</a>

  <div class="row mt-3 g-4">
    <div class="col-md-4">
      <div class="movie-poster rounded-3" style="height:380px; font-size:5rem;"><i class="fa-solid fa-clapperboard"></i></div>
    </div>
    <div class="col-md-8">
      <h2 class="fw-bold"><?php echo h($movie['title']); ?></h2>
      <span class="badge badge-genre mb-2"><?php echo h($movie['genre']); ?></span>
      <p class="rating mb-2"><i class="fa-solid fa-star"></i> <?php echo h($movie['rating']); ?> / 10</p>
      <ul class="list-unstyled text-muted">
        <li><strong>Director:</strong> <?php echo h($movie['director']); ?></li>
        <li><strong>Duration:</strong> <?php echo intval($movie['duration_minutes']); ?> minutes</li>
        <li><strong>Release Date:</strong> <?php echo date('F j, Y', strtotime($movie['release_date'])); ?></li>
      </ul>

      <h5 class="fw-bold mt-4 mb-3">Select a Showtime</h5>
      <?php if (mysqli_num_rows($shows) === 0): ?>
        <div class="alert alert-info">No upcoming showtimes for this movie.</div>
      <?php else: ?>
        <div class="row g-3">
          <?php while ($s = mysqli_fetch_assoc($shows)): ?>
            <div class="col-sm-6 col-lg-4">
              <a href="seats.php?show_id=<?php echo (int)$s['show_id']; ?>" class="text-decoration-none">
                <div class="card p-3 h-100 border-0 shadow-sm">
                  <div class="fw-bold"><?php echo date('D, M j', strtotime($s['show_date'])); ?></div>
                  <div class="text-muted"><?php echo date('g:i A', strtotime($s['start_time'])); ?></div>
                  <div class="small mt-1"><i class="fa-solid fa-tv me-1"></i><?php echo h($s['screen_name']); ?> (<?php echo h($s['screen_type']); ?>)</div>
                  <div class="fw-bold text-end mt-2" style="color:var(--cinema-red)">Rs. <?php echo number_format($s['base_price'], 2); ?></div>
                </div>
              </a>
            </div>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
