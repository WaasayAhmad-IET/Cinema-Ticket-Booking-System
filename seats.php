<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireCustomerLogin();

$show_id = isset($_GET['show_id']) ? (int)$_GET['show_id'] : 0;

$stmt = mysqli_prepare($conn, "
    SELECT sh.show_id, sh.show_date, sh.start_time, sh.base_price, sh.screen_id,
           m.title, sc.screen_name, sc.screen_type
    FROM Shows sh
    JOIN Movie m ON sh.movie_id = m.movie_id
    JOIN Screen sc ON sh.screen_id = sc.screen_id
    WHERE sh.show_id = ? AND sh.is_active = TRUE
");
mysqli_stmt_bind_param($stmt, "i", $show_id);
mysqli_stmt_execute($stmt);
$show = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);

if (!$show) {
    $_SESSION['flash_error'] = "Show not found.";
    header("Location: movies.php");
    exit();
}

$pageTitle = "Select Seats - " . $show['title'];

// All seats for this screen
$seatStmt = mysqli_prepare($conn, "SELECT * FROM Seat WHERE screen_id = ? ORDER BY seat_row, seat_number");
mysqli_stmt_bind_param($seatStmt, "i", $show['screen_id']);
mysqli_stmt_execute($seatStmt);
$allSeats = mysqli_stmt_get_result($seatStmt);

$seats = [];
$rows = [];
while ($s = mysqli_fetch_assoc($allSeats)) {
    $seats[] = $s;
    $rows[$s['seat_row']][] = $s;
}
ksort($rows);

// Booked seat IDs for this specific show (Confirmed bookings only)
$bookedIds = [];
$bookedStmt = mysqli_prepare($conn, "
    SELECT sb.seat_id FROM SeatBooking sb
    JOIN Booking b ON sb.booking_id = b.booking_id
    WHERE b.show_id = ? AND b.status = 'Confirmed'
");
mysqli_stmt_bind_param($bookedStmt, "i", $show_id);
mysqli_stmt_execute($bookedStmt);
$bookedResult = mysqli_stmt_get_result($bookedStmt);
while ($b = mysqli_fetch_assoc($bookedResult)) {
    $bookedIds[] = (int)$b['seat_id'];
}

include 'includes/header.php';
?>

<div class="container py-4">
  <a href="javascript:history.back();" class="text-decoration-none">&larr; Back</a>

  <div class="text-center mt-3 mb-4">
    <h3 class="fw-bold mb-1"><?php echo h($show['title']); ?></h3>
    <p class="text-muted mb-0">
      <?php echo date('D, M j, Y', strtotime($show['show_date'])); ?> &bull; <?php echo date('g:i A', strtotime($show['start_time'])); ?>
      &bull; <?php echo h($show['screen_name']); ?> (<?php echo h($show['screen_type']); ?>)
    </p>
  </div>

  <?php if (empty($seats)): ?>
    <div class="alert alert-warning text-center">No seats configured for this screen yet.</div>
  <?php else: ?>

  <div class="screen-label">SCREEN</div>
  <div class="screen-bar"></div>

  <form method="POST" action="book.php" id="bookingForm">
    <input type="hidden" name="show_id" value="<?php echo (int)$show_id; ?>">
    <input type="hidden" name="selected_seats" id="selectedSeatsInput" value="">

    <div class="seat-map mb-4">
      <?php foreach ($rows as $rowLetter => $rowSeats): ?>
        <div class="seat-row">
          <div class="seat-row-label"><?php echo h($rowLetter); ?></div>
          <?php foreach ($rowSeats as $seat):
              $isBooked = in_array((int)$seat['seat_id'], $bookedIds);
              $typeClass = strtolower($seat['seat_type']);
              $price = round($seat['price_multiplier'] * $show['base_price'], 2);
          ?>
            <div class="seat <?php echo $isBooked ? 'booked' : $typeClass; ?>"
                 data-seat-id="<?php echo (int)$seat['seat_id']; ?>"
                 data-price="<?php echo $price; ?>"
                 <?php echo $isBooked ? 'title="Already booked"' : 'title="' . h($seat['seat_type']) . ' - Rs. ' . $price . '"'; ?>>
              <?php echo (int)$seat['seat_number']; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="seat-legend text-center mb-4">
      <span><span class="legend-box" style="background:#fff;"></span> Available</span>
      <span><span class="legend-box" style="background:var(--cinema-gold); border-color:var(--cinema-gold);"></span> Selected</span>
      <span><span class="legend-box" style="background:#d8d6de; border-color:#d8d6de;"></span> Booked</span>
      <span><span class="legend-box" style="border-color:#8e7cc3;"></span> Premium</span>
      <span><span class="legend-box" style="border-color:var(--cinema-red);"></span> VIP</span>
    </div>

    <div class="card mx-auto" style="max-width:420px;">
      <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
          <span>Selected Seats:</span>
          <span id="seatList" class="fw-bold">None</span>
        </div>
        <div class="d-flex justify-content-between mb-3">
          <span>Total Price:</span>
          <span id="totalPrice" class="fw-bold" style="color:var(--cinema-red)">Rs. 0.00</span>
        </div>
        <div class="mb-3">
          <label class="form-label small">Payment Method</label>
          <select name="payment_method" class="form-select">
            <option value="Card">Credit/Debit Card</option>
            <option value="Mobile">Mobile Wallet</option>
            <option value="Cash">Cash at Counter</option>
          </select>
        </div>
        <button type="submit" class="btn btn-cinema w-100" id="confirmBtn" disabled>
          <i class="fa-solid fa-ticket me-1"></i>Confirm Booking
        </button>
      </div>
    </div>
  </form>

  <?php endif; ?>
</div>

<script src="js/seats.js"></script>
<?php include 'includes/footer.php'; ?>
