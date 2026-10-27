<?php
/**
 * SOBHA REALTY LUXURY CRM - ADMIN AUTHENTICATION CHECK
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['sobha_admin_logged']) || $_SESSION['sobha_admin_logged'] !== true) {
    header('Location: login.php');
    exit;
}
