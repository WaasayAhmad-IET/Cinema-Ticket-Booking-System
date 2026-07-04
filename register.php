<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Register';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    if ($first_name === '' || $last_name === '') $errors[] = "First and last name are required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if (strlen($phone) < 7) $errors[] = "Please enter a valid phone number.";
    if (strlen($password) < 3) $errors[] = "Password must be at least 3 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $checkStmt = mysqli_prepare($conn, "SELECT customer_id FROM Customer WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $errors[] = "An account with this email already exists.";
        }
        mysqli_stmt_close($checkStmt);
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO Customer (first_name, last_name, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssss", $first_name, $last_name, $email, $phone, $hash);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = "Registration successful! Please log in.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

include 'includes/header.php';
?>

<div class="container">
  <div class="card auth-card">
    <div class="card-header"><i class="fa-solid fa-user-plus me-2"></i>Create an Account</div>
    <div class="card-body p-4">

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?><li><?php echo h($e); ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" action="register.php" novalidate>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" required value="<?php echo h($_POST['first_name'] ?? ''); ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" required value="<?php echo h($_POST['last_name'] ?? ''); ?>">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required value="<?php echo h($_POST['email'] ?? ''); ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Phone Number</label>
          <input type="text" name="phone" class="form-control" required value="<?php echo h($_POST['phone'] ?? ''); ?>">
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required minlength="3">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="3">
          </div>
        </div>
        <button type="submit" class="btn btn-cinema w-100 mt-2">Register</button>
      </form>

      <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Log in</a></p>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
