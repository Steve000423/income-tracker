<?php
/**
 * CRUD 操作函數模組
 */

require_once 'database.php';
require_once 'auth.php';
require_once 'currency.php';

/**
 * 添加工作記錄
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param array $lang 語言陣列
 */
function addJob($conn, $userId, $lang) {
    if (!validateUserPermission($userId)) {
        echo json_encode(['status' => 'error', 'message' => $lang['unauthorized']]);
        return;
    }
    $jobName = $_POST['jobName'] ?? '';
    $hourlyWage = isset($_POST['hourlyWage']) ? floatval($_POST['hourlyWage']) : 0;
    $hoursWorked = isset($_POST['hoursWorked']) ? floatval($_POST['hoursWorked']) : 0;
    $date = $_POST['date'] ?? date('Y-m-d');
    $currency = $_POST['currency'] ?? 'MOP';
    $notes = $_POST['notes'] ?? '';
    $income = $hourlyWage * $hoursWorked;
    if (empty($jobName) || $hourlyWage <= 0 || $hoursWorked <= 0) {
        echo json_encode(['status' => 'error', 'message' => $lang['fill_required_fields']]);
        return;
    }
    if (!isSupportedCurrency($currency)) {
        echo json_encode(['status' => 'error', 'message' => $lang['invalid_currency']]);
        return;
    }
    // 寫入 job_names
    $stmt = $conn->prepare("INSERT IGNORE INTO job_names (name) VALUES (?)");
    $stmt->execute([$jobName]);
    // 插入 jobs
    $stmt = $conn->prepare("INSERT INTO jobs (user_id, job_name, hourly_wage, hours_worked, income, currency, date, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$userId, $jobName, $hourlyWage, $hoursWorked, $income, $currency, $date, $notes])) {
        echo json_encode(['status' => 'success', 'message' => $lang['job_added']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $lang['job_add_failed']]);
    }
}

/**
 * 編輯工作記錄
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param array $lang 語言陣列
 */
function editJob($conn, $userId, $lang) {
    if (!validateUserPermission($userId)) {
        echo json_encode(['status' => 'error', 'message' => $lang['unauthorized']]);
        return;
    }
    $jobId = $_POST['job_id'] ?? '';
    $jobName = $_POST['jobName'] ?? '';
    $hourlyWage = isset($_POST['hourlyWage']) ? floatval($_POST['hourlyWage']) : 0;
    $hoursWorked = isset($_POST['hoursWorked']) ? floatval($_POST['hoursWorked']) : 0;
    $date = $_POST['date'] ?? '';
    $currency = $_POST['currency'] ?? 'MOP';
    $notes = $_POST['notes'] ?? '';
    $income = $hourlyWage * $hoursWorked;
    if (empty($jobId) || empty($jobName) || $hourlyWage <= 0 || $hoursWorked <= 0) {
        echo json_encode(['status' => 'error', 'message' => $lang['fill_required_fields']]);
        return;
    }
    if (!isSupportedCurrency($currency)) {
        echo json_encode(['status' => 'error', 'message' => $lang['invalid_currency']]);
        return;
    }
    // 寫入 job_names
    $stmt = $conn->prepare("INSERT IGNORE INTO job_names (name) VALUES (?)");
    $stmt->execute([$jobName]);
    // 更新 jobs
    $stmt = $conn->prepare("UPDATE jobs SET job_name = ?, hourly_wage = ?, hours_worked = ?, income = ?, currency = ?, date = ?, notes = ? WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$jobName, $hourlyWage, $hoursWorked, $income, $currency, $date, $notes, $jobId, $userId])) {
        echo json_encode(['status' => 'success', 'message' => $lang['job_updated']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $lang['job_update_failed']]);
    }
}

/**
 * 刪除工作記錄
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param array $lang 語言陣列
 */
