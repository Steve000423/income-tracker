<?php
// 開啟錯誤顯示（僅用於除錯）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 設定錯誤日誌
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/httpd/php_errors.log');

// Session settings for iOS compatibility
ini_set('session.cookie_secure', '0');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60); // 30 days
session_set_cookie_params([
    'lifetime' => 30 * 24 * 60 * 60, // 30 days
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

try {
    session_start();
} catch (Exception $e) {
    error_log("Session start error: " . $e->getMessage());
    die("Session start failed: " . $e->getMessage());
}

// Set timezone
date_default_timezone_set('Asia/Taipei');

// Load config
try {
    require_once __DIR__ . '/config.php';
} catch (Exception $e) {
    error_log("Config load error: " . $e->getMessage());
    die("Config load failed: " . $e->getMessage());
}

// Language handling
$defaultLang = 'zh';
$availableLangs = ['zh', 'en'];
$langCode = isset($_SESSION['lang']) ? $_SESSION['lang'] : $defaultLang;

// 設定語言
$lang = $_SESSION['lang'] ?? 'zh';
$langFile = __DIR__ . "/lang/{$lang}.php";

if (!file_exists($langFile)) {
    error_log("Language file not found: " . $langFile);
    die("無法載入語言檔案，請檢查檔案權限和路徑");
}

try {
    $lang = include $langFile;
} catch (Exception $e) {
    error_log("Language file load error: " . $e->getMessage());
    die("語言檔案載入失敗: " . $e->getMessage());
}

// Database connection
function getDbConnection() {
    $host = DB_HOST;
    $user = DB_USER;
    $password = DB_PASS;
    $database = DB_NAME;
    try {
        $conn = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        error_log("DB Connection failed: " . $e->getMessage());
        die("資料庫連線失敗: " . $e->getMessage());
    }
}

// 初始化資料庫
try {
    $conn = getDbConnection();
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    error_log("Database initialization error: " . $e->getMessage());
    die("資料庫初始化失敗: " . $e->getMessage());
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] === 'true';

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => $lang['invalid_credentials']]);
        exit;
    }

    try {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_activity'] = time();
            
            if ($remember) {
                $_SESSION['expires'] = time() + (30 * 24 * 60 * 60);
                setcookie('remember_user', $user['id'], time() + (30 * 24 * 60 * 60), '/', '', false, true);
            }
            
            // 使用相對路徑
            echo json_encode([
                'status' => 'success',
                'redirect' => '/expense.php'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $lang['invalid_credentials']]);
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $lang['server_error']]);
    }
    exit;
}

// 檢查 session 是否過期
if (isset($_SESSION['expires']) && $_SESSION['expires'] < time()) {
    session_unset();
    session_destroy();
}

// Redirect to expense.php if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . SERVER_URL . '/expense.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $langCode; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="manifest" href="/expense-manifest.json">
    <link rel="apple-touch-icon" href="/images/icons8-expense-100.png">
    <title><?php echo $lang['expense_tracker_login']; ?></title>
    <style>
        body {
            font-family: -apple-system, Arial, sans-serif;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100vh;
        }
        h1 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .login-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007aff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #005bb5;
        }
        .lang-switcher {
            float: left;
            margin-bottom: 10px;
        }
        .lang-switcher select {
            padding: 5px;
            border-radius: 4px;
            font-size: 14px;
        }
        .error {
            color: #ff3b30;
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }
        @media (max-width: 400px) {
            body {
                padding: 10px;
            }
            h1 {
                font-size: 20px;
            }
            input, button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="lang-switcher">
    <select id="languageSelect" onchange="switchLanguage()"hidden>
        <option value="zh" <?php echo $langCode === 'zh' ? 'selected' : ''; ?>>繁體中文</option>
        <option value="en" <?php echo $langCode === 'en' ? 'selected' : ''; ?>>English</option>
    </select>
</div>
<h1><?php echo $lang['expense_tracker_login']; ?></h1>
<div class="login-section">
    <input type="text" id="username" placeholder="<?php echo $lang['username']; ?>" required>
    <input type="password" id="password" placeholder="<?php echo $lang['password']; ?>" required>
    <div style="margin: 10px 0;">
        <input type="checkbox" id="remember" style="width: auto;">
        <label for="remember"><?php echo $lang['remember_me']; ?></label>
    </div>
    <button onclick="login()"><?php echo $lang['login']; ?></button>
    <div id="error" class="error"></div>
</div>

<script>
async function switchLanguage() {
    const lang = document.getElementById('languageSelect').value;
    const formData = new FormData();
    formData.append('action', 'switch_lang');
    formData.append('lang', lang);

    try {
        const response = await fetch('/expense-login.php', {
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

async function login() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const remember = document.getElementById('remember').checked;
    const errorDiv = document.getElementById('error');

    if (!username || !password) {
        errorDiv.textContent = '<?php echo $lang['invalid_credentials']; ?>';
        return;
    }

    const formData = new FormData();
    formData.append('action', 'login');
    formData.append('username', username);
    formData.append('password', password);
    formData.append('remember', remember);

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const result = await response.json();

        if (result.status === 'success') {
            // 直接使用伺服器返回的完整網址進行跳轉
            window.location.href = result.redirect;
        } else {
            errorDiv.textContent = result.message || '<?php echo $lang['invalid_credentials']; ?>';
        }
    } catch (error) {
        console.error('Login error:', error);
        errorDiv.textContent = '<?php echo $lang['server_error']; ?>';
    }
}
</script>
</body>
</html>