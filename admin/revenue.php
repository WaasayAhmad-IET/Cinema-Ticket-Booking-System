<?php
$pageTitle = 'Revenue Report';
require_once 'includes/admin_header.php';

$revenue = mysqli_query($conn, "SELECT * FROM RevenueReport ORDER BY show_date DESC");
$totalRevenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount),0) r FROM Payment WHERE status='Success'"))['r'];

// Revenue by genre for a quick breakdown
$byGenre = mysqli_query($conn, "
    SELECT m.genre, SUM(p.amount) AS revenue
    FROM Payment p
    JOIN Booking b ON p.booking_id = b.booking_id
    JOIN Shows sh ON b.show_id = sh.show_id
    JOIN Movie m ON sh.movie_id = m.movie_id
    WHERE p.status = 'Success'
    GROUP BY m.genre
    ORDER BY revenue DESC
");
?>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="admin-stat-card stat-4">
      <div class="small text-uppercase opacity-75">Total Revenue</div>
      <div class="fs-2 fw-bold">Rs. <?php echo number_format($totalRevenue, 2); ?></div>
      <i class="fa-solid fa-sack-dollar"></i>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-md-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-bold">Revenue by Movie &amp; Show Date</div>
      <div class="table-responsive">
        <table class="table table-cinema mb-0 align-middle">
          <thead><tr><th>Movie</th><th>Show Date</th><th class="text-end">Revenue</th></tr></thead>
          <tbody>
          <?php if (mysqli_num_rows($revenue) === 0): ?>
            <tr><td colspan="3" class="text-center text-muted py-3">No revenue recorded yet.</td></tr>
          <?php else: ?>
            <?php while ($r = mysqli_fetch_assoc($revenue)): ?>
              <tr>
                <td><?php echo h($r['title']); ?></td>
                <td><?php echo date('M j, Y', strtotime($r['show_date'])); ?></td>
                <td class="text-end">Rs. <?php echo number_format($r['total_revenue'], 2); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-bold">Revenue by Genre</div>
      <div class="table-responsive">
        <table class="table table-cinema mb-0 align-middle">
          <thead><tr><th>Genre</th><th class="text-end">Revenue</th></tr></thead>
          <tbody>
          <?php if (mysqli_num_rows($byGenre) === 0): ?>
            <tr><td colspan="2" class="text-center text-muted py-3">No data yet.</td></tr>
          <?php else: ?>
            <?php while ($g = mysqli_fetch_assoc($byGenre)): ?>
              <tr>
                <td><?php echo h($g['genre']); ?></td>
                <td class="text-end">Rs. <?php echo number_format($g['revenue'], 2); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
