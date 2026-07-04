<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Movies';

$genre = isset($_GET['genre']) && $_GET['genre'] !== '' ? $_GET['genre'] : null;
$date  = isset($_GET['date'])  && $_GET['date']  !== '' ? $_GET['date']  : null;

// Get distinct genres for filter dropdown
$genreList = [];
$gRes = mysqli_query($conn, "SELECT DISTINCT genre FROM Movie WHERE is_active = TRUE ORDER BY genre");
while ($g = mysqli_fetch_assoc($gRes)) { $genreList[] = $g['genre']; }

// Use the GetAvailableMovies stored procedure
$stmt = mysqli_prepare($conn, "CALL GetAvailableMovies(?, ?)");
mysqli_stmt_bind_param($stmt, "ss", $genre, $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Group rows by movie so each movie card lists its showtimes
$movies = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $mid = $row['movie_id'];
        if (!isset($movies[$mid])) {
            $movies[$mid] = [
                'title' => $row['title'],
                'genre' => $row['genre'],
                'shows' => []
            ];
        }
        $movies[$mid]['shows'][] = $row;
    }
}
mysqli_stmt_close($stmt);
// Clear any extra result sets left by the CALL
while (mysqli_more_results($conn)) { mysqli_next_result($conn); }

include 'includes/header.php';
?>

<div class="container py-4">
  <h3 class="fw-bold mb-4"><i class="fa-solid fa-clapperboard me-2"></i>Now Showing</h3>

  <form method="GET" class="row g-2 mb-4">
    <div class="col-sm-4">
      <select name="genre" class="form-select">
        <option value="">All Genres</option>
        <?php foreach ($genreList as $g): ?>
          <option value="<?php echo h($g); ?>" <?php echo ($genre === $g) ? 'selected' : ''; ?>><?php echo h($g); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-4">
      <input type="date" name="date" class="form-control" value="<?php echo h($date ?? ''); ?>">
    </div>
    <div class="col-sm-3">
      <button class="btn btn-cinema w-100" type="submit"><i class="fa-solid fa-filter me-1"></i>Filter</button>
    </div>
    <div class="col-sm-1">
      <a href="movies.php" class="btn btn-outline-secondary w-100" title="Clear"><i class="fa-solid fa-xmark"></i></a>
    </div>
  </form>

  <?php if (empty($movies)): ?>
    <div class="alert alert-info">No movies found for the selected filters.</div>
  <?php endif; ?>

  <div class="row g-4">
    <?php foreach ($movies as $movie_id => $m): ?>
      <div class="col-md-6 col-lg-4">
        <div class="movie-card">
          <div class="movie-poster"><i class="fa-solid fa-clapperboard"></i></div>
          <div class="card-body">
            <h5 class="fw-bold mb-1"><?php echo h($m['title']); ?></h5>
            <span class="badge badge-genre mb-2"><?php echo h($m['genre']); ?></span>
            <div class="mt-2 mb-2">
              <small class="text-muted d-block mb-1">Showtimes:</small>
              <?php foreach ($m['shows'] as $s): ?>
                <span class="show-chip" style="cursor:default;">
                  <?php echo date('M j', strtotime($s['show_date'])); ?> &bull; <?php echo date('g:i A', strtotime($s['start_time'])); ?> &bull; <?php echo h($s['screen_name']); ?>
                </span>
              <?php endforeach; ?>
            </div>
            <a href="movie_details.php?id=<?php echo (int)$movie_id; ?>" class="btn btn-cinema btn-sm w-100 mt-2">View Details &amp; Book</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
