<?php
session_start(); 
require 'db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['identity_id'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE identity_id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['identity_id'];
            $_SESSION['user_name'] = $user['name'];
            
            // 💡 【核心修改點】檢查是否有帶轉址來源
            $redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : '';
            
            if (!empty($redirect_url)) {
                // 如果有來源網址，登入成功就直接導回當初的頁面
                header("Location: " . $redirect_url);
            } else {
                // 沒有的話，才導向預設的主頁
                header("Location: home.php");
            }
            exit;
        } else {
            echo "<script>alert('身分證字號或密碼錯誤'); history.back();</script>";
        }
    } catch (PDOException $e) {
        echo "系統錯誤：" . $e->getMessage();
    }
} else {
    header("Location: login.php");
}
?>