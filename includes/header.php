<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
$base = getBasePath();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? h($pageTitle) . ' - CTBS' : 'Cinema Ticket Booking System'; ?></title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="<?php echo $base; ?>css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark cinema-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo $base; ?>index.php">
      <i class="fa-solid fa-film me-2"></i>CTBS Cinemas
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>movies.php">Movies</a></li>
        <?php if (isCustomerLoggedIn()): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>history.php">My Bookings</a></li>
          <li class="nav-item">
            <span class="nav-link text-warning">
              <i class="fa-solid fa-user me-1"></i><?php echo h($_SESSION['customer_name']); ?>
            </span>
          </li>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm ms-lg-2" href="<?php echo $base; ?>logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>login.php">Login</a></li>
          <li class="nav-item"><a class="btn btn-warning btn-sm ms-lg-2 text-dark fw-semibold" href="<?php echo $base; ?>register.php">Sign Up</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<?php if (isset($_SESSION['flash_success'])): ?>
  <div class="container mt-3">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo h($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
  <div class="container mt-3">
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo h($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
<?php endif; ?>
