<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
$pageTitle = 'Login';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT customer_id, first_name, last_name, password_hash, is_active FROM Customer WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $customer = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($customer && verifyPassword($password, $customer['password_hash'])) {
        if (!$customer['is_active']) {
            $error = "Your account has been deactivated. Please contact support.";
        } else {
            $_SESSION['customer_id'] = $customer['customer_id'];
            $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
            header("Location: index.php");
            exit();
        }
    } else {
        $error = "Invalid email or password.";
    }
}

include 'includes/header.php';
?>

<div class="container">
  <div class="card auth-card">
    <div class="card-header"><i class="fa-solid fa-right-to-bracket me-2"></i>Customer Login</div>
    <div class="card-body p-4">

      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" novalidate>
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required value="<?php echo h($_POST['email'] ?? ''); ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-cinema w-100 mt-2">Login</button>
      </form>

      <p class="text-center mt-3 mb-0">Don't have an account? <a href="register.php">Sign up</a></p>
      <p class="text-center text-muted small mt-2">Cinema staff? <a href="admin/login.php">Admin login</a></p>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
