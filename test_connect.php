<?php
header('Content-Type: application/json; charset=utf-8');

$host = '127.0.0.1';
$db   = 'company';
$user = 'root';
$pass = 'root'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // 【經典實驗：聯組撈出各部門的平均薪資】
    // 透過 Dno / Dnumber 將 EMPLOYEE 表和 DEPARTMENT 表連起來，並計算平均薪資
    $sql = "SELECT 
                d.Dname AS department_name, 
                COUNT(e.Ssn) AS total_employees,
                ROUND(AVG(e.Salary), 2) AS average_salary
            FROM EMPLOYEE e
            JOIN DEPARTMENT d ON e.Dno = d.Dnumber
            GROUP BY d.Dnumber, d.Dname";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $report = $stmt->fetchAll();
    
    // 吐出 JSON
    echo json_encode([
        'status' => 'success',
        'source' => 'Elmasri COMPANY Database Schema',
        'data' => $report
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); // 加上 PRETTY_PRINT 讓排版更漂亮

} catch (\PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => '撈取失敗，可能表名大小寫有錯，原因：' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}