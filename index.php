<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Home';

// Fetch up to 6 active movies that currently have upcoming shows
$sql = "SELECT DISTINCT m.* FROM Movie m
        JOIN Shows sh ON sh.movie_id = m.movie_id
        WHERE m.is_active = TRUE AND sh.is_active = TRUE
        ORDER BY m.movie_id DESC LIMIT 6";
$result = mysqli_query($conn, $sql);

include 'includes/header.php';
?>

<section class="hero">
  <div class="container text-center">
    <h1>Book Your Next <span class="text-accent">Movie Night</span></h1>
    <p class="lead mb-4">Browse showtimes, pick your favorite seats, and book tickets in seconds.</p>
    <a href="movies.php" class="btn btn-gold btn-lg px-4"><i class="fa-solid fa-ticket me-2"></i>Browse Movies</a>
  </div>
</section>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">Now Showing</h3>
    <a href="movies.php" class="text-decoration-none">View all &rarr;</a>
  </div>

  <div class="row g-4">
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
      <?php while ($movie = mysqli_fetch_assoc($result)): ?>
        <div class="col-sm-6 col-md-4 col-lg-2">
          <div class="movie-card">
            <div class="movie-poster"><i class="fa-solid fa-clapperboard"></i></div>
            <div class="card-body">
              <h6 class="fw-bold mb-1"><?php echo h($movie['title']); ?></h6>
              <span class="badge badge-genre mb-2"><?php echo h($movie['genre']); ?></span>
              <div class="small text-muted mb-2"><?php echo intval($movie['duration_minutes']); ?> min</div>
              <div class="rating mb-2"><i class="fa-solid fa-star"></i> <?php echo h($movie['rating']); ?></div>
              <a href="movie_details.php?id=<?php echo (int)$movie['movie_id']; ?>" class="btn btn-cinema btn-sm w-100">Book Now</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-muted">No movies currently scheduled. Please check back soon.</p>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
