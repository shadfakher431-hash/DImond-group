<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/language_helper.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Handle login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'] ?? '';
    
    if (!empty($pin)) {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE is_active = 1");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        foreach ($users as $user) {
            if (password_verify($pin, $user['pin'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['permissions'] = $user['permissions'];
                
                // Update last login
                $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                logActivity('login', 'User logged in', $user['id']);
                
                header('Location: index.php');
                exit;
            }
        }
        
        $error = __('invalid_credentials');
    }
}

$lang = getCurrentLanguage();
$dir = getLanguageDirection($lang);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('login'); ?> - <?php echo __('app_name'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
        }
        
        .login-card {
            max-width: 400px;
            width: 100%;
            background: var(--gradient-card);
            border: 1px solid rgba(250, 204, 21, 0.3);
            border-radius: var(--radius-xl);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-accent);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .login-logo-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-accent);
            border-radius: var(--radius-lg);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--bg-primary);
            margin-bottom: var(--spacing-md);
            box-shadow: var(--shadow-accent);
        }
        
        .login-title {
            font-size: 1.75rem;
            margin-bottom: var(--spacing-sm);
        }
        
        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .pin-input-container {
            margin: var(--spacing-xl) 0;
        }
        
        .pin-display {
            display: flex;
            justify-content: center;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-xl);
        }
        
        .pin-dot {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(250, 204, 21, 0.3);
            transition: all var(--transition-base);
        }
        
        .pin-dot.filled {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            box-shadow: 0 0 10px rgba(250, 204, 21, 0.6);
        }
        
        .pin-keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
        }
        
        .pin-key {
            aspect-ratio: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: var(--radius-lg);
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-base);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .pin-key:hover {
            background: rgba(250, 204, 21, 0.1);
            border-color: var(--accent-primary);
            transform: scale(1.05);
        }
        
        .pin-key:active {
            transform: scale(0.95);
        }
        
        .pin-key.delete {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            font-size: 1.25rem;
        }
        
        .pin-key.delete:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: var(--error);
        }
        
        .language-selector {
            display: flex;
            justify-content: center;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-lg);
        }
        
        .lang-option-btn {
            padding: var(--spacing-sm) var(--spacing-md);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: var(--radius-full);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all var(--transition-base);
        }
        
        .lang-option-btn:hover,
        .lang-option-btn.active {
            background: var(--gradient-accent);
            color: var(--bg-primary);
            border-color: var(--accent-primary);
        }
    </style>
</head>
<body class="<?php echo $dir; ?>">
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <div class="login-logo-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <h1 class="login-title"><?php echo __('app_name'); ?></h1>
                <p class="login-subtitle"><?php echo __('login'); ?></p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="pin-input-container">
                    <div class="pin-display">
                        <div class="pin-dot" id="dot1"></div>
                        <div class="pin-dot" id="dot2"></div>
                        <div class="pin-dot" id="dot3"></div>
                        <div class="pin-dot" id="dot4"></div>
                        <div class="pin-dot" id="dot5"></div>
                        <div class="pin-dot" id="dot6"></div>
                    </div>
                    
                    <input type="hidden" name="pin" id="pinInput" value="">
                    
                    <div class="pin-keypad">
                        <button type="button" class="pin-key" data-key="1">1</button>
                        <button type="button" class="pin-key" data-key="2">2</button>
                        <button type="button" class="pin-key" data-key="3">3</button>
                        <button type="button" class="pin-key" data-key="4">4</button>
                        <button type="button" class="pin-key" data-key="5">5</button>
                        <button type="button" class="pin-key" data-key="6">6</button>
                        <button type="button" class="pin-key" data-key="7">7</button>
                        <button type="button" class="pin-key" data-key="8">8</button>
                        <button type="button" class="pin-key" data-key="9">9</button>
                        <button type="button" class="pin-key delete" data-key="delete">
                            <i class="fas fa-backspace"></i>
                        </button>
                        <button type="button" class="pin-key" data-key="0">0</button>
                        <button type="button" class="pin-key delete" data-key="clear">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-sign-in-alt"></i>
                        <?php echo __('login'); ?>
                    </button>
                </div>
            </form>
            
            <div class="language-selector">
                <?php foreach (getAvailableLanguages() as $code => $name): ?>
                    <a href="?set_lang=<?php echo $code; ?>" class="lang-option-btn <?php echo $lang == $code ? 'active' : ''; ?>">
                        <?php echo $name; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <script>
        // PIN input handling
        const pinInput = document.getElementById('pinInput');
        const dots = Array.from({ length: 6 }, (_, i) => document.getElementById(`dot${i + 1}`));
        const keys = document.querySelectorAll('.pin-key');
        const form = document.getElementById('loginForm');
        
        let currentPin = '';
        
        keys.forEach(key => {
            key.addEventListener('click', () => {
                const keyValue = key.dataset.key;
                
                if (keyValue === 'delete') {
                    currentPin = currentPin.slice(0, -1);
                } else if (keyValue === 'clear') {
                    currentPin = '';
                } else if (currentPin.length < 6) {
                    currentPin += keyValue;
                }
                
                updateDisplay();
                
                // Auto-submit if 6 digits entered
                if (currentPin.length === 6) {
                    pinInput.value = currentPin;
                    setTimeout(() => form.submit(), 300);
                }
            });
        });
        
        function updateDisplay() {
            dots.forEach((dot, index) => {
                if (index < currentPin.length) {
                    dot.classList.add('filled');
                } else {
                    dot.classList.remove('filled');
                }
            });
            pinInput.value = currentPin;
        }
        
        // Keyboard support
        document.addEventListener('keydown', (e) => {
            if (e.key >= '0' && e.key <= '9' && currentPin.length < 6) {
                currentPin += e.key;
                updateDisplay();
                
                if (currentPin.length === 6) {
                    pinInput.value = currentPin;
                    setTimeout(() => form.submit(), 300);
                }
            } else if (e.key === 'Backspace') {
                e.preventDefault();
                currentPin = currentPin.slice(0, -1);
                updateDisplay();
            } else if (e.key === 'Enter' && currentPin.length === 6) {
                e.preventDefault();
                form.submit();
            }
        });
    </script>
</body>
</html>

<?php
// Handle language change
if (isset($_GET['set_lang'])) {
    setLanguage($_GET['set_lang']);
    header('Location: login.php');
    exit;
}
?>
