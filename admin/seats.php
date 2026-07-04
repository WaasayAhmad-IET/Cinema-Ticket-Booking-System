<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdminLogin();

$screens = mysqli_query($conn, "SELECT * FROM Screen ORDER BY screen_name");
$screen_id = isset($_GET['screen_id']) ? (int)$_GET['screen_id'] : 0;

$errors = [];
$success = '';

// Add single seat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_seat'])) {
    $sid = (int)$_POST['screen_id'];
    $row = strtoupper(trim($_POST['seat_row']));
    $num = (int)$_POST['seat_number'];
    $type = in_array($_POST['seat_type'], ['Standard','Premium','VIP']) ? $_POST['seat_type'] : 'Standard';
    $mult = (float)$_POST['price_multiplier'];

    if ($row === '' || $num <= 0) {
        $errors[] = "Please provide a valid row letter and seat number.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO Seat (screen_id, seat_row, seat_number, seat_type, price_multiplier) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isisd", $sid, $row, $num, $type, $mult);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = "Seat $row$num added.";
        } else {
            $_SESSION['flash_error'] = "Could not add seat (it may already exist).";
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: seats.php?screen_id=$sid");
    exit();
}

// Bulk generate rows of seats
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_generate'])) {
    $sid = (int)$_POST['screen_id'];
    $rowsCount = (int)$_POST['rows_count'];
    $seatsPerRow = (int)$_POST['seats_per_row'];
    $type = in_array($_POST['bulk_seat_type'], ['Standard','Premium','VIP']) ? $_POST['bulk_seat_type'] : 'Standard';
    $mult = (float)$_POST['bulk_price_multiplier'];

    $added = 0;
    for ($r = 0; $r < $rowsCount && $r < 26; $r++) {
        $rowLetter = chr(65 + $r);
        for ($n = 1; $n <= $seatsPerRow; $n++) {
            $stmt = mysqli_prepare($conn, "INSERT IGNORE INTO Seat (screen_id, seat_row, seat_number, seat_type, price_multiplier) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isisd", $sid, $rowLetter, $n, $type, $mult);
            if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) $added++;
            mysqli_stmt_close($stmt);
        }
    }
    $_SESSION['flash_success'] = "$added seats generated.";
    header("Location: seats.php?screen_id=$sid");
    exit();
}

$pageTitle = 'Manage Seats';
require_once 'includes/admin_header.php';

$seats = [];
if ($screen_id) {
    $sStmt = mysqli_prepare($conn, "SELECT * FROM Seat WHERE screen_id = ? ORDER BY seat_row, seat_number");
    mysqli_stmt_bind_param($sStmt, "i", $screen_id);
    mysqli_stmt_execute($sStmt);
    $seats = mysqli_stmt_get_result($sStmt);
}
?>

<div class="row">
  <div class="col-md-4 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold">Select Screen</h6>
        <form method="GET" action="seats.php">
          <select name="screen_id" class="form-select mb-2" onchange="this.form.submit()">
            <option value="">-- Choose a screen --</option>
            <?php mysqli_data_seek($screens, 0); while ($sc = mysqli_fetch_assoc($screens)): ?>
              <option value="<?php echo (int)$sc['screen_id']; ?>" <?php echo $screen_id === (int)$sc['screen_id'] ? 'selected' : ''; ?>>
                <?php echo h($sc['screen_name']); ?> (<?php echo h($sc['screen_type']); ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </form>
      </div>
    </div>

    <?php if ($screen_id): ?>
    <div class="card border-0 shadow-sm mt-3">
      <div class="card-body">
        <h6 class="fw-bold">Bulk Generate Seats</h6>
        <form method="POST" action="seats.php">
          <input type="hidden" name="screen_id" value="<?php echo $screen_id; ?>">
          <div class="mb-2">
            <label class="form-label small">Number of Rows</label>
            <input type="number" name="rows_count" class="form-control form-control-sm" value="5" min="1" max="26" required>
          </div>
          <div class="mb-2">
            <label class="form-label small">Seats per Row</label>
            <input type="number" name="seats_per_row" class="form-control form-control-sm" value="10" min="1" required>
          </div>
          <div class="mb-2">
            <label class="form-label small">Seat Type</label>
            <select name="bulk_seat_type" class="form-select form-select-sm">
              <option value="Standard">Standard</option>
              <option value="Premium">Premium</option>
              <option value="VIP">VIP</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label small">Price Multiplier</label>
            <input type="number" step="0.01" name="bulk_price_multiplier" class="form-control form-control-sm" value="1.00" required>
          </div>
          <button type="submit" name="bulk_generate" value="1" class="btn btn-cinema btn-sm w-100">Generate Seats</button>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
      <div class="card-body">
        <h6 class="fw-bold">Add Single Seat</h6>
        <form method="POST" action="seats.php">
          <input type="hidden" name="screen_id" value="<?php echo $screen_id; ?>">
          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="form-label small">Row</label>
              <input type="text" name="seat_row" maxlength="1" class="form-control form-control-sm" required>
            </div>
            <div class="col-6">
              <label class="form-label small">Number</label>
              <input type="number" name="seat_number" class="form-control form-control-sm" required>
            </div>
          </div>
          <div class="mb-2">
            <label class="form-label small">Seat Type</label>
            <select name="seat_type" class="form-select form-select-sm">
              <option value="Standard">Standard</option>
              <option value="Premium">Premium</option>
              <option value="VIP">VIP</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label small">Price Multiplier</label>
            <input type="number" step="0.01" name="price_multiplier" class="form-control form-control-sm" value="1.00" required>
          </div>
          <button type="submit" name="add_seat" value="1" class="btn btn-outline-dark btn-sm w-100">Add Seat</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-md-8">
    <?php if (!$screen_id): ?>
      <div class="alert alert-info">Select a screen on the left to view and manage its seats.</div>
    <?php else: ?>
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Configured Seats</div>
        <div class="table-responsive">
          <table class="table table-cinema mb-0 align-middle">
            <thead><tr><th>Seat</th><th>Type</th><th>Price Multiplier</th><th>Action</th></tr></thead>
            <tbody>
              <?php if (mysqli_num_rows($seats) === 0): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">No seats configured for this screen yet.</td></tr>
              <?php else: ?>
                <?php while ($s = mysqli_fetch_assoc($seats)): ?>
                  <tr>
                    <td><?php echo h($s['seat_row'] . $s['seat_number']); ?></td>
                    <td><?php echo h($s['seat_type']); ?></td>
                    <td><?php echo h($s['price_multiplier']); ?>x</td>
                    <td>
                      <a href="seat_delete.php?seat_id=<?php echo (int)$s['seat_id']; ?>&screen_id=<?php echo $screen_id; ?>"
                         class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this seat?');">
                        <i class="fa-solid fa-trash"></i>
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
