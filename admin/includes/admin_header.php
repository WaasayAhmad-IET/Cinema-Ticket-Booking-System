<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdminLogin();
$current = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? h($pageTitle) . ' - Admin' : 'Admin Panel'; ?></title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
  <div class="admin-sidebar" style="width:240px;">
    <div class="p-3 border-bottom border-secondary border-opacity-25">
      <h5 class="text-warning fw-bold mb-0"><i class="fa-solid fa-film me-2"></i>CTBS Admin</h5>
    </div>
    <a href="dashboard.php" class="<?php echo $current === 'dashboard.php' ? 'active' : ''; ?>"><i class="fa-solid fa-gauge me-2"></i>Dashboard</a>
    <a href="movies.php" class="<?php echo in_array($current, ['movies.php','movie_add.php','movie_edit.php']) ? 'active' : ''; ?>"><i class="fa-solid fa-clapperboard me-2"></i>Movies</a>
    <a href="screens.php" class="<?php echo in_array($current, ['screens.php','screen_add.php','screen_edit.php']) ? 'active' : ''; ?>"><i class="fa-solid fa-tv me-2"></i>Screens</a>
    <a href="seats.php" class="<?php echo $current === 'seats.php' ? 'active' : ''; ?>"><i class="fa-solid fa-chair me-2"></i>Seats</a>
    <a href="shows.php" class="<?php echo in_array($current, ['shows.php','show_add.php','show_edit.php']) ? 'active' : ''; ?>"><i class="fa-solid fa-calendar-days me-2"></i>Shows</a>
    <a href="bookings.php" class="<?php echo $current === 'bookings.php' ? 'active' : ''; ?>"><i class="fa-solid fa-ticket me-2"></i>Bookings</a>
    <a href="customers.php" class="<?php echo $current === 'customers.php' ? 'active' : ''; ?>"><i class="fa-solid fa-users me-2"></i>Customers</a>
    <a href="revenue.php" class="<?php echo $current === 'revenue.php' ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line me-2"></i>Revenue Report</a>
    <a href="logout.php" class="mt-3"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
  </div>

  <div class="flex-grow-1">
    <nav class="navbar navbar-light bg-white border-bottom px-4">
      <span class="fw-semibold"><?php echo isset($pageTitle) ? h($pageTitle) : 'Dashboard'; ?></span>
      <span class="text-muted"><i class="fa-solid fa-user-shield me-1"></i><?php echo h($_SESSION['admin_username'] ?? 'Admin'); ?></span>
    </nav>
    <div class="p-4">

    <?php if (isset($_SESSION['flash_success'])): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <?php echo h($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <?php echo h($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
