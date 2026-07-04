<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdminLogin();

$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Soft delete to preserve referential integrity with existing Shows/Bookings
$stmt = mysqli_prepare($conn, "UPDATE Movie SET is_active = FALSE WHERE movie_id = ?");
mysqli_stmt_bind_param($stmt, "i", $movie_id);
if (mysqli_stmt_execute($stmt)) {
    $_SESSION['flash_success'] = "Movie deactivated successfully.";
} else {
    $_SESSION['flash_error'] = "Failed to deactivate movie.";
}
mysqli_stmt_close($stmt);

header("Location: movies.php");
exit();
