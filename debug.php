<?php
/**
 * 診斷腳本 - 檢查重構後的應用程式問題
 */

echo "<!DOCTYPE html>\n";
echo "<html lang='zh'>\n";
echo "<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "<title>收入支出追蹤器 - 診斷報告</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; }\n";
echo ".success { color: green; }\n";
echo ".error { color: red; }\n";
echo ".warning { color: orange; }\n";
echo ".section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }\n";
echo "</style>\n";
echo "</head>\n";
echo "<body>\n";

echo "<h1>收入支出追蹤器 - 診斷報告</h1>\n";

// 1. 檢查 PHP 版本
echo "<div class='section'>\n";
echo "<h2>1. PHP 環境檢查</h2>\n";
echo "<p>PHP 版本: <span class='success'>" . phpversion() . "</span></p>\n";
echo "<p>PDO 支援: <span class='" . (extension_loaded('pdo') ? 'success' : 'error') . "'>" . (extension_loaded('pdo') ? '是' : '否') . "</span></p>\n";
echo "<p>PDO MySQL 支援: <span class='" . (extension_loaded('pdo_mysql') ? 'success' : 'error') . "'>" . (extension_loaded('pdo_mysql') ? '是' : '否') . "</span></p>\n";
echo "</div>\n";

// 2. 檢查檔案存在性
echo "<div class='section'>\n";
echo "<h2>2. 檔案存在性檢查</h2>\n";

$requiredFiles = [
    'config.php',
    'includes/database.php',
    'includes/language.php',
    'includes/currency.php',
    'includes/income.php',
    'includes/expense.php',
    'includes/statistics.php',
    'includes/auth.php',
    'includes/crud.php',
    'views/login.php',
    'views/dashboard.php',
    'index_new.php'
];

foreach ($requiredFiles as $file) {
    $exists = file_exists($file);
    $class = $exists ? 'success' : 'error';
    $status = $exists ? '存在' : '不存在';
    echo "<p>$file: <span class='$class'>$status</span></p>\n";
}
echo "</div>\n";

// 3. 檢查目錄權限
echo "<div class='section'>\n";
echo "<h2>3. 目錄權限檢查</h2>\n";

$directories = [
    'includes',
    'views',
    'css',
    'lang',
    'images'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $readable = is_readable($dir);
        $writable = is_writable($dir);
        $class = $readable ? 'success' : 'error';
        echo "<p>$dir/: <span class='$class'>可讀: " . ($readable ? '是' : '否') . ", 可寫: " . ($writable ? '是' : '否') . "</span></p>\n";
    } else {
        echo "<p>$dir/: <span class='error'>目錄不存在</span></p>\n";
    }
}
echo "</div>\n";

// 4. 檢查模組載入
echo "<div class='section'>\n";
echo "<h2>4. 模組載入測試</h2>\n";

try {
    require_once 'includes/language.php';
    echo "<p>語言模組: <span class='success'>載入成功</span></p>\n";
} catch (Exception $e) {
    echo "<p>語言模組: <span class='error'>載入失敗 - " . $e->getMessage() . "</span></p>\n";
}

try {
    require_once 'includes/database.php';
    echo "<p>資料庫模組: <span class='success'>載入成功</span></p>\n";
} catch (Exception $e) {
    echo "<p>資料庫模組: <span class='error'>載入失敗 - " . $e->getMessage() . "</span></p>\n";
}

try {
    require_once 'includes/currency.php';
    echo "<p>貨幣模組: <span class='success'>載入成功</span></p>\n";
} catch (Exception $e) {
    echo "<p>貨幣模組: <span class='error'>載入失敗 - " . $e->getMessage() . "</span></p>\n";
}

try {
    require_once 'includes/income.php';
    echo "<p>收入模組: <span class='success'>載入成功</span></p>\n";
} catch (Exception $e) {
    echo "<p>收入模組: <span class='error'>載入失敗 - " . $e->getMessage() . "</span></p>\n";
}

try {
    require_once 'includes/expense.php';
    echo "<p>支出模組: <span class='success'>載入成功</span></p>\n";
} catch (Exception $e) {
    echo "<p>支出模組: <span class='error'>載入失敗 - " . $e->getMessage() . "</span></p>\n";
}

try {
    require_once 'includes/statistics.php';
    echo "<p>統計模組: <span class='success'>載入成功</span></p>\n";
} catch (Exception $e) {
    echo "<p>統計模組: <span class='error'>載入失敗 - " . $e->getMessage() . "</span></p>\n";
}

try {
    require_once 'includes/auth.php';
    echo "<p>認證模組: <span class='success'>載入成功</span></p>\n";
} catch (Exception $e) {
    echo "<p>認證模組: <span class='error'>載入失敗 - " . $e->getMessage() . "</span></p>\n";
}

