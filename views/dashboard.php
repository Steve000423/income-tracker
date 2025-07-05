<?php
/**
 * 主應用程式頁面視圖
 */

// 確保必要的模組已載入
if (!function_exists('getCurrentLang')) {
    require_once __DIR__ . '/../includes/language.php';
}
if (!function_exists('getCurrentUserId')) {
    require_once __DIR__ . '/../includes/auth.php';
}
if (!function_exists('getChartData')) {
    require_once __DIR__ . '/../includes/statistics.php';
}
if (!function_exists('getTotalIncome')) {
    require_once __DIR__ . '/../includes/income.php';
}
if (!function_exists('getTotalExpenses')) {
    require_once __DIR__ . '/../includes/expense.php';
}

$langCode = getCurrentLang();
$lang = loadLanguage($langCode);

// 獲取數據
$userId = getCurrentUserId();
$jobs = getJobs($conn, $userId);
$totalIncome = getTotalIncome($jobs);
$jobNames = getJobNames($conn, $userId);
$expenses = getExpenses($conn, $userId);
$totalExpenses = getTotalExpenses($expenses);
$expenseCategories = getExpenseCategories($conn, $userId);
$chartData = getChartData($conn, $userId);
$netIncome = $totalIncome - $totalExpenses;
?>
<!DOCTYPE html>
<html lang="<?php echo $langCode; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['app_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            /* 淺色模式變數 */
            --bg-color: #f8f9fa;
            --text-color: #2c3e50;
            --card-bg: white;
            --card-shadow: 0 4px 6px rgba(0,0,0,0.1);
            --border-color: #e9ecef;
            --input-bg: white;
            --table-header-bg: #f8f9fa;
            --modal-overlay: rgba(0,0,0,0.5);
            --modal-bg: white;
            --button-primary: #3498db;
            --button-primary-hover: #2980b9;
            --button-danger: #e74c3c;
            --button-danger-hover: #c0392b;
            --button-info: #2ecc71;
            --button-info-hover: #27ae60;
            --button-warning: #f1c40f;
            --button-warning-hover: #f39c12;
        }

        [data-theme="dark"] {
            /* 深色模式變數 */
            --bg-color: #1a1a1a;
            --text-color: #ecf0f1;
            --card-bg: #2d2d2d;
            --card-shadow: 0 4px 6px rgba(0,0,0,0.2);
            --border-color: #404040;
            --input-bg: #333333;
            --table-header-bg: #333333;
            --modal-overlay: rgba(0,0,0,0.7);
            --modal-bg: #2d2d2d;
            --button-primary: #3498db;
            --button-primary-hover: #2980b9;
            --button-danger: #e74c3c;
            --button-danger-hover: #c0392b;
            --button-info: #2ecc71;
            --button-info-hover: #27ae60;
            --button-warning: #f1c40f;
            --button-warning-hover: #f39c12;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
            font-size: 14px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 18px;
            margin-bottom: 15px;
        }

        h3 {
            font-size: 16px;
            margin-bottom: 12px;
        }

        .input-section, .summary, .chart-container, .summary-grid {
            background: var(--card-bg);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            transition: transform 0.2s ease;
        }
        .input-section:hover, .summary:hover, .chart-container:hover, .summary-grid:hover {
            transform: translateY(-2px);
        }
        .chart-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .chart-row {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 20px;
        }
        .chart-box {
            width: 400px;
            height: 400px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .chart-box canvas {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }

        .input-section select, .input-section input {
            padding: 8px;
            margin: 5px;
            width: calc(20% - 10px);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--input-bg);
            color: var(--text-color);
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .input-section select:focus, .input-section input:focus {
            border-color: var(--button-primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        button {
            padding: 8px 15px;
            background-color: var(--button-primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 13px;
        }

        button:hover {
            background-color: var(--button-primary-hover);
            transform: translateY(-1px);
        }

        .logout-btn {
            background-color: var(--button-danger);
            padding: 8px 16px;
            font-size: 13px;
        }

        .logout-btn:hover {
            background-color: var(--button-danger-hover);
        }

        .edit-btn {
            background-color: var(--button-info);
            margin-right: 8px;
        }

        .edit-btn:hover {
            background-color: var(--button-info-hover);
        }

        .export-btn {
            background-color: var(--button-warning);
            margin-left: 12px;
        }

        .export-btn:hover {
            background-color: var(--button-warning-hover);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 15px;
        }

        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
            font-size: 13px;
        }

        th {
            background-color: var(--table-header-bg);
            font-weight: 600;
        }

        tr:hover {
            background-color: rgba(0,0,0,0.02);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--modal-overlay);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: var(--modal-bg);
            padding: 20px;
            border-radius: 12px;
            width: 400px;
            color: var(--text-color);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .modal-content select, .modal-content input {
            width: calc(50% - 10px);
            padding: 8px;
            margin: 5px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--input-bg);
            color: var(--text-color);
            font-size: 13px;
        }

        .theme-switch {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--card-bg);
            border: none;
            padding: 8px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: var(--card-shadow);
            font-size: 16px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: var(--card-bg);
            padding: 15px;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            text-align: center;
        }

        .summary-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: var(--text-color);
        }

        .summary-card .amount {
            font-size: 24px;
            font-weight: bold;
            color: var(--button-primary);
        }

        .summary-card .net-income {
            color: var(--button-info);
        }

        .summary-card .expense {
            color: var(--button-danger);
        }
    </style>
