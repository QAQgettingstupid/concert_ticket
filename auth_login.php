<?php
session_start(); // 啟動 Session，這是記錄登入狀態的關鍵
require 'db_connect.php'; // 引入我們定義好的資料庫連線

// 檢查是否有 POST 請求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['identity_id'];
    $password = $_POST['password'];

    try {
        // 1. 根據身分證字號撈取該使用者的資料
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE identity_id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        // 2. 使用 password_verify 比對雜湊後的密碼
        // 即使資料庫存的是加密過的值，password_verify 也能安全地進行比對
        if ($user && password_verify($password, $user['password_hash'])) {
            // 3. 驗證成功，將使用者資訊存入 Session
            $_SESSION['user_id'] = $user['identity_id'];
            $_SESSION['user_name'] = $user['name'];
            
            // 導向至主頁
            echo "這是 home.php，測試是否成功跳轉！";
            header("Location: home.php");
            exit;
        } else {
            // 4. 驗證失敗
            echo "<script>alert('身分證字號或密碼錯誤'); history.back();</script>";
        }
    } catch (PDOException $e) {
        echo "系統錯誤：" . $e->getMessage();
    }
} else {
    // 如果不是 POST 請求，直接導回登入頁
    header("Location: login.php");
}
?>