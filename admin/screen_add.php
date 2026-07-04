<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdminLogin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $screen_name = trim($_POST['screen_name'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);
    $screen_type = in_array($_POST['screen_type'] ?? '', ['Standard','IMAX','VIP']) ? $_POST['screen_type'] : 'Standard';

    if ($screen_name === '') $errors[] = "Screen name is required.";
    if ($capacity <= 0) $errors[] = "Capacity must be greater than 0.";

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO Screen (screen_name, capacity, screen_type) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sis", $screen_name, $capacity, $screen_type);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = "Screen added successfully. Now add seats to it.";
            header("Location: screens.php");
            exit();
        } else {
            $errors[] = "Failed to add screen.";
        }
        mysqli_stmt_close($stmt);
    }
}

$pageTitle = 'Add Screen';
require_once 'includes/admin_header.php';
?>

<div class="card border-0 shadow-sm" style="max-width:520px;">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-3">Add New Screen</h5>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo "<li>" . h($e) . "</li>"; ?></ul></div>
    <?php endif; ?>

    <form method="POST" action="screen_add.php">
      <div class="mb-3">
        <label class="form-label">Screen Name</label>
        <input type="text" name="screen_name" class="form-control" required placeholder="e.g. Screen 4" value="<?php echo h($_POST['screen_name'] ?? ''); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Capacity</label>
        <input type="number" name="capacity" class="form-control" required value="<?php echo h($_POST['capacity'] ?? ''); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Screen Type</label>
        <select name="screen_type" class="form-select">
          <option value="Standard">Standard</option>
          <option value="IMAX">IMAX</option>
          <option value="VIP">VIP</option>
        </select>
      </div>
      <button type="submit" class="btn btn-cinema">Save Screen</button>
      <a href="screens.php" class="btn btn-outline-secondary">Cancel</a>
    </form>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
