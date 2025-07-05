<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/httpd/php_errors.log');

// Session settings for iOS compatibility
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Set timezone
date_default_timezone_set('Asia/Taipei');

// Load config
require_once __DIR__ . '/config.php';

// Language handling
$defaultLang = 'zh';
$availableLangs = ['zh', 'en'];
$langCode = isset($_SESSION['lang']) ? $_SESSION['lang'] : $defaultLang;

// 設定語言
$lang = $_SESSION['lang'] ?? 'zh';
$langFile = __DIR__ . "/lang/{$lang}.php";

if (!file_exists($langFile)) {
    die('無法載入語言檔案，請檢查檔案權限和路徑');
}

$lang = include $langFile;

// Handle language switch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'switch_lang') {
    $newLang = $_POST['lang'];
    if (in_array($newLang, $availableLangs)) {
        $_SESSION['lang'] = $newLang;
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $lang['invalid_language']]);
    }
    exit;
}

// Supported currencies
$supportedCurrencies = ['MOP', 'HKD', 'RMB', 'USD', 'JPN'];

// Database connection
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
        error_log("DB Connection failed: " . $e->getMessage());
        die("Connection failed");
    }
}

// Redirect to expense-login.php if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /expense-login.php');
    exit;
}

// Get expense categories
$userId = $_SESSION['user_id'];
$conn = getDbConnection();
$expenseCategories = $conn->prepare("SELECT name FROM expense_categories ORDER BY name");
$expenseCategories->execute();
$categories = $expenseCategories->fetchAll(PDO::FETCH_COLUMN);

// Handle Requests
$conn = getDbConnection();

