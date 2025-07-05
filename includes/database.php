<?php
/**
 * 資料庫連接和基本操作模組
 */

require_once __DIR__ . '/../config.php';

/**
 * 獲取資料庫連接
 * @return PDO 資料庫連接物件
 */
function getDbConnection() {
    $host = DB_HOST;
    $user = DB_USER;
    $password = DB_PASS;
    $database = DB_NAME;
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec("SET NAMES utf8mb4");
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

/**
 * 執行查詢並返回結果
 * @param PDO $conn 資料庫連接
 * @param string $sql SQL 語句
 * @param array $params 參數陣列
 * @return array 查詢結果
 */
function executeQuery($conn, $sql, $params = []) {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        return [];
    }
}

/**
 * 執行插入、更新或刪除操作
 * @param PDO $conn 資料庫連接
 * @param string $sql SQL 語句
 * @param array $params 參數陣列
 * @return bool 操作是否成功
 */
function executeNonQuery($conn, $sql, $params = []) {
    try {
        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Database operation error: " . $e->getMessage());
        return false;
    }
}
?> 