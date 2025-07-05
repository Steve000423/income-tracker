<?php
/**
 * 統計分析函數模組
 */

require_once 'income.php';
require_once 'expense.php';

/**
 * 獲取圖表數據
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param string $filter 篩選條件 ('all' 或 'this_month')
 * @return array 圖表數據陣列
 */
function getChartData($conn, $userId, $filter = 'all') {
    $incomeByJobName = getIncomeByJobName($conn, $userId, $filter);
    $expensesByCategory = getExpensesByCategory($conn, $userId, $filter);
    $monthlyIncome = getMonthlyIncome($conn, $userId);
    $monthlyExpenses = getMonthlyExpenses($conn, $userId);
    
    // 獲取所有月份
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
    
    return [
        'incomeByJobName' => $incomeByJobName,
        'expensesByCategory' => $expensesByCategory,
        'monthlyIncome' => $monthlyIncome,
        'monthlyExpenses' => $monthlyExpenses,
        'allMonths' => $allMonths
    ];
}

/**
 * 導出數據為 CSV 格式
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param array $lang 語言陣列
 */
function exportData($conn, $userId, $lang) {
    $jobs = getJobs($conn, $userId);
    $expenses = getExpenses($conn, $userId);
    
    // 設定 CSV 檔案標頭
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="income_expense_data_' . date('Y-m-d') . '.csv"');
    
    // 創建檔案指標
    $output = fopen('php://output', 'w');
    
    // 寫入 BOM 以支援中文
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // 寫入收入數據
    fputcsv($output, [$lang['income_data']]);
    fputcsv($output, [$lang['date'], $lang['job_name'], $lang['income'], $lang['currency'], $lang['notes']]);
    
    foreach ($jobs as $job) {
        fputcsv($output, [
            $job['date'],
            $job['job_name'],
            $job['income'],
            $job['currency'],
            $job['notes'] ?? ''
        ]);
    }
    
    // 空行
    fputcsv($output, []);
    
    // 寫入支出數據
    fputcsv($output, [$lang['expense_data']]);
    fputcsv($output, [$lang['date'], $lang['category'], $lang['amount'], $lang['currency'], $lang['notes']]);
    
    foreach ($expenses as $expense) {
        fputcsv($output, [
            $expense['date'],
            $expense['category'],
            $expense['amount'],
            $expense['currency'],
            $expense['notes'] ?? ''
        ]);
    }
    
    fclose($output);
    exit;
}

/**
 * 計算淨收入
 * @param float $totalIncome 總收入
 * @param float $totalExpenses 總支出
 * @return float 淨收入
 */
function calculateNetIncome($totalIncome, $totalExpenses) {
    return $totalIncome - $totalExpenses;
}

/**
 * 獲取財務摘要
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @return array 財務摘要陣列
 */
function getFinancialSummary($conn, $userId) {
    $jobs = getJobs($conn, $userId);
    $expenses = getExpenses($conn, $userId);
    
    $totalIncome = getTotalIncome($jobs);
    $totalExpenses = getTotalExpenses($expenses);
    $netIncome = calculateNetIncome($totalIncome, $totalExpenses);
    
    return [
        'total_income' => $totalIncome,
        'total_expenses' => $totalExpenses,
        'net_income' => $netIncome,
        'job_count' => count($jobs),
        'expense_count' => count($expenses)
    ];
} 