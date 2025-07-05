<?php
// api/filter_by_month.php
session_start();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/statistics.php';
require_once __DIR__ . '/../includes/currency.php';
header('Content-Type: application/json');

$conn = getDbConnection();
$userId = getCurrentUserId();
$month = isset($_GET['month']) ? $_GET['month'] : '';

// 收入查詢
if ($month && $month !== 'all') {
    $incomeSql = "SELECT * FROM jobs WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ? ORDER BY date DESC";
    $incomeParams = [$userId, $month];
} else {
    $incomeSql = "SELECT * FROM jobs WHERE user_id = ? ORDER BY date DESC";
    $incomeParams = [$userId];
}
$incomes = executeQuery($conn, $incomeSql, $incomeParams);

// 支出查詢
if ($month && $month !== 'all') {
    $expenseSql = "SELECT * FROM expenses WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ? ORDER BY date DESC";
    $expenseParams = [$userId, $month];
} else {
    $expenseSql = "SELECT * FROM expenses WHERE user_id = ? ORDER BY date DESC";
    $expenseParams = [$userId];
}
$expenses = executeQuery($conn, $expenseSql, $expenseParams);

// 圖表統計資料
if ($month && $month !== 'all') {
    // 只統計該月份
    $filter = $month;
    // 依月份查詢
    $incomeByJobName = getIncomeByJobNameByMonth($conn, $userId, $month);
    $expensesByCategory = getExpensesByCategoryByMonth($conn, $userId, $month);
    $monthlyIncome = getMonthlyIncomeByMonth($conn, $userId, $month);
    $monthlyExpenses = getMonthlyExpensesByMonth($conn, $userId, $month);
    $allMonths = [$month];
} else {
    // 全部月份
    $incomeByJobName = getIncomeByJobName($conn, $userId, 'all');
    $expensesByCategory = getExpensesByCategory($conn, $userId, 'all');
    $monthlyIncome = getMonthlyIncome($conn, $userId);
    $monthlyExpenses = getMonthlyExpenses($conn, $userId);
    // 取得所有月份
    $allMonths = [];
    foreach ($monthlyIncome as $income) {
        $allMonths[] = $income['month'];
    }
    foreach ($monthlyExpenses as $expense) {
        if (!in_array($expense['month'], $allMonths)) {
            $allMonths[] = $expense['month'];
        }
    }
    sort($allMonths);
}

// 計算 summary 數值
$totalIncome = 0;
foreach ($incomes as $job) {
    // income 欄位已經是原幣種，需轉換
    $totalIncome += convertToMOP($job['income'], $job['currency']);
}
$totalExpenses = 0;
foreach ($expenses as $expense) {
    $totalExpenses += convertToMOP($expense['amount'], $expense['currency']);
}
$netIncome = $totalIncome - $totalExpenses;

echo json_encode([
    'incomes' => $incomes,
    'expenses' => $expenses,
    'incomeByJobName' => $incomeByJobName,
    'expensesByCategory' => $expensesByCategory,
    'monthlyIncome' => $monthlyIncome,
    'monthlyExpenses' => $monthlyExpenses,
    'allMonths' => $allMonths,
    'totalIncome' => round($totalIncome, 2),
    'totalExpenses' => round($totalExpenses, 2),
    'netIncome' => round($netIncome, 2)
]);

// --- 需要在 includes/income.php/expense.php 新增 getIncomeByJobNameByMonth, getExpensesByCategoryByMonth, getMonthlyIncomeByMonth, getMonthlyExpensesByMonth --- 