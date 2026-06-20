<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>會員登入 - 搶票系統</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); width: 350px; }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 25px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-login { width: 100%; background-color: #3498db; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .btn-login:hover { background-color: #2980b9; }
        .register-link { text-align: center; margin-top: 20px; font-size: 0.9rem; color: #7f8c8d; }
        .register-link a { color: #27ae60; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>會員登入</h2>
    <form action="auth_login.php" method="POST">
        <input type="text" name="identity_id" placeholder="身分證字號" required>
        <input type="password" name="password" placeholder="密碼" required>
        <button type="submit" class="btn-login">登入</button>
    </form>
    <div class="register-link">
        還沒有帳號嗎？ <a href="register.php">立即免費註冊</a>
    </div>
</div>

</body>
</html>