// Handle authentication actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'register') {
        handleRegister($conn, $lang);
        exit;
    } elseif ($_POST['action'] === 'login') {
        handleLogin($conn, $lang);
        exit;
    } elseif ($_POST['action'] === 'logout') {
        // 清除所有 session 資料
        session_unset();
        session_destroy();
        
        // 設定回應標頭
        header('Content-Type: application/json');
        
        // 返回成功狀態
        echo json_encode(['status' => 'success']);
        exit;
    } elseif ($_POST['action'] === 'sync_expenses' && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $expenses = json_decode($_POST['expenses'], true);
        
        if (!is_array($expenses)) {
            echo json_encode(['status' => 'error', 'message' => $lang['invalid_data']]);
            exit;
        }

        try {
            $conn->beginTransaction();
            
            foreach ($expenses as $expense) {
                // 確保類別存在
                $stmt = $conn->prepare("INSERT IGNORE INTO expense_categories (name) VALUES (?)");
                $stmt->execute([$expense['category']]);
                
                // 插入支出記錄
                $stmt = $conn->prepare("INSERT INTO expenses (category, amount, date, currency, notes, user_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $expense['category'],
                    $expense['amount'],
                    $expense['date'],
                    $expense['currency'],
                    $expense['notes'] ?? '',
                    $userId
                ]);
            }
            
            $conn->commit();
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Sync error: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => $lang['server_error']]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang['app_language']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Expense Tracker">
    <meta name="theme-color" content="#4CAF50">
    <meta name="description" content="Income and Expense Tracker Application">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <title><?php echo $lang['app_title']; ?></title>
    <style>
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #388E3C;
            --primary-light: #C8E6C9;
            --accent-color: #FF9800;
            --accent-dark: #F57C00;
            --text-primary: #212121;
            --text-secondary: #757575;
            --divider-color: #BDBDBD;
            --error-color: #F44336;
            --success-color: #4CAF50;
            --background-color: #F5F5F5;
            --card-background: #FFFFFF;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.6;
        }

        h1 {
            text-align: center;
            color: var(--text-primary);
            font-size: 28px;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .input-section {
            background: var(--card-background);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 12px var(--shadow-color);
            margin-bottom: 25px;
            transition: transform 0.2s ease;
        }

        .input-section:hover {
            transform: translateY(-2px);
        }

        select, input {
            width: 100%;
            padding: 12px 16px;
            margin: 8px 0;
            border: 2px solid var(--divider-color);
            border-radius: 12px;
            box-sizing: border-box;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: var(--card-background);
        }

        select:focus, input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin: 12px 0;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--shadow-color);
        }

        button:active {
            transform: translateY(0);
        }

        .logout-btn {
            background-color: var(--error-color);
        }

        .logout-btn:hover {
            background-color: #D32F2F;
        }

        .sync-btn {
            background-color: var(--primary-color);
        }

        .sync-btn:hover {
            background-color: var(--primary-dark);
        }

        .reset-btn {
            background-color: var(--accent-color);
        }

        .reset-btn:hover {
            background-color: var(--accent-dark);
        }

        .delete-btn {
            background-color: var(--error-color);
            padding: 8px 16px;
            font-size: 14px;
            width: auto;
            display: inline-block;
            border-radius: 8px;
            cursor: pointer;
            margin: 0;
        }

        .delete-btn:hover {
            background-color: #D32F2F;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--card-background);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px var(--shadow-color);
            margin-top: 25px;
        }

        th, td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid var(--divider-color);
        }

        th {
            background-color: var(--primary-light);
            color: var(--text-primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: var(--primary-light);
        }

        td:last-child {
            text-align: center;
        }

        #customCategory {
            display: none;
        }

        .lang-switcher {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .lang-switcher select {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 14px;
            border: 2px solid var(--divider-color);
            background-color: var(--card-background);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .lang-switcher select:hover {
            border-color: var(--primary-color);
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            h1 {
                font-size: 24px;
                margin-bottom: 20px;
            }

            .input-section {
                padding: 20px;
            }

            select, input, button {
                font-size: 14px;
                padding: 10px 14px;
            }

            .delete-btn {
                font-size: 12px;
                padding: 6px 12px;
            }

            th, td {
                padding: 12px;
                font-size: 14px;
            }

            .lang-switcher {
                position: static;
                margin-bottom: 20px;
                text-align: right;
            }
        }

        .remove-btn {
            background-color: var(--accent-color);
            padding: 8px 16px;
            font-size: 14px;
            width: auto;
            display: inline-block;
            border-radius: 8px;
            cursor: pointer;
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background-color: var(--accent-dark);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px var(--shadow-color);
        }

        /* 動畫效果 */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .input-section, table {
            animation: fadeIn 0.5s ease-out;
        }

        /* 載入動畫 */
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loading::after {
            content: "";
            width: 40px;
            height: 40px;
            border: 4px solid var(--primary-light);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<div class="lang-switcher">
    <select id="languageSelect" onchange="switchLanguage()">
        <option value="zh" <?php echo $langCode === 'zh' ? 'selected' : ''; ?>>繁體中文</option>
        <option value="en" <?php echo $langCode === 'en' ? 'selected' : ''; ?>>English</option>
    </select>
</div>
<h1><?php echo $lang['expense_tracker']; ?></h1>
<div class="input-section">
    <select id="category" onchange="toggleCustomCategory()">
        <option value=""><?php echo $lang['select_category']; ?></option>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
        <?php endforeach; ?>
        <option value="custom"><?php echo $lang['custom_category']; ?></option>
    </select>
    <input type="text" id="customCategory" placeholder="<?php echo $lang['enter_category']; ?>">
    <input type="text" 
           id="amount" 
           placeholder="<?php echo $lang['amount']; ?>" 
           inputmode="decimal"
           onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46"
           oninput="validateAmount(this)"
           onpaste="return false"
           required>
    <select id="currency" required>
        <option value=""><?php echo $lang['select_currency']; ?></option>
        <?php foreach ($supportedCurrencies as $currency): ?>
            <option value="<?php echo $currency; ?>"><?php echo $currency; ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" id="date" required>
    <input type="text" id="notes" placeholder="<?php echo $lang['notes']; ?>" maxlength="255">
    <button onclick="addExpense()"><?php echo $lang['add_expense']; ?></button>
</div>
<button class="sync-btn" onclick="syncExpenses()"><?php echo $lang['sync_now']; ?></button>
<button class="reset-btn" onclick="resetSyncStatus()" hidden><?php echo $lang['reset_sync_status']; ?></button>
<button class="logout-btn" onclick="logout()"><?php echo $lang['logout']; ?></button>
<table id="expenseTable">
    <thead>
        <tr>
            <th><?php echo $lang['category']; ?></th>
            <th><?php echo $lang['amount']; ?></th>
            <th><?php echo $lang['currency']; ?></th>
            <th><?php echo $lang['date']; ?></th>
            <th><?php echo $lang['notes']; ?></th>
            <th><?php echo $lang['synced']; ?></th>
            <th><?php echo $lang['action']; ?></th>
        </tr>
    </thead>
    <tbody id="expenseTableBody"></tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/dexie@3/dist/dexie.min.js"></script>
<script>
const db = new Dexie('ExpenseTracker');
db.version(25).stores({
    expenses: '++id,amount,category,currency,date,notes,synced',
    syncMetadata: 'key'
});

// 初始化資料庫並遷移資料
async function initDatabase() {
    try {
        // 檢查是否需要遷移資料
        const oldExpenses = await db.expenses.toArray();
        if (oldExpenses.length > 0) {
            // 將布林值轉換為數字
            for (const expense of oldExpenses) {
                await db.expenses.update(expense.id, {
                    synced: expense.synced ? 1 : 0
                });
            }
        }
    } catch (error) {
        console.error('Database migration error:', error);
    }
}

// 在頁面載入時初始化資料庫
initDatabase();

async function switchLanguage() {
    const lang = document.getElementById('languageSelect').value;
    const formData = new FormData();
    formData.append('action', 'switch_lang');
    formData.append('lang', lang);

    try {
        const response = await fetch('/expense.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const result = await response.json();
        if (result.status === 'success') {
            window.location.reload();
        } else {
            console.error('Language switch failed:', result.message);
            alert('<?php echo $lang['invalid_language']; ?>');
        }
    } catch (error) {
        console.error('Language switch error:', error);
        alert('<?php echo $lang['server_error']; ?>');
    }
}

function toggleCustomCategory() {
    const categorySelect = document.getElementById('category');
    const customCategory = document.getElementById('customCategory');
    customCategory.style.display = categorySelect.value === 'custom' ? 'block' : 'none';
    if (categorySelect.value !== 'custom') {
        customCategory.value = '';
    }
}

function validateAmount(input) {
    // 獲取當前輸入值
    let value = input.value;
    
    // 如果輸入為空，直接返回
    if (value === '') {
        return;
    }
    
    // 只允許數字和小數點，其他字符直接移除
    value = value.replace(/[^\d.]/g, '');
    
    // 處理小數點
    const parts = value.split('.');
    
    // 如果有多個小數點，只保留第一個
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // 如果小數點後超過兩位，截取前兩位
    if (parts.length > 1 && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    // 限制整數部分不超過 6 位數
    if (parts[0].length > 6) {
        value = parts[0].substring(0, 6) + (parts.length > 1 ? '.' + parts[1] : '');
    }
    
    // 限制最大值
    const numValue = parseFloat(value);
    if (numValue > 999999.99) {
        value = '999999.99';
    }
    
    // 更新輸入值
    if (value !== input.value) {
        input.value = value;
    }
}

// 防止貼上非數字內容
document.getElementById('amount').addEventListener('paste', function(e) {
    e.preventDefault();
    const pastedText = (e.clipboardData || window.clipboardData).getData('text');
    if (/^[\d.]+$/.test(pastedText)) {
        const start = this.selectionStart;
        const end = this.selectionEnd;
        const value = this.value;
        this.value = value.substring(0, start) + pastedText + value.substring(end);
        validateAmount(this);
    }
});

async function addExpense() {
    let category = document.getElementById('category').value;
    const customCategory = document.getElementById('customCategory').value;
    const amountInput = document.getElementById('amount');
    const amount = parseFloat(amountInput.value) || 0;
    const currency = document.getElementById('currency').value;
    const date = document.getElementById('date').value;
    const notes = document.getElementById('notes').value;

    if (category === 'custom' && customCategory) {
        category = customCategory;
    }

    if (!category || amount <= 0 || amount > 999999.99 || !currency || !date) {
        alert('<?php echo $lang['invalid_expense_input']; ?>');
        return;
    }

    const expense = { amount, category, currency, date, notes, synced: 0 }; // 使用 0 代替 false
    try {
        const id = await db.expenses.add(expense);
        console.log('Added expense to IndexedDB:', { id, ...expense });
    } catch (error) {
        console.error('Failed to add expense to IndexedDB:', error);
        let errorMessage = '<?php echo $lang['server_error']; ?>: Failed to save expense';
        if (error.name === 'VersionError') {
            errorMessage = '<?php echo $lang['server_error']; ?>: Database version conflict. Please clear browser data and try again.';
        }
        alert(errorMessage);
        return;
    }
    document.getElementById('category').value = '';
    document.getElementById('customCategory').value = '';
    document.getElementById('amount').value = '';
    document.getElementById('currency').value = '';
    document.getElementById('date').value = '';
    document.getElementById('notes').value = '';
    updateExpenseTable();
}

async function updateExpenseTable() {
    try {
        const expenses = await db.expenses.orderBy('date').reverse().toArray();
        console.log('Local expenses:', expenses);
        const tbody = document.getElementById('expenseTableBody');
        tbody.innerHTML = '';
        expenses.forEach(expense => {
            console.log(`Rendering expense ID ${expense.id}, synced: ${expense.synced}, type: ${typeof expense.synced}`);
            const currencyDisplay = expense.currency || 'TWD';
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${expense.category}</td>
                <td>${expense.amount.toFixed(2)}</td>
                <td>${currencyDisplay}</td>
                <td>${expense.date}</td>
                <td>${expense.notes || ''}</td>
                <td>${expense.synced === 1 ? '<?php echo $lang['yes']; ?>' : '<?php echo $lang['no']; ?>'}</td>
                <td>
                    ${expense.synced === 1 ? 
                        `<button class="remove-btn" onclick="removeFromUI(${expense.id})"><?php echo $lang['remove_from_ui']; ?></button>` :
                        `<button class="delete-btn" onclick="deleteExpense(${expense.id})"><?php echo $lang['delete']; ?></button>`
                    }
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Failed to update expense table:', error);
        alert('<?php echo $lang['server_error']; ?>: Failed to load expenses');
    }
}

async function deleteExpense(id) {
    try {
        const expense = await db.expenses.get(id);
        if (expense) {
            if (expense.synced === 1) { // 使用 1 代替 true
                await db.expenses.delete(id);
                console.log(`Removed synced expense ID ${id} from UI only`);
                alert('<?php echo $lang['expense_removed_from_ui']; ?>');
            } else {
                await db.expenses.delete(id);
                console.log(`Deleted unsynced expense ID ${id} from IndexedDB`);
                alert('<?php echo $lang['expense_deleted']; ?>');
            }
            updateExpenseTable();
        } else {
            console.warn(`Cannot delete expense ID ${id}: Not found`);
            alert('<?php echo $lang['cannot_delete_expense']; ?>');
        }
    } catch (error) {
        console.error(`Failed to delete expense ID ${id}:`, error);
        alert('<?php echo $lang['server_error']; ?>: Failed to delete expense');
    }
}

async function removeFromUI(id) {
    try {
        const expense = await db.expenses.get(id);
        if (expense && expense.synced === 1) { // 使用 1 代替 true
            await db.expenses.delete(id);
            console.log(`Removed synced expense ID ${id} from UI only`);
            alert('<?php echo $lang['expense_removed_from_ui']; ?>');
            updateExpenseTable();
        } else {
            console.warn(`Cannot remove expense ID ${id}: Not found or not synced`);
            alert('<?php echo $lang['cannot_remove_expense']; ?>');
        }
    } catch (error) {
        console.error(`Failed to remove expense ID ${id}:`, error);
        alert('<?php echo $lang['server_error']; ?>: Failed to remove expense');
    }
}

async function syncExpenses() {
    try {
        const unsyncedExpenses = await db.expenses.where('synced').equals(0).toArray(); // 使用 0 代替 false
        if (unsyncedExpenses.length === 0) {
            alert('<?php echo $lang['no_expenses_to_sync']; ?>');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'sync_expenses');
        formData.append('expenses', JSON.stringify(unsyncedExpenses));

        const response = await fetch('', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const result = await response.json();
        if (result.status === 'success') {
            // 更新本地資料庫中的同步狀態
            for (const expense of unsyncedExpenses) {
                await db.expenses.update(expense.id, { synced: 1 }); // 使用 1 代替 true
            }
            alert(`${unsyncedExpenses.length} <?php echo $lang['expenses_synced']; ?>`);
            updateExpenseTable();
        } else {
            console.error('Sync failed:', result.message);
            alert('<?php echo $lang['server_error']; ?>: ' + result.message);
        }
    } catch (error) {
        console.error('Sync error:', error);
        alert('<?php echo $lang['server_error']; ?>: ' + error.message);
    }
}

async function logout() {
    try {
        const formData = new FormData();
        formData.append('action', 'logout');

        const response = await fetch('', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const result = await response.json();
        if (result.status === 'success') {
            // 清除 IndexedDB 資料
            await db.delete();
            // 重新導向到登入頁面
            window.location.href = '/expense-login.php';
        } else {
            console.error('Logout failed:', result.message);
            alert('<?php echo $lang['server_error']; ?>: ' + result.message);
        }
    } catch (error) {
        console.error('Logout error:', error);
        alert('<?php echo $lang['server_error']; ?>: ' + error.message);
    }
}
</script>
</body>
</html>