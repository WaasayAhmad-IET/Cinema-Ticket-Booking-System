<?php
// ============================================
// Session & Auth Helpers
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Customer auth ----
function isCustomerLoggedIn() {
    return isset($_SESSION['customer_id']);
}

function requireCustomerLogin() {
    if (!isCustomerLoggedIn()) {
        header("Location: " . getBasePath() . "login.php");
        exit();
    }
}

// ---- Admin auth ----
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Figures out relative path prefix depending on whether we're inside /admin or /customer
function getBasePath() {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    if (strpos($scriptDir, '/admin') !== false) {
        return '../';
    }
    return '';
}

// Simple helper to sanitize output
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Verifies a password against either a bcrypt hash (new users) or a legacy
// plain-text value (the sample data shipped in Project_Cinema.sql uses
// plain values like '123' and 'admin123' for demo purposes).
function verifyPassword($plainPassword, $storedHash) {
    if (password_verify($plainPassword, $storedHash)) {
        return true;
    }
    // Fallback for demo/sample data that isn't hashed
    return hash_equals($storedHash, $plainPassword);
}
