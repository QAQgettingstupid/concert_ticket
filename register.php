<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>會員註冊 - 搶票系統</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .register-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 25px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-register { width: 100%; background-color: #27ae60; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .btn-register:hover { background-color: #219150; }
        .login-link { text-align: center; margin-top: 20px; font-size: 0.9rem; color: #7f8c8d; }
    </style>
</head>
<body>

<div class="register-card">
    <h2>立即註冊會員</h2>
    <form action="auth_register.php" method="POST">
        <input type="text" name="identity_id" placeholder="身分證字號 (10碼)" required pattern="[A-Z0-9]{10}" title="請輸入正確的身分證字號格式">
        <input type="password" name="password" placeholder="密碼" required minlength="6">
        <input type="text" name="name" placeholder="姓名" required>
        <input type="email" name="email" placeholder="電子郵件" required>
        <input type="tel" name="phone" placeholder="手機號碼" required pattern="[0-9]{10,}" title="請輸入正確的手機號碼">
        <button type="submit" class="btn-register">完成註冊</button>
    </form>
    <div class="login-link">
        已有帳號？ <a href="login.php">登入搶票</a>
    </div>
</div>

</body>
</html>