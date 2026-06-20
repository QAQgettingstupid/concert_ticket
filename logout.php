<?php
session_start();
// 1. 清除所有 Session 變數
$_SESSION = []; 

// 2. 刪除伺服器上的 Session 檔案
session_destroy();

// 3. 跳轉回首頁
header("Location: home.php");
exit;
?>