try {
    require_once 'includes/crud.php';
    echo "<p>CRUD模組: <span class='success'>載入成功</span></p>\n";
} catch (Exception $e) {
    echo "<p>CRUD模組: <span class='error'>載入失敗 - " . $e->getMessage() . "</span></p>\n";
}
echo "</div>\n";

// 5. 檢查函數可用性
echo "<div class='section'>\n";
echo "<h2>5. 函數可用性測試</h2>\n";

$functions = [
    'getCurrentLang',
    'loadLanguage',
    'convertToMOP',
    'getSupportedCurrencies',
    'isLoggedIn',
    'getCurrentUserId'
];

foreach ($functions as $function) {
    $exists = function_exists($function);
    $class = $exists ? 'success' : 'error';
    $status = $exists ? '可用' : '不可用';
    echo "<p>$function(): <span class='$class'>$status</span></p>\n";
}
echo "</div>\n";

// 6. 檢查資料庫連接
echo "<div class='section'>\n";
echo "<h2>6. 資料庫連接測試</h2>\n";

if (file_exists('config.php')) {
    try {
        require_once 'config.php';
        echo "<p>配置檔案: <span class='success'>載入成功</span></p>\n";
        
        // 檢查配置變數
        $configVars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];
        foreach ($configVars as $var) {
            if (defined($var)) {
                echo "<p>$var: <span class='success'>已定義</span></p>\n";
            } else {
                echo "<p>$var: <span class='error'>未定義</span></p>\n";
            }
        }
        
        // 嘗試連接資料庫
        try {
            $conn = getDbConnection();
            echo "<p>資料庫連接: <span class='success'>成功</span></p>\n";
        } catch (Exception $e) {
            echo "<p>資料庫連接: <span class='error'>失敗 - " . $e->getMessage() . "</span></p>\n";
        }
    } catch (Exception $e) {
        echo "<p>配置檔案: <span class='error'>載入失敗 - " . $e->getMessage() . "</span></p>\n";
    }
} else {
    echo "<p>配置檔案: <span class='error'>不存在</span></p>\n";
}
echo "</div>\n";

// 7. 檢查語言檔案
echo "<div class='section'>\n";
echo "<h2>7. 語言檔案檢查</h2>\n";

$langFiles = ['lang/zh.php', 'lang/en.php'];
foreach ($langFiles as $file) {
    if (file_exists($file)) {
        try {
            $lang = include $file;
            $count = is_array($lang) ? count($lang) : 0;
            echo "<p>$file: <span class='success'>載入成功 ($count 個翻譯項目)</span></p>\n";
        } catch (Exception $e) {
            echo "<p>$file: <span class='error'>載入失敗 - " . $e->getMessage() . "</span></p>\n";
        }
    } else {
        echo "<p>$file: <span class='error'>不存在</span></p>\n";
    }
}
echo "</div>\n";

// 8. 檢查 Web 伺服器配置
echo "<div class='section'>\n";
echo "<h2>8. Web 伺服器資訊</h2>\n";
echo "<p>伺服器軟體: " . ($_SERVER['SERVER_SOFTWARE'] ?? '未知') . "</p>\n";
echo "<p>PHP SAPI: " . php_sapi_name() . "</p>\n";
echo "<p>當前檔案: " . __FILE__ . "</p>\n";
echo "<p>文檔根目錄: " . ($_SERVER['DOCUMENT_ROOT'] ?? '未知') . "</p>\n";
echo "<p>請求 URI: " . ($_SERVER['REQUEST_URI'] ?? '未知') . "</p>\n";
echo "</div>\n";

// 9. 建議解決方案
echo "<div class='section'>\n";
echo "<h2>9. 建議解決方案</h2>\n";
echo "<h3>如果遇到 403 Forbidden 錯誤：</h3>\n";
echo "<ol>\n";
echo "<li>檢查檔案權限：確保 includes/ 和 views/ 目錄有適當的讀取權限</li>\n";
echo "<li>檢查 .htaccess 檔案：確保沒有阻止訪問的規則</li>\n";
echo "<li>檢查 Web 伺服器配置：確保 PHP 檔案可以被執行</li>\n";
echo "<li>嘗試直接訪問 index_new.php 而不是 index.php</li>\n";
echo "</ol>\n";

echo "<h3>如果遇到 KaTeX 警告：</h3>\n";
echo "<ol>\n";
echo "<li>確保所有 HTML 檔案都有正確的 DOCTYPE 聲明</li>\n";
echo "<li>檢查是否有 JavaScript 錯誤影響頁面載入</li>\n";
echo "<li>清除瀏覽器快取並重新載入頁面</li>\n";
echo "</ol>\n";

echo "<h3>測試步驟：</h3>\n";
echo "<ol>\n";
echo "<li>先運行：<code>php test_refactor.php</code></li>\n";
echo "<li>然後訪問：<code>http://your-domain/debug.php</code></li>\n";
echo "<li>最後測試：<code>http://your-domain/index_new.php</code></li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "</body>\n";
echo "</html>\n";
?> 