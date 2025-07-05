<?php
/**
 * 登入/註冊頁面視圖
 */

// 確保語言模組已載入
if (!function_exists('getCurrentLang')) {
    require_once __DIR__ . '/../includes/language.php';
}

$langCode = getCurrentLang();
$lang = loadLanguage($langCode);
?>
<!DOCTYPE html>
<html lang="<?php echo $langCode; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['app_title']; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/dexie@3/dist/dexie.min.js"></script>
    <style>
        :root {
            --primary-color: #3498db;
            --primary-hover: #2980b9;
            --danger-color: #e74c3c;
            --danger-hover: #c0392b;
            --text-color: #2c3e50;
            --bg-color: #f8f9fa;
            --card-bg: white;
            --border-color: #e9ecef;
            --shadow: 0 4px 6px rgba(0,0,0,0.1);
            --error-color: #e74c3c;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 28px;
            color: var(--primary-color);
            margin: 0;
            font-weight: 600;
        }

        .logo p {
            color: #666;
            margin: 10px 0 0;
            font-size: 14px;
        }

        .auth-container {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .auth-container:hover {
            transform: translateY(-5px);
        }

        .auth-container h2 {
            text-align: center;
            margin: 0 0 25px;
            color: var(--text-color);
            font-size: 20px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-size: 14px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .toggle-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            padding: 10px;
            margin: 15px auto;
            font-size: 14px;
            cursor: pointer;
            display: block;
            transition: all 0.3s ease;
        }

        .toggle-btn:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .error {
            color: var(--error-color);
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }

        .lang-switcher {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .lang-switcher select {
            padding: 8px 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--card-bg);
            color: var(--text-color);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .lang-switcher select:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="lang-switcher">
        <select onchange="switchLanguage(this.value)">
            <option value="zh" <?php echo $langCode === 'zh' ? 'selected' : ''; ?>>中文</option>
            <option value="en" <?php echo $langCode === 'en' ? 'selected' : ''; ?>>English</option>
        </select>
    </div>

    <div class="container">
        <div class="logo">
            <h1><?php echo $lang['app_title']; ?></h1>
            <p><?php echo $lang['app_description']; ?></p>
        </div>

        <div class="auth-container">
            <!-- 登入表單 -->
            <form id="loginForm" style="display: block;">
                <h2><?php echo $lang['login']; ?></h2>
                <div class="form-group">
                    <label for="loginUsername"><?php echo $lang['username']; ?></label>
                    <input type="text" id="loginUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword"><?php echo $lang['password']; ?></label>
                    <div style="position: relative;">
                        <input type="password" id="loginPassword" name="password" required>
                        <button type="button" onclick="togglePassword('loginPassword', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">👁️</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $lang['login']; ?></button>
                <button type="button" class="toggle-btn" onclick="toggleForm()"><?php echo $lang['no_account']; ?></button>
            </form>

            <!-- 註冊表單 -->
            <form id="registerForm" style="display: none;">
                <h2><?php echo $lang['register']; ?></h2>
                <div class="form-group">
                    <label for="registerUsername"><?php echo $lang['username']; ?></label>
                    <input type="text" id="registerUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="registerPassword"><?php echo $lang['password']; ?></label>
                    <div style="position: relative;">
                        <input type="password" id="registerPassword" name="password" required>
                        <button type="button" onclick="togglePassword('registerPassword', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">👁️</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $lang['register']; ?></button>
                <button type="button" class="toggle-btn" onclick="toggleForm()"><?php echo $lang['have_account']; ?></button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 <?php echo $lang['app_title']; ?>. <?php echo $lang['all_rights_reserved']; ?></p>
    </footer>

    <script>
        function toggleForm() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            
            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
            }
        }

        function switchLanguage(lang) {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=switch_lang&lang=' + lang
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                }
            });
        }

        function login() {
            const username = document.getElementById('loginUsername').value;
            const password = document.getElementById('loginPassword').value;
            
            if (!username || !password) {
                alert('<?php echo $lang['fill_all_fields']; ?>');
                return;
            }
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=login&username=' + encodeURIComponent(username) + '&password=' + encodeURIComponent(password)
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

        function register() {
            const username = document.getElementById('registerUsername').value;
            const password = document.getElementById('registerPassword').value;
            
            if (!username || !password) {
                alert('<?php echo $lang['fill_all_fields']; ?>');
                return;
            }
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=register&username=' + encodeURIComponent(username) + '&password=' + encodeURIComponent(password)
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

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            login();
        });

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            register();
        });

        document.getElementById('loginUsername').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                login();
            }
        });

        document.getElementById('loginPassword').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                login();
            }
        });

        document.getElementById('registerUsername').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                register();
            }
        });

        document.getElementById('registerPassword').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                register();
            }
        });

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
            }
        }
    </script>
</body>
</html> 