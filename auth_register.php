<?php
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
        
        $message = "註冊成功！系統將在 3 秒後自動跳轉至登入頁面...";
        $is_success = true;
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
    <?php if ($is_success): ?>
        <meta http-equiv="refresh" content="3;url=login.php">
    <?php elseif ($ssn_is_registered): ?>
        <meta http-equiv="refresh" content="3;url=register.php">
    <?php endif; ?>
</head>
<body>
    <div class="result-card">
        <h2><?php echo $is_success ? "🎉 恭喜！" : "⚠️ 註冊提示"; ?></h2>
        <p><?php echo $message; ?></p>
        <a href="login.php">若沒自動跳轉，請點此登入</a>
    </div>
</body>
</html>