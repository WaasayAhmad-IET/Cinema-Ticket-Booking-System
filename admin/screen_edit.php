<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdminLogin();

$screen_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

$stmt = mysqli_prepare($conn, "SELECT * FROM Screen WHERE screen_id = ?");
mysqli_stmt_bind_param($stmt, "i", $screen_id);
mysqli_stmt_execute($stmt);
$screen = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);

if (!$screen) {
    $_SESSION['flash_error'] = "Screen not found.";
    header("Location: screens.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $screen_name = trim($_POST['screen_name'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);
    $screen_type = in_array($_POST['screen_type'] ?? '', ['Standard','IMAX','VIP']) ? $_POST['screen_type'] : 'Standard';

    if ($screen_name === '') $errors[] = "Screen name is required.";

    if (empty($errors)) {
        $upd = mysqli_prepare($conn, "UPDATE Screen SET screen_name=?, capacity=?, screen_type=? WHERE screen_id=?");
        mysqli_stmt_bind_param($upd, "sisi", $screen_name, $capacity, $screen_type, $screen_id);
        if (mysqli_stmt_execute($upd)) {
            $_SESSION['flash_success'] = "Screen updated successfully.";
            header("Location: screens.php");
            exit();
        } else {
            $errors[] = "Failed to update screen.";
        }
        mysqli_stmt_close($upd);
    }
    $screen = array_merge($screen, $_POST);
}

$pageTitle = 'Edit Screen';
require_once 'includes/admin_header.php';
?>

<div class="card border-0 shadow-sm" style="max-width:520px;">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-3">Edit Screen</h5>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo "<li>" . h($e) . "</li>"; ?></ul></div>
    <?php endif; ?>

    <form method="POST" action="screen_edit.php?id=<?php echo (int)$screen_id; ?>">
      <div class="mb-3">
        <label class="form-label">Screen Name</label>
        <input type="text" name="screen_name" class="form-control" required value="<?php echo h($screen['screen_name']); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Capacity</label>
        <input type="number" name="capacity" class="form-control" required value="<?php echo h($screen['capacity']); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Screen Type</label>
        <select name="screen_type" class="form-select">
          <?php foreach (['Standard','IMAX','VIP'] as $t): ?>
            <option value="<?php echo $t; ?>" <?php echo $screen['screen_type'] === $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-cinema">Update Screen</button>
      <a href="screens.php" class="btn btn-outline-secondary">Cancel</a>
    </form>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
