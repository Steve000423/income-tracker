<?php
/**
 * 貨幣轉換模組
 */

// 支援的貨幣
$supportedCurrencies = ['MOP', 'HKD', 'RMB', 'USD', 'JPN'];

/**
 * 將指定貨幣轉換為澳門幣 (MOP)
 * @param float $amount 金額
 * @param string $currency 貨幣代碼
 * @return float 轉換後的金額（澳門幣）
 */
function convertToMOP($amount, $currency) {
    $exchangeRates = [
        'MOP' => 1.00,
        'HKD' => 1.03,
        'RMB' => 1.13,
        'USD' => 8.00,
        'JPN' => 0.056
    ];
    
    if (!isset($exchangeRates[$currency])) {
        // 如果貨幣不支援，返回原金額
        return $amount;
    }
    
    return round($amount * $exchangeRates[$currency], 2);
}

/**
 * 檢查貨幣是否支援
 * @param string $currency 貨幣代碼
 * @return bool 是否支援
 */
function isSupportedCurrency($currency) {
    global $supportedCurrencies;
    return in_array($currency, $supportedCurrencies);
}

/**
 * 獲取支援的貨幣列表
 * @return array 支援的貨幣陣列
 */
function getSupportedCurrencies() {
    global $supportedCurrencies;
    return $supportedCurrencies;
}

/**
 * 格式化貨幣顯示
 * @param float $amount 金額
 * @param string $currency 貨幣代碼
 * @return string 格式化後的金額字串
 */
function formatCurrency($amount, $currency) {
    return number_format($amount, 2) . ' ' . $currency;
}

/**
 * 格式化澳門幣顯示
 * @param float $amount 金額（澳門幣）
 * @return string 格式化後的金額字串
 */
function formatMOP($amount) {
    return number_format($amount, 2) . ' MOP';
}
?> 