</head>
<body>
    <button id="themeSwitch" class="theme-switch" onclick="toggleTheme()">🌙</button>
    
    <h1><?php echo $lang['app_title']; ?></h1>
    <button class="logout-btn" onclick="logout()"><?php echo $lang['logout']; ?></button>
    <button class="export-btn" onclick="exportData()" hidden><?php echo $lang['export_data']; ?></button>

    <div style="display: flex; justify-content: flex-end; align-items: center; max-width: 1200px; margin: 0 auto 20px auto;">
        <button id="langSwitchBtn" onclick="switchLang()" style="padding:6px 16px;font-weight:600;border-radius:8px;border:none;background:#3498db;color:white;cursor:pointer;">
            <?php echo $langCode==='zh' ? 'English' : '中文'; ?>
        </button>
    </div>

    <!-- 月份篩選下拉選單 -->
    <div style="display: flex; justify-content: flex-start; align-items: center; max-width: 1200px; margin: 0 auto 20px auto;">
        <label for="monthFilter" style="margin-right:8px;font-weight:600;">月份篩選：</label>
        <select id="monthFilter">
            <option value="all">全部</option>
            <?php
            // 取得所有有資料的月份
            $allMonths = [];
            foreach ($jobs as $job) {
                $m = date('Y-m', strtotime($job['date']));
                if (!in_array($m, $allMonths)) $allMonths[] = $m;
            }
            foreach ($expenses as $expense) {
                $m = date('Y-m', strtotime($expense['date']));
                if (!in_array($m, $allMonths)) $allMonths[] = $m;
            }
            rsort($allMonths);
            foreach ($allMonths as $m) {
                echo '<option value="' . $m . '">' . $m . '</option>';
            }
            ?>
        </select>
    </div>

    <!-- 財務摘要 -->
    <div class="summary-grid">
        <div class="summary-card">
            <h3><?php echo $lang['total_income']; ?></h3>
            <div class="amount"><?php echo number_format($totalIncome, 2); ?> MOP</div>
        </div>
        <div class="summary-card">
            <h3><?php echo $lang['total_expenses']; ?></h3>
            <div class="amount expense"><?php echo number_format($totalExpenses, 2); ?> MOP</div>
        </div>
        <div class="summary-card">
            <h3><?php echo $lang['net_income']; ?></h3>
            <div class="amount net-income"><?php echo number_format($netIncome, 2); ?> MOP</div>
        </div>
    </div>

    <!-- 收入輸入區域 -->
    <div class="input-section">
        <h2><?php echo $lang['add_income']; ?></h2>
        <input list="jobNameList" id="jobName" placeholder="<?php echo $lang['select_job']; ?>">
        <datalist id="jobNameList">
            <?php foreach ($jobNames as $jobName): ?>
                <option value="<?php echo htmlspecialchars($jobName); ?>">
            <?php endforeach; ?>
        </datalist>
        <input type="number" id="hourlyWage" placeholder="<?php echo $lang['hourly_wage']; ?>" step="0.01">
        <input type="number" id="hoursWorked" placeholder="<?php echo $lang['hours_worked']; ?>" step="0.01">
        <input type="number" id="income" placeholder="<?php echo $lang['income']; ?>" step="0.01" readonly style="background:#e9ecef;">
        <select id="incomeCurrency">
            <option value="MOP">MOP</option>
            <option value="HKD">HKD</option>
            <option value="RMB">RMB</option>
            <option value="USD">USD</option>
            <option value="JPN">JPN</option>
        </select>
        <input type="date" id="incomeDate" value="<?php echo date('Y-m-d'); ?>">
        <input type="text" id="incomeNotes" placeholder="<?php echo $lang['notes']; ?>">
        <button onclick="addJob()"><?php echo $lang['add']; ?></button>
        <script>
        // 自動計算金額
        function updateIncomeAmount() {
            const wage = parseFloat(document.getElementById('hourlyWage').value) || 0;
            const hours = parseFloat(document.getElementById('hoursWorked').value) || 0;
            document.getElementById('income').value = (wage * hours).toFixed(2);
        }
        document.getElementById('hourlyWage').addEventListener('input', updateIncomeAmount);
        document.getElementById('hoursWorked').addEventListener('input', updateIncomeAmount);
        </script>
    </div>

    <!-- 支出輸入區域 -->
    <div class="input-section">
        <h2><?php echo $lang['add_expense']; ?></h2>
        <input list="expenseCategoryList" id="expenseCategory" placeholder="<?php echo $lang['select_category']; ?>">
        <datalist id="expenseCategoryList">
            <?php foreach ($expenseCategories as $category): ?>
                <option value="<?php echo htmlspecialchars($category); ?>">
            <?php endforeach; ?>
        </datalist>
        <input type="number" id="expenseAmount" placeholder="<?php echo $lang['amount']; ?>" step="0.01">
        <select id="expenseCurrency">
            <option value="MOP">MOP</option>
            <option value="HKD">HKD</option>
            <option value="RMB">RMB</option>
            <option value="USD">USD</option>
            <option value="JPN">JPN</option>
        </select>
        <input type="date" id="expenseDate" value="<?php echo date('Y-m-d'); ?>">
        <input type="text" id="expenseNotes" placeholder="<?php echo $lang['notes']; ?>">
        <button onclick="addExpense()"><?php echo $lang['add']; ?></button>
    </div>

    <!-- 收入記錄表格 -->
    <div class="summary">
        <h2><?php echo $lang['income_records']; ?></h2>
        <table id="incomeTable">
            <thead>
                <tr>
                    <th><?php echo $lang['date']; ?></th>
                    <th><?php echo $lang['job_name']; ?></th>
                    <th><?php echo $lang['income']; ?></th>
                    <th><?php echo $lang['currency']; ?></th>
                    <th><?php echo $lang['notes']; ?></th>
                    <th><?php echo $lang['actions']; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                <tr>
                    <td><?php echo htmlspecialchars($job['date']); ?></td>
                    <td><?php echo htmlspecialchars($job['job_name']); ?></td>
                    <td><?php echo number_format($job['income'], 2); ?></td>
                    <td><?php echo htmlspecialchars($job['currency']); ?></td>
                    <td><?php echo htmlspecialchars($job['notes'] ?? ''); ?></td>
                    <td>
                        <button class="edit-btn" onclick="editJob(<?php echo $job['id']; ?>, '<?php echo htmlspecialchars($job['job_name']); ?>', <?php echo $job['income']; ?>, '<?php echo $job['currency']; ?>', '<?php echo $job['date']; ?>', '<?php echo htmlspecialchars($job['notes'] ?? ''); ?>', <?php echo $job['hours_worked']; ?>, <?php echo $job['hourly_wage']; ?>)"><?php echo $lang['edit']; ?></button>
                        <button class="logout-btn" onclick="deleteJob(<?php echo $job['id']; ?>)"><?php echo $lang['delete']; ?></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 支出記錄表格 -->
    <div class="summary">
        <h2><?php echo $lang['expense_records']; ?></h2>
        <table id="expenseTable">
            <thead>
                <tr>
                    <th><?php echo $lang['date']; ?></th>
                    <th><?php echo $lang['category']; ?></th>
                    <th><?php echo $lang['amount']; ?></th>
                    <th><?php echo $lang['currency']; ?></th>
                    <th><?php echo $lang['notes']; ?></th>
                    <th><?php echo $lang['actions']; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenses as $expense): ?>
                <tr>
                    <td><?php echo htmlspecialchars($expense['date']); ?></td>
                    <td><?php echo htmlspecialchars($expense['category']); ?></td>
                    <td><?php echo number_format($expense['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($expense['currency']); ?></td>
                    <td><?php echo htmlspecialchars($expense['notes'] ?? ''); ?></td>
                    <td>
                        <button class="edit-btn" onclick="editExpense(<?php echo $expense['id']; ?>, '<?php echo htmlspecialchars($expense['category']); ?>', <?php echo $expense['amount']; ?>, '<?php echo $expense['currency']; ?>', '<?php echo $expense['date']; ?>', '<?php echo htmlspecialchars($expense['notes'] ?? ''); ?>')"><?php echo $lang['edit']; ?></button>
                        <button class="logout-btn" onclick="deleteExpense(<?php echo $expense['id']; ?>)"><?php echo $lang['delete']; ?></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 圖表區域 -->
    <div class="chart-container">
        <h2><?php echo $lang['charts']; ?></h2>
        <div class="chart-row">
            <div class="chart-box"><canvas id="incomeChart"></canvas></div>
            <div class="chart-box"><canvas id="expenseChart"></canvas></div>
        </div>
        <div class="chart-row">
            <div class="chart-box"><canvas id="monthlyChart"></canvas></div>
            <div class="chart-box"><canvas id="netIncomeChart"></canvas></div>
        </div>
    </div>

    <!-- 編輯工作記錄模態框 -->
    <div id="editJobModal" class="modal">
        <div class="modal-content">
            <h3><?php echo $lang['edit_job']; ?></h3>
            <input type="hidden" id="editJobId">
            <input type="text" id="editJobName" placeholder="<?php echo $lang['select_job']; ?>">
            <input type="number" id="editHourlyWage" placeholder="<?php echo $lang['hourly_wage']; ?>" step="0.01">
            <input type="number" id="editHoursWorked" placeholder="<?php echo $lang['hours_worked']; ?>" step="0.01">
            <input type="number" id="editIncome" placeholder="<?php echo $lang['income']; ?>" step="0.01">
            <select id="editIncomeCurrency">
                <option value="MOP">MOP</option>
                <option value="HKD">HKD</option>
                <option value="RMB">RMB</option>
                <option value="USD">USD</option>
                <option value="JPN">JPN</option>
            </select>
            <input type="date" id="editIncomeDate">
            <input type="text" id="editIncomeNotes" placeholder="<?php echo $lang['notes']; ?>">
            <button onclick="updateJob()"><?php echo $lang['update']; ?></button>
            <button onclick="closeModal('editJobModal')"><?php echo $lang['cancel']; ?></button>
        </div>
    </div>

    <!-- 編輯支出記錄模態框 -->
    <div id="editExpenseModal" class="modal">
        <div class="modal-content">
            <h3><?php echo $lang['edit_expense']; ?></h3>
            <input type="hidden" id="editExpenseId">
            <select id="editExpenseCategory">
                <?php foreach ($expenseCategories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" id="editExpenseAmount" step="0.01">
            <select id="editExpenseCurrency">
                <option value="MOP">MOP</option>
                <option value="HKD">HKD</option>
                <option value="RMB">RMB</option>
                <option value="USD">USD</option>
                <option value="JPN">JPN</option>
            </select>
            <input type="date" id="editExpenseDate">
            <input type="text" id="editExpenseNotes">
            <button onclick="updateExpense()"><?php echo $lang['update']; ?></button>
            <button onclick="closeModal('editExpenseModal')"><?php echo $lang['cancel']; ?></button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script>
    $(document).ready(function() {
        $('#incomeTable').DataTable({
            "lengthMenu": [10, 25, 50, 100],
            "order": [[0, "desc"]]
        });
        $('#expenseTable').DataTable({
            "lengthMenu": [10, 25, 50, 100],
            "order": [[0, "desc"]]
        });
    });

    let incomeChart, expenseChart, monthlyChart, netIncomeChart;

    // 初始化收入圖表
    function initIncomeChart() {
        const ctx = document.getElementById('incomeChart').getContext('2d');
        const data = <?php echo json_encode($chartData['incomeByJobName']); ?>;
        
        incomeChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.map(item => item.job_name),
                datasets: [{
                    data: data.map(item => item.total_income),
                    backgroundColor: [
                        '#36a2eb', '#ff6384', '#4bc0c0', '#ffcd56', '#9966ff',
                        '#ff9f40', '#ff6384', '#c9cbcf', '#4bc0c0', '#ff6384'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1,
                plugins: {
                    legend: { display: true },
                    title: { display: true, text: <?php echo json_encode($lang['income_by_job'] . ' (MOP)'); ?> },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw.toFixed(2) + ' MOP';
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // 初始化支出圖表
    function initExpenseChart() {
        const ctx = document.getElementById('expenseChart').getContext('2d');
        const data = <?php echo json_encode($chartData['expensesByCategory']); ?>;
        
        expenseChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.map(item => item.category),
                datasets: [{
                    data: data.map(item => item.total_expense),
                    backgroundColor: [
                        '#ff6384', '#36a2eb', '#4bc0c0', '#ffcd56', '#9966ff',
                        '#ff9f40', '#ff6384', '#c9cbcf', '#4bc0c0', '#ff6384'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1,
                plugins: {
                    legend: { display: true },
                    title: { display: true, text: <?php echo json_encode($lang['expenses_by_category'] . ' (MOP)'); ?> },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw.toFixed(2) + ' MOP';
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // 初始化月度比較圖表
    function initMonthlyChart() {
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const months = [<?php foreach ($chartData['allMonths'] as $month) { echo "'" . $month . "',"; } ?>];
        const incomeData = months.map(month => {
            const income = <?php echo json_encode($chartData['monthlyIncome']); ?>.find(i => i.month === month);
            return income ? income.total_income : 0;
        });
        const expenseData = months.map(month => {
            const expense = <?php echo json_encode($chartData['monthlyExpenses']); ?>.find(e => e.month === month);
            return expense ? expense.total_expense : 0;
        });

        monthlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: '<?php echo $lang['monthly_income']; ?> (MOP)',
                        data: incomeData,
                        backgroundColor: '#36a2eb',
                        stack: 'Stack0'
                    },
                    {
                        label: '<?php echo $lang['monthly_expenses']; ?> (MOP)',
                        data: expenseData,
                        backgroundColor: '#ff6384',
                        stack: 'Stack0'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: '<?php echo $lang['amount']; ?> (MOP)' }
                    },
                    x: {
                        title: { display: true, text: '<?php echo $lang['month']; ?>' }
                    }
                },
                plugins: {
                    legend: { display: true },
                    title: { display: true, text: <?php echo json_encode($lang['monthly_comparison'] . ' (MOP)'); ?> },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw.toFixed(2) + ' MOP';
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // 初始化淨收入圖表
    function initNetIncomeChart() {
        const ctx = document.getElementById('netIncomeChart').getContext('2d');
        const months = [<?php foreach ($chartData['allMonths'] as $month) { echo "'" . $month . "',"; } ?>];
        const incomeData = months.map(month => {
            const income = <?php echo json_encode($chartData['monthlyIncome']); ?>.find(i => i.month === month);
            return income ? income.total_income : 0;
        });
        const expenseData = months.map(month => {
            const expense = <?php echo json_encode($chartData['monthlyExpenses']); ?>.find(e => e.month === month);
            return expense ? expense.total_expense : 0;
        });
        const netIncomeData = incomeData.map((income, i) => income - expenseData[i]);

        netIncomeChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: '<?php echo $lang['net_income']; ?> (MOP)',
                    data: netIncomeData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1,
                scales: {
                    y: {
                        title: { display: true, text: '<?php echo $lang['amount']; ?> (MOP)' },
                        beginAtZero: false
                    },
                    x: {
                        title: { display: true, text: '<?php echo $lang['month']; ?>' }
                    }
                },
                plugins: {
                    legend: { display: true },
                    title: { display: true, text: <?php echo json_encode($lang['net_income_chart'] . ' (MOP)'); ?> },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw.toFixed(2) + ' MOP';
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // 初始化圖表
    window.onload = function() {
        initIncomeChart();
        initExpenseChart();
        initMonthlyChart();
        initNetIncomeChart();
    }

    // 切換主題（移除 Dexie，僅切換屬性與圖示）
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        updateThemeIcon(newTheme);
    }
    function updateThemeIcon(theme) {
        const themeSwitch = document.getElementById('themeSwitch');
        themeSwitch.textContent = theme === 'dark' ? '☀️' : '🌙';
    }
    // 預設主題初始化
    document.addEventListener('DOMContentLoaded', function() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-theme', 'dark');
            updateThemeIcon('dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            updateThemeIcon('light');
        }
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            const newTheme = e.matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            updateThemeIcon(newTheme);
        });
    });

    // CRUD 操作函數
    function addJob() {
        const jobName = document.getElementById('jobName').value;
        const hourlyWage = document.getElementById('hourlyWage').value;
        const hoursWorked = document.getElementById('hoursWorked').value;
        const currency = document.getElementById('incomeCurrency').value;
        const date = document.getElementById('incomeDate').value;
        const notes = document.getElementById('incomeNotes').value;

        if (!jobName || !hourlyWage || !hoursWorked) {
            alert(<?php echo json_encode($lang['fill_required_fields']); ?>);
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_job');
        formData.append('jobName', jobName);
        formData.append('hourlyWage', hourlyWage);
        formData.append('hoursWorked', hoursWorked);
        formData.append('currency', currency);
        formData.append('date', date);
        formData.append('notes', notes);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }

    function addExpense() {
        const category = document.getElementById('expenseCategory').value;
        const amount = document.getElementById('expenseAmount').value;
        const currency = document.getElementById('expenseCurrency').value;
        const date = document.getElementById('expenseDate').value;
        const notes = document.getElementById('expenseNotes').value;

        if (!category || !amount) {
            alert(<?php echo json_encode($lang['fill_required_fields']); ?>);
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_expense');
        formData.append('category', category);
        formData.append('amount', amount);
        formData.append('currency', currency);
        formData.append('date', date);
        formData.append('notes', notes);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }

    function editJob(id, jobName, income, currency, date, notes, hoursWorked, hourlyWage) {
        document.getElementById('editJobId').value = id;
        document.getElementById('editJobName').value = jobName;
        document.getElementById('editIncome').value = income;
        document.getElementById('editIncomeCurrency').value = currency;
        document.getElementById('editIncomeDate').value = date;
        document.getElementById('editIncomeNotes').value = notes;
        document.getElementById('editHoursWorked').value = hoursWorked;
        document.getElementById('editHourlyWage').value = hourlyWage;
        document.getElementById('editJobModal').style.display = 'flex';
    }

    function editExpense(id, category, amount, currency, date, notes) {
        document.getElementById('editExpenseId').value = id;
        document.getElementById('editExpenseCategory').value = category;
        document.getElementById('editExpenseAmount').value = amount;
        document.getElementById('editExpenseCurrency').value = currency;
        document.getElementById('editExpenseDate').value = date;
        document.getElementById('editExpenseNotes').value = notes;
        document.getElementById('editExpenseModal').style.display = 'flex';
    }

    function updateJob() {
        const id = document.getElementById('editJobId').value;
        const jobName = document.getElementById('editJobName').value;
        const hourlyWage = document.getElementById('editHourlyWage').value;
        const hoursWorked = document.getElementById('editHoursWorked').value;
        const income = document.getElementById('editIncome').value;
        const currency = document.getElementById('editIncomeCurrency').value;
        const date = document.getElementById('editIncomeDate').value;
        const notes = document.getElementById('editIncomeNotes').value;

        if (!jobName || !income) {
            alert(<?php echo json_encode($lang['fill_required_fields']); ?>);
            return;
        }

        const formData = new FormData();
        formData.append('action', 'edit_job');
        formData.append('job_id', id);
        formData.append('jobName', jobName);
        formData.append('hourlyWage', hourlyWage);
        formData.append('hoursWorked', hoursWorked);
        formData.append('income', income);
        formData.append('currency', currency);
        formData.append('date', date);
        formData.append('notes', notes);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status && data.status.toLowerCase() === 'success') {
                location.reload();
            } else {
                alert(data.message || '操作失敗');
            }
        });
    }

    function updateExpense() {
        const id = document.getElementById('editExpenseId').value;
        const category = document.getElementById('editExpenseCategory').value;
        const amount = document.getElementById('editExpenseAmount').value;
        const currency = document.getElementById('editExpenseCurrency').value;
        const date = document.getElementById('editExpenseDate').value;
        const notes = document.getElementById('editExpenseNotes').value;

        if (!category || !amount) {
            alert(<?php echo json_encode($lang['fill_required_fields']); ?>);
            return;
        }

        const formData = new FormData();
        formData.append('action', 'edit_expense');
        formData.append('expense_id', id);
        formData.append('category', category);
        formData.append('amount', amount);
        formData.append('currency', currency);
        formData.append('date', date);
        formData.append('notes', notes);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status && data.status.toLowerCase() === 'success') {
                location.reload();
            } else {
                alert(data.message || '操作失敗');
            }
        });
    }

    function deleteJob(id) {
        if (!confirm(<?php echo json_encode($lang['confirm_delete']); ?>)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete_job');
        formData.append('job_id', id);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status && data.status.toLowerCase() === 'success') {
                location.reload();
            } else {
                alert(data.message || '操作失敗');
            }
        });
    }

    function deleteExpense(id) {
        if (!confirm(<?php echo json_encode($lang['confirm_delete']); ?>)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete_expense');
        formData.append('expense_id', id);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status && data.status.toLowerCase() === 'success') {
                location.reload();
            } else {
                alert(data.message || '操作失敗');
            }
        });
    }

    function logout() {
        const formData = new FormData();
        formData.append('action', 'logout');

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            }
        });
    }

    function exportData() {
        const formData = new FormData();
        formData.append('action', 'export_data');

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'income_expense_data_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        });
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // 點擊模態框外部關閉
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }

    // 語言切換
    function switchLang() {
        const newLang = <?php echo json_encode($langCode==='zh' ? 'en' : 'zh'); ?>;
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=switch_lang&lang=' + newLang
        })
        .then(response => response.json())
        .then(data => { if(data.status==='success'){ location.reload(); } });
    }

    // 月份篩選AJAX
    $(document).ready(function() {
        $('#monthFilter').on('change', function() {
            var month = $(this).val();
            $.get('api/filter_by_month.php', {month: month}, function(data) {
                // 更新收入表格
                var incomeRows = '';
                data.incomes.forEach(function(job) {
                    incomeRows += '<tr>' +
                        '<td>' + job.date + '</td>' +
                        '<td>' + job.job_name + '</td>' +
                        '<td>' + parseFloat(job.income).toFixed(2) + '</td>' +
                        '<td>' + job.currency + '</td>' +
                        '<td>' + (job.notes || '') + '</td>' +
                        '<td>' +
                        '<button class="edit-btn" onclick="editJob('
                            + job.id + ', '
                            + JSON.stringify(job.job_name) + ', '
                            + job.income + ', '
                            + JSON.stringify(job.currency) + ', '
                            + JSON.stringify(job.date) + ', '
                            + JSON.stringify(job.notes || '') + ', '
                            + job.hours_worked + ', '
                            + job.hourly_wage
                        + ')"><?php echo $lang['edit']; ?></button>' +
                        '<button class="logout-btn" onclick="deleteJob(' + job.id + ')"><?php echo $lang['delete']; ?></button>' +
                        '</td>' +
                        '</tr>';
                });
                $('#incomeTable tbody').html(incomeRows);
                // 更新支出表格
                var expenseRows = '';
                data.expenses.forEach(function(expense) {
                    expenseRows += '<tr>' +
                        '<td>' + expense.date + '</td>' +
                        '<td>' + expense.category + '</td>' +
                        '<td>' + parseFloat(expense.amount).toFixed(2) + '</td>' +
                        '<td>' + expense.currency + '</td>' +
                        '<td>' + (expense.notes || '') + '</td>' +
                        '<td>' +
                        '<button class="edit-btn" onclick="editExpense('
                            + expense.id + ', '
                            + JSON.stringify(expense.category) + ', '
                            + expense.amount + ', '
                            + JSON.stringify(expense.currency) + ', '
                            + JSON.stringify(expense.date) + ', '
                            + JSON.stringify(expense.notes || '')
                        + ')"><?php echo $lang['edit']; ?></button>' +
                        '<button class="logout-btn" onclick="deleteExpense(' + expense.id + ')"><?php echo $lang['delete']; ?></button>' +
                        '</td>' +
                        '</tr>';
                });
                $('#expenseTable tbody').html(expenseRows);

                // ====== 圖表同步更新 ======
                // 收入圓餅
                if (incomeChart) incomeChart.destroy();
                incomeChart = new Chart(document.getElementById('incomeChart').getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: data.incomeByJobName.map(function(item){return item.job_name;}),
                        datasets: [{
                            data: data.incomeByJobName.map(function(item){return item.total_income;}),
                            backgroundColor: [
                                '#36a2eb', '#ff6384', '#4bc0c0', '#ffcd56', '#9966ff',
                                '#ff9f40', '#ff6384', '#c9cbcf', '#4bc0c0', '#ff6384'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        aspectRatio: 1,
                        plugins: {
                            legend: { display: true },
                            title: { display: true, text: <?php echo json_encode($lang['income_by_job'] . ' (MOP)'); ?> },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) label += ': ';
                                        label += context.raw.toFixed(2) + ' MOP';
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
                // 支出圓餅
                if (expenseChart) expenseChart.destroy();
                expenseChart = new Chart(document.getElementById('expenseChart').getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: data.expensesByCategory.map(function(item){return item.category;}),
                        datasets: [{
                            data: data.expensesByCategory.map(function(item){return item.total_expense;}),
                            backgroundColor: [
                                '#ff6384', '#36a2eb', '#4bc0c0', '#ffcd56', '#9966ff',
                                '#ff9f40', '#ff6384', '#c9cbcf', '#4bc0c0', '#ff6384'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        aspectRatio: 1,
                        plugins: {
                            legend: { display: true },
                            title: { display: true, text: <?php echo json_encode($lang['expenses_by_category'] . ' (MOP)'); ?> },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) label += ': ';
                                        label += context.raw.toFixed(2) + ' MOP';
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
                // 月度長條
                if (monthlyChart) monthlyChart.destroy();
                var months = data.allMonths;
                var incomeData = months.map(function(month){
                    var found = data.monthlyIncome.find(function(i){return i.month===month;});
                    return found ? found.total_income : 0;
                });
                var expenseData = months.map(function(month){
                    var found = data.monthlyExpenses.find(function(e){return e.month===month;});
                    return found ? found.total_expense : 0;
                });
                monthlyChart = new Chart(document.getElementById('monthlyChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [
                            {
                                label: '<?php echo $lang['monthly_income']; ?> (MOP)',
                                data: incomeData,
                                backgroundColor: '#36a2eb',
                                stack: 'Stack0'
                            },
                            {
                                label: '<?php echo $lang['monthly_expenses']; ?> (MOP)',
                                data: expenseData,
                                backgroundColor: '#ff6384',
                                stack: 'Stack0'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        aspectRatio: 1,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: '<?php echo $lang['amount']; ?> (MOP)' }
                            },
                            x: {
                                title: { display: true, text: '<?php echo $lang['month']; ?>' }
                            }
                        },
                        plugins: {
                            legend: { display: true },
                            title: { display: true, text: <?php echo json_encode($lang['monthly_comparison'] . ' (MOP)'); ?> },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        label += context.raw.toFixed(2) + ' MOP';
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
                // 淨收入折線
                if (netIncomeChart) netIncomeChart.destroy();
                var netIncomeData = incomeData.map(function(val, i){return val - expenseData[i];});
                netIncomeChart = new Chart(document.getElementById('netIncomeChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: '<?php echo $lang['net_income']; ?> (MOP)',
                            data: netIncomeData,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.2)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        aspectRatio: 1,
                        scales: {
                            y: {
                                title: { display: true, text: '<?php echo $lang['amount']; ?> (MOP)' },
                                beginAtZero: false
                            },
                            x: {
                                title: { display: true, text: '<?php echo $lang['month']; ?>' }
                            }
                        },
                        plugins: {
                            legend: { display: true },
                            title: { display: true, text: <?php echo json_encode($lang['net_income_chart'] . ' (MOP)'); ?> },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        label += context.raw.toFixed(2) + ' MOP';
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
                // ====== END 圖表同步更新 ======

                // ====== 更新 summary 數字 ======
                $(".summary-card:contains('<?php echo $lang['total_income']; ?>') .amount").text(data.totalIncome.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' MOP');
                $(".summary-card:contains('<?php echo $lang['total_expenses']; ?>') .amount").text(data.totalExpenses.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' MOP');
                $(".summary-card:contains('<?php echo $lang['net_income']; ?>') .amount").text(data.netIncome.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' MOP');
                // ====== END 更新 summary ======
            }, 'json');
        });
    });
    </script>
</body>
</html> 