<?php
// 💡 記得一定要在最頂端啟動 Session，這樣才能在註冊成功時直接寫入登入狀態
session_start(); 
require 'db_connect.php';

$message = "";
$is_success = false;
$ssn_is_registered = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['identity_id'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    try {
        $sql = "INSERT INTO Users (identity_id, password_hash, name, email, phone) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $password, $name, $email, $phone]);
        
        // --- 💡 【核心修改點 1】：註冊成功，直接塞入 Session 視同登入 ---
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;

        // --- 💡 【核心修改點 2】：檢查有沒有中斷的來源網址，有的話直接跳轉 ---
        $redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : '';
        
        if (!empty($redirect_url)) {
            header("Location: " . $redirect_url);
        } else {
            header("Location: home.php"); // 如果是一般正常註冊，就去首頁
        }
        exit; // 直接中斷，不走下面的 HTML 提示畫面

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $message = "註冊失敗：該身分證字號已註冊過。";
            $ssn_is_registered = true;
        } else {
            $message = "註冊失敗：" . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>註冊結果</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f7f6; }
        .result-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; }
    </style>
    <?php if ($ssn_is_registered): ?>
        <meta http-equiv="refresh" content="3;url=register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
    <?php endif; ?>
</head>
<body>
    <div class="result-card">
        <h2>⚠️ 註冊提示</h2>
        <p><?php echo $message; ?></p>
        <a href="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">返回重新註冊</a>
    </div>
</body>
</html>