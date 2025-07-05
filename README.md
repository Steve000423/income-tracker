# 收入追蹤器 (Income Tracker)

一個功能完整的個人收入與支出管理系統，支援多語言介面、數據視覺化圖表、行動裝置響應式設計，以及深色/淺色主題切換。

## 🌟 主要功能

### 📊 收入管理
- 新增、編輯、刪除工作收入記錄
- 按工作名稱分類收入
- 支援多種收入來源追蹤
- 收入統計與分析

### 💰 支出管理
- 新增、編輯、刪除支出記錄
- 按類別分類支出
- 支出趨勢分析
- 支出預算控制

### 📈 數據視覺化
- 收入來源圓餅圖
- 支出類別分布圖
- 月度收入/支出趨勢圖
- 淨收入統計

### 🔐 用戶管理
- 用戶註冊與登入系統
- 安全的密碼驗證
- 會話管理
- 用戶資料保護

### 🌍 多語言支援
- 繁體中文 (預設)
- 英文
- 可輕鬆擴展其他語言

### 📱 響應式設計
- 桌面版完整功能
- 手機版專用介面
- 觸控友善的操作體驗

### 🎨 主題切換
- 淺色主題
- 深色主題
- 自動適應系統偏好

## 🛠️ 技術架構

### 後端技術
- **PHP 7.4+** - 主要程式語言
- **MySQL** - 資料庫管理系統
- **PDO** - 資料庫連接與操作
- **Session** - 用戶會話管理

### 前端技術
- **HTML5** - 頁面結構
- **CSS3** - 樣式設計與響應式佈局
- **JavaScript** - 互動功能
- **Chart.js** - 數據視覺化圖表
- **AJAX** - 非同步數據處理

### 專案結構
```
income-tracker/
├── api/                    # API 端點
├── config.php             # 資料庫配置
├── css/                   # 樣式文件
├── error/                 # 錯誤頁面
├── images/                # 圖片資源
├── includes/              # 核心模組
│   ├── auth.php          # 認證模組
│   ├── crud.php          # 資料操作模組
│   ├── currency.php      # 貨幣處理
│   ├── database.php      # 資料庫連接
│   ├── expense.php       # 支出管理
│   ├── income.php        # 收入管理
│   ├── language.php      # 多語言支援
│   └── statistics.php    # 統計分析
├── lang/                  # 語言文件
├── phone/                 # 手機版介面
├── views/                 # 頁面視圖
└── index_new.php         # 主入口文件
```

## 📋 系統需求

### 伺服器環境
- **PHP**: 7.4 或更高版本
- **MySQL**: 5.7 或更高版本
- **Web 伺服器**: Apache 或 Nginx
- **PHP 擴展**: PDO, PDO_MySQL, Session

### 瀏覽器支援
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## 🚀 安裝說明

### 1. 下載專案
```bash
git clone https://github.com/your-username/income-tracker.git
cd income-tracker
```

### 2. 配置資料庫
1. 在 MySQL 中建立新資料庫：
```sql
CREATE DATABASE income_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. 修改 `config.php` 文件中的資料庫連接資訊：
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'income_tracker');
```

### 3. 建立資料表
執行以下 SQL 語句建立必要的資料表：

```sql
-- 用戶表
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 工作收入表
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_name VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 支出表
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 4. 設定檔案權限
```bash
chmod 755 css/
chmod 644 css/style.css
chmod 755 images/
```

### 5. 訪問應用程式
在瀏覽器中訪問：`http://your-domain/income-tracker/`

## 📖 使用指南

### 首次使用
1. 訪問應用程式首頁
2. 點擊「註冊」建立新帳戶
3. 填寫用戶名、密碼和電子郵件
4. 登入系統開始使用

### 新增收入記錄
1. 在「新增工作收入」區域選擇或輸入工作名稱
2. 輸入收入金額
3. 選擇收入日期
4. 可選：添加描述
5. 點擊「新增」按鈕

### 新增支出記錄
1. 在「新增支出」區域選擇支出類別
2. 輸入支出金額
3. 選擇支出日期
4. 可選：添加描述
5. 點擊「新增」按鈕

### 查看統計數據
- **總覽卡片**：顯示總收入、總支出和淨收入
- **收入來源圖表**：按工作名稱顯示收入分布
- **支出類別圖表**：按類別顯示支出分布
- **月度趨勢圖**：顯示收入支出趨勢

### 管理記錄
- 點擊記錄旁的「編輯」按鈕修改資料
- 點擊「刪除」按鈕移除記錄
- 使用篩選功能查看特定時間範圍的數據

### 數據匯出
- 點擊「匯出數據」按鈕下載 CSV 格式的完整數據

## 🔧 自訂設定

### 新增語言支援
1. 在 `lang/` 目錄下建立新的語言文件 (如 `ja.php`)
2. 參考現有語言文件格式
3. 在 `includes/language.php` 中註冊新語言

### 修改主題色彩
編輯 `css/style.css` 中的 CSS 變數：
```css
:root {
    --button-primary: #3498db;
    --button-danger: #e74c3c;
    --button-info: #2ecc71;
}
```

### 自訂支出類別
修改 `includes/expense.php` 中的預設類別陣列：
```php
$defaultCategories = ['餐飲', '交通', '購物', '娛樂', '其他'];
```

## 🐛 故障排除

### 常見問題

**Q: 無法連接到資料庫**
A: 檢查 `config.php` 中的資料庫設定是否正確，確認 MySQL 服務正在運行。

**Q: 頁面顯示空白**
A: 檢查 PHP 錯誤日誌，確認所有必要的 PHP 擴展已安裝。

**Q: 圖表無法顯示**
A: 確認網路連接正常，Chart.js CDN 可以正常載入。

**Q: 手機版功能異常**
A: 確認手機版文件在 `phone/` 目錄下，並檢查檔案權限。

### 錯誤代碼
- **403**: 權限不足
- **404**: 頁面不存在
- **500**: 伺服器內部錯誤

## 🤝 貢獻指南

歡迎提交 Issue 和 Pull Request！

### 開發環境設定
1. Fork 專案
2. 建立功能分支：`git checkout -b feature/new-feature`
3. 提交變更：`git commit -am 'Add new feature'`
4. 推送分支：`git push origin feature/new-feature`
5. 建立 Pull Request

### 程式碼規範
- 使用 PSR-4 自動載入標準
- 遵循 PSR-12 程式碼風格
- 添加適當的註釋和文件說明
- 確保所有功能都有對應的測試

## 📄 授權條款

本專案採用 MIT 授權條款 - 詳見 [LICENSE](LICENSE) 文件

## 👨‍💻 作者

- **開發者**: [您的姓名]
- **電子郵件**: [您的郵箱]
- **GitHub**: [您的 GitHub 連結]

## 🙏 致謝

- Chart.js 團隊提供的優秀圖表庫
- PHP 社群提供的豐富資源
- 所有貢獻者的寶貴建議和回饋

---

⭐ 如果這個專案對您有幫助，請給我們一個星標！