<?php
/**
 * 收入相關函數模組
 */

require_once 'currency.php';

/**
 * 獲取用戶的所有工作記錄
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @return array 工作記錄陣列
 */
function getJobs($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM jobs WHERE user_id = ? ORDER BY date DESC");
    $stmt->execute([$userId]);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($jobs as &$job) {
        $job['income_mop'] = convertToMOP($job['income'], $job['currency']);
    }
    
    return $jobs;
}

/**
 * 計算總收入（澳門幣）
 * @param array $jobs 工作記錄陣列
 * @return float 總收入
 */
function getTotalIncome($jobs) {
    return array_sum(array_column($jobs, 'income_mop'));
}

/**
 * 獲取用戶的工作名稱列表
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @return array 工作名稱陣列
 */
function getJobNames($conn, $userId) {
    $stmt = $conn->prepare("SELECT DISTINCT jn.name 
                            FROM job_names jn 
                            JOIN jobs j ON jn.name = j.job_name 
                            WHERE j.user_id = ? 
                            ORDER BY jn.name");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * 根據工作名稱獲取收入統計
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param string $filter 篩選條件 ('all' 或 'this_month')
 * @return array 收入統計陣列
 */
function getIncomeByJobName($conn, $userId, $filter = 'all') {
    $query = "SELECT job_name, income, currency 
              FROM jobs 
              WHERE user_id = ? ";
    $params = [$userId];

    if ($filter === 'this_month') {
        $query .= "AND DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') ";
    }

    $query .= "ORDER BY job_name";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $incomeByJobName = [];
    foreach ($rows as $row) {
        $jobName = $row['job_name'];
        $incomeMop = convertToMOP($row['income'], $row['currency']);
        
        if (!isset($incomeByJobName[$jobName])) {
            $incomeByJobName[$jobName] = 0;
        }
        $incomeByJobName[$jobName] += $incomeMop;
    }

    $result = [];
    foreach ($incomeByJobName as $jobName => $total) {
        $result[] = [
            'job_name' => $jobName,
            'total_income' => round($total, 2)
        ];
    }

    return $result;
}

/**
 * 獲取月度收入統計
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @return array 月度收入統計陣列
 */
function getMonthlyIncome($conn, $userId) {
    $stmt = $conn->prepare("SELECT DATE_FORMAT(date, '%Y-%m') AS month, income, currency 
                            FROM jobs 
                            WHERE user_id = ? 
                            ORDER BY month");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $monthlyIncome = [];
    foreach ($rows as $row) {
        $month = $row['month'];
        $incomeMop = convertToMOP($row['income'], $row['currency']);
        if (!isset($monthlyIncome[$month])) {
            $monthlyIncome[$month] = 0;
        }
        $monthlyIncome[$month] += $incomeMop;
    }

    $result = [];
    foreach ($monthlyIncome as $month => $total) {
        $result[] = [
            'month' => $month,
            'total_income' => round($total, 2)
        ];
    }
    
    usort($result, function($a, $b) {
        return strcmp($a['month'], $b['month']);
    });

    return $result;
}

/**
 * 根據指定月份查詢各職位收入
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param string $month 月份
 * @return array 收入統計陣列
 */
function getIncomeByJobNameByMonth($conn, $userId, $month) {
    $query = "SELECT job_name, income, currency FROM jobs WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ? ORDER BY job_name";
    $params = [$userId, $month];
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $incomeByJobName = [];
    foreach ($rows as $row) {
        $jobName = $row['job_name'];
        $incomeMop = convertToMOP($row['income'], $row['currency']);
        if (!isset($incomeByJobName[$jobName])) {
            $incomeByJobName[$jobName] = 0;
        }
        $incomeByJobName[$jobName] += $incomeMop;
    }
    $result = [];
    foreach ($incomeByJobName as $jobName => $total) {
        $result[] = [
            'job_name' => $jobName,
            'total_income' => round($total, 2)
        ];
    }
    return $result;
}

/**
 * 根據指定月份查詢月度收入
 * @param PDO $conn 資料庫連接
 * @param int $userId 用戶ID
 * @param string $month 月份
 * @return array 月度收入統計陣列
 */
function getMonthlyIncomeByMonth($conn, $userId, $month) {
    $stmt = $conn->prepare("SELECT DATE_FORMAT(date, '%Y-%m') AS month, income, currency FROM jobs WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ? ORDER BY month");
    $stmt->execute([$userId, $month]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $monthlyIncome = [];
    foreach ($rows as $row) {
        $m = $row['month'];
        $incomeMop = convertToMOP($row['income'], $row['currency']);
        if (!isset($monthlyIncome[$m])) {
            $monthlyIncome[$m] = 0;
        }
        $monthlyIncome[$m] += $incomeMop;
    }
    $result = [];
    foreach ($monthlyIncome as $m => $total) {
        $result[] = [
            'month' => $m,
            'total_income' => round($total, 2)
        ];
    }
    return $result;
}
?> 