<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
/**
 * 主控制器 - 收入支出追蹤器
 * 重構版本：將功能模組化，便於維護
 */

// 開啟錯誤報告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 載入必要的模組
require_once 'includes/language.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/crud.php';
require_once 'includes/statistics.php';
require_once 'includes/income.php';
require_once 'includes/expense.php';

// 獲取資料庫連接
$conn = getDbConnection();

// 初始化語言
$langCode = getCurrentLang();
$lang = loadLanguage($langCode);

// 處理認證相關操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'register':
            handleRegister($conn, $lang);
            exit;
        case 'login':
            handleLogin($conn, $lang);
            exit;
        case 'logout':
            handleLogout();
            exit;
        case 'switch_lang':
            // 處理語言切換
            $newLang = $_POST['lang'] ?? 'zh';
            $result = switchLanguage($newLang);
            echo json_encode($result);
            exit;
    }
}

// 處理圖表數據請求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_chart_data' && isLoggedIn()) {
    $userId = getCurrentUserId();
    $filter = $_POST['filter'] ?? 'all';
    $chartData = getChartData($conn, $userId, $filter);
    echo json_encode([
        'status' => 'success',
        'incomeByJobName' => $chartData['incomeByJobName'],
        'expensesByCategory' => $chartData['expensesByCategory']
    ]);
    exit;
}

// 處理數據導出請求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'export_data' && isLoggedIn()) {
    $userId = getCurrentUserId();
    exportData($conn, $userId, $lang);
    exit;
}

// 處理 CRUD 操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isLoggedIn()) {
    $userId = getCurrentUserId();
    
    switch ($_POST['action']) {
        case 'add_job':
            addJob($conn, $userId, $lang);
            break;
        case 'edit_job':
            editJob($conn, $userId, $lang);
            break;
        case 'delete_job':
            deleteJob($conn, $userId, $lang);
            break;
        case 'add_expense':
            addExpense($conn, $userId, $lang);
            break;
        case 'edit_expense':
            editExpense($conn, $userId, $lang);
            break;
        case 'delete_expense':
            deleteExpense($conn, $userId, $lang);
            break;
    }
    exit;
}

// 處理 dashboard 動態資料請求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_dashboard_data' && isLoggedIn()) {
    echo json_encode([
        'status' => 'success',
        'jobs' => [],
        'expenses' => [],
        'chartData' => [
            'incomeByJobName' => [],
            'expensesByCategory' => [],
            'allMonths' => [],
            'monthlyIncome' => [],
            'monthlyExpenses' => []
        ],
        'totalIncome' => 0,
        'totalExpenses' => 0,
        'netIncome' => 0
    ]);
    exit;
}

// 渲染適當的頁面
if (!isLoggedIn()) {
    // 顯示登入/註冊頁面
    include 'views/login.php';
} else {
    // 顯示主應用程式頁面
    include 'views/dashboard.php';
}
?> 