function deleteJob($conn, $userId, $lang) {
    if (!validateUserPermission($userId)) {
        echo json_encode(['status' => 'error', 'message' => $lang['unauthorized']]);
        return;
    }
    
    $jobId = $_POST['job_id'] ?? '';
    
    if (empty($jobId)) {
        echo json_encode(['status' => 'error', 'message' => $lang['invalid_request']]);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ? AND user_id = ?");
    
    if ($stmt->execute([$jobId, $userId])) {
        echo json_encode(['status' => 'success', 'message' => $lang['job_deleted']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $lang['job_delete_failed']]);
    }
}

/**
 * 添加支出記錄
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param array $lang 語言陣列
 */
function addExpense($conn, $userId, $lang) {
    if (!validateUserPermission($userId)) {
        echo json_encode(['status' => 'error', 'message' => $lang['unauthorized']]);
        return;
    }
    $category = $_POST['category'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $currency = $_POST['currency'] ?? 'MOP';
    $date = $_POST['date'] ?? date('Y-m-d');
    $notes = $_POST['notes'] ?? '';
    if (empty($category) || empty($amount)) {
        echo json_encode(['status' => 'error', 'message' => $lang['fill_required_fields']]);
        return;
    }
    if (!isSupportedCurrency($currency)) {
        echo json_encode(['status' => 'error', 'message' => $lang['invalid_currency']]);
        return;
    }
    // 寫入 expense_categories
    $stmt = $conn->prepare("INSERT IGNORE INTO expense_categories (name) VALUES (?)");
    $stmt->execute([$category]);
    // 插入 expenses
    $stmt = $conn->prepare("INSERT INTO expenses (user_id, category, amount, currency, date, notes) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$userId, $category, $amount, $currency, $date, $notes])) {
        echo json_encode(['status' => 'success', 'message' => $lang['expense_added']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $lang['expense_add_failed']]);
    }
}

/**
 * 編輯支出記錄
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param array $lang 語言陣列
 */
function editExpense($conn, $userId, $lang) {
    if (!validateUserPermission($userId)) {
        echo json_encode(['status' => 'error', 'message' => $lang['unauthorized']]);
        return;
    }
    $expenseId = $_POST['expense_id'] ?? '';
    $category = $_POST['category'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $currency = $_POST['currency'] ?? 'MOP';
    $date = $_POST['date'] ?? '';
    $notes = $_POST['notes'] ?? '';
    if (empty($expenseId) || empty($category) || empty($amount)) {
        echo json_encode(['status' => 'error', 'message' => $lang['fill_required_fields']]);
        return;
    }
    if (!isSupportedCurrency($currency)) {
        echo json_encode(['status' => 'error', 'message' => $lang['invalid_currency']]);
        return;
    }
    // 寫入 expense_categories
    $stmt = $conn->prepare("INSERT IGNORE INTO expense_categories (name) VALUES (?)");
    $stmt->execute([$category]);
    // 更新 expenses
    $stmt = $conn->prepare("UPDATE expenses SET category = ?, amount = ?, currency = ?, date = ?, notes = ? WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$category, $amount, $currency, $date, $notes, $expenseId, $userId])) {
        echo json_encode(['status' => 'success', 'message' => $lang['expense_updated']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $lang['expense_update_failed']]);
    }
}

/**
 * 刪除支出記錄
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param array $lang 語言陣列
 */
function deleteExpense($conn, $userId, $lang) {
    if (!validateUserPermission($userId)) {
        echo json_encode(['status' => 'error', 'message' => $lang['unauthorized']]);
        return;
    }
    
    $expenseId = $_POST['expense_id'] ?? '';
    
    if (empty($expenseId)) {
        echo json_encode(['status' => 'error', 'message' => $lang['invalid_request']]);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    
    if ($stmt->execute([$expenseId, $userId])) {
        echo json_encode(['status' => 'success', 'message' => $lang['expense_deleted']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $lang['expense_delete_failed']]);
    }
} 