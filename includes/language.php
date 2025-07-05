<?php
/**
 * 語言處理模組
 */

// session_start(); // 已由 index_new.php 統一啟動 session

// 語言設定
$defaultLang = 'zh';
$availableLangs = ['zh', 'en'];

/**
 * 獲取當前語言代碼
 * @return string 語言代碼
 */
function getCurrentLang() {
    global $defaultLang;
    return isset($_SESSION['lang']) ? $_SESSION['lang'] : $defaultLang;
}

/**
 * 處理語言切換
 * @param string $newLang 新語言代碼
 * @return array 結果陣列
 */
function switchLanguage($newLang) {
    global $availableLangs;
    
    if (in_array($newLang, $availableLangs)) {
        $_SESSION['lang'] = $newLang;
        return ['status' => 'success'];
    } else {
        return ['status' => 'error', 'message' => '無效的語言選擇'];
    }
}

/**
 * 載入語言檔案
 * @param string $langCode 語言代碼
 * @return array 語言陣列
 */
function loadLanguage($langCode) {
    global $defaultLang;
    
    $langFile = __DIR__ . "/../lang/{$langCode}.php";
    
    if (!file_exists($langFile)) {
        // 如果指定的語言檔案不存在，使用預設語言
        $langFile = __DIR__ . "/../lang/{$defaultLang}.php";
        if (!file_exists($langFile)) {
            die('無法載入語言檔案，請檢查檔案權限和路徑');
        }
    }
    
    return include $langFile;
}

/**
 * 處理語言切換 API 請求
 */
function handleLanguageSwitch() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'switch_lang') {
        $newLang = $_POST['lang'];
        $result = switchLanguage($newLang);
        echo json_encode($result);
        exit;
    }
}

/**
 * 處理 URL 語言參數
 */
function handleUrlLanguage() {
    if (isset($_GET['lang'])) {
        $lang = $_GET['lang'];
        if ($lang === 'en' || $lang === 'zh') {
            $_SESSION['lang'] = $lang;
        } else {
            die('無效的語言選擇');
        }
    }
}

// 初始化語言處理
handleLanguageSwitch();
handleUrlLanguage();
?> 