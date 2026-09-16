<?php
// Shared helpers. Included by every page.
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Escape output to prevent XSS
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Format money in Kenyan shillings
function money($amount) {
    return 'Ksh ' . number_format((float) $amount, 2);
}

// ---- Authentication ----
function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_admin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function require_login() {
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin($prefix = '') {
    if (!is_admin()) {
        header('Location: ' . $prefix . 'login.php');
        exit;
    }
}

// ---- Flash messages (one-time notices across redirects) ----
function flash_set($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function flash_get() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// ---- Cart (session based) ----
function cart_items() {
    return $_SESSION['cart'] ?? []; // [product_id => quantity]
}

function cart_count() {
    $count = 0;
    foreach (cart_items() as $qty) {
        $count += (int) $qty;
    }
    return $count;
}

// ---- Product name validation (shared rule) ----
// Returns an error string, or '' if valid.
function validate_product_name($name) {
    $name = trim($name);
    if ($name === '') {
        return 'Product name is required.';
    }
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
        return 'Product name must be between 2 and 120 characters.';
    }
    if (ctype_digit($name)) {
        return 'Product name cannot be only numbers.';
    }
    if (!preg_match('/[A-Za-z]/', $name)) {
        return 'Product name must contain at least one letter.';
    }
    return '';
}
