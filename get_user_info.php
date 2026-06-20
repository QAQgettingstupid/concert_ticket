<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['user_name'])) {
    echo json_encode([
        'is_logged_in' => true,
        'name' => $_SESSION['user_name']
    ]);
} else {
    echo json_encode(['is_logged_in' => false]);
}
?>