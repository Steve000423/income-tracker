<?php
/**
 * 認證相關函數模組
 */

require_once 'database.php';

/**
 * 處理用戶註冊
 * @param PDO $conn 資料庫連接
 * @param array $lang 語言陣列
 */
function handleRegister($conn, $lang) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => $lang['fill_all_fields']]);
        return;
    }
    
    // 檢查用戶名是否已存在
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => $lang['username_exists']]);
        return;
    }
    
    // 創建新用戶
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    
    if ($stmt->execute([$username, $hashedPassword])) {
        $_SESSION['user_id'] = $conn->lastInsertId();
        $_SESSION['username'] = $username;
        echo json_encode(['status' => 'success', 'message' => $lang['registration_success']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $lang['registration_failed']]);
    }
}

/**
 * 處理用戶登入
 * @param PDO $conn 資料庫連接
 * @param array $lang 語言陣列
 */
function handleLogin($conn, $lang) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => $lang['fill_all_fields']]);
        return;
    }
    
    // 驗證用戶
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        echo json_encode(['status' => 'success', 'message' => $lang['login_success']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $lang['invalid_credentials']]);
    }
}

/**
 * 處理用戶登出
 */
function handleLogout() {
    session_destroy();
    echo json_encode(['status' => 'success', 'message' => '已成功登出']);
}

/**
 * 檢查用戶是否已登入
 * @return bool 是否已登入
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * 獲取當前用戶ID
 * @return int|null 用戶ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * 獲取當前用戶名
 * @return string|null 用戶名
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * 驗證用戶權限
 * @param int $userId 用戶ID
 * @return bool 是否有權限
 */
function validateUserPermission($userId) {
    return isLoggedIn() && getCurrentUserId() == $userId;
}
?> 