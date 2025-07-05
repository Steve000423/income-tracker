<?php
/**
 * 支出相關函數模組
 */

require_once 'currency.php';

/**
 * 獲取用戶的所有支出記錄
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @return array 支出記錄陣列
 */
function getExpenses($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY date DESC");
    $stmt->execute([$userId]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($expenses as &$expense) {
        $expense['amount_mop'] = convertToMOP($expense['amount'], $expense['currency']);
    }
    
    return $expenses;
}

/**
 * 計算總支出（澳門幣）
 * @param array $expenses 支出記錄陣列
 * @return float 總支出
 */
function getTotalExpenses($expenses) {
    return array_sum(array_column($expenses, 'amount_mop'));
}

/**
 * 獲取用戶的支出類別列表
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @return array 支出類別陣列
 */
function getExpenseCategories($conn, $userId) {
    $stmt = $conn->prepare("SELECT name FROM expense_categories ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * 根據類別獲取支出統計
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param string $filter 篩選條件 ('all' 或 'this_month')
 * @return array 支出統計陣列
 */
function getExpensesByCategory($conn, $userId, $filter = 'all') {
    $query = "SELECT category, amount, currency 
              FROM expenses 
              WHERE user_id = ? ";
    $params = [$userId];

    if ($filter === 'this_month') {
        $query .= "AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') ";
    }

    $query .= "ORDER BY category";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $expensesByCategory = [];
    foreach ($rows as $row) {
        $category = $row['category'];
        $amountMop = convertToMOP($row['amount'], $row['currency']);
        
        if (!isset($expensesByCategory[$category])) {
            $expensesByCategory[$category] = 0;
        }
        $expensesByCategory[$category] += $amountMop;
    }

    $result = [];
    foreach ($expensesByCategory as $category => $total) {
        $result[] = [
            'category' => $category,
            'total_expense' => round($total, 2)
        ];
    }

    return $result;
}

/**
 * 獲取月度支出統計
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @return array 月度支出統計陣列
 */
function getMonthlyExpenses($conn, $userId) {
    $stmt = $conn->prepare("SELECT DATE_FORMAT(date, '%Y-%m') AS month, amount, currency 
                            FROM expenses 
                            WHERE user_id = ? 
                            ORDER BY month");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $monthlyExpenses = [];
    foreach ($rows as $row) {
        $month = $row['month'];
        $amountMop = convertToMOP($row['amount'], $row['currency']);
        if (!isset($monthlyExpenses[$month])) {
            $monthlyExpenses[$month] = 0;
        }
        $monthlyExpenses[$month] += $amountMop;
    }

    $result = [];
    foreach ($monthlyExpenses as $month => $total) {
        $result[] = [
            'month' => $month,
            'total_expense' => round($total, 2)
        ];
    }
    
    usort($result, function($a, $b) {
        return strcmp($a['month'], $b['month']);
    });

    return $result;
}

/**
 * 根據指定月份查詢各分類支出
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param string $month 月份
 * @return array 支出統計陣列
 */
function getExpensesByCategoryByMonth($conn, $userId, $month) {
    $query = "SELECT category, amount, currency FROM expenses WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ? ORDER BY category";
    $params = [$userId, $month];
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $expensesByCategory = [];
    foreach ($rows as $row) {
        $category = $row['category'];
        $amountMop = convertToMOP($row['amount'], $row['currency']);
        if (!isset($expensesByCategory[$category])) {
            $expensesByCategory[$category] = 0;
        }
        $expensesByCategory[$category] += $amountMop;
    }
    $result = [];
    foreach ($expensesByCategory as $category => $total) {
        $result[] = [
            'category' => $category,
            'total_expense' => round($total, 2)
        ];
    }
    return $result;
}

/**
 * 根據指定月份查詢月度支出
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param string $month 月份
 * @return array 月度支出統計陣列
 */
function getMonthlyExpensesByMonth($conn, $userId, $month) {
    $stmt = $conn->prepare("SELECT DATE_FORMAT(date, '%Y-%m') AS month, amount, currency FROM expenses WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ? ORDER BY month");
    $stmt->execute([$userId, $month]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $monthlyExpenses = [];
    foreach ($rows as $row) {
        $m = $row['month'];
        $amountMop = convertToMOP($row['amount'], $row['currency']);
        if (!isset($monthlyExpenses[$m])) {
            $monthlyExpenses[$m] = 0;
        }
        $monthlyExpenses[$m] += $amountMop;
    }
    $result = [];
    foreach ($monthlyExpenses as $m => $total) {
        $result[] = [
            'month' => $m,
            'total_expense' => round($total, 2)
        ];
    }
    return $result;
}
?> 