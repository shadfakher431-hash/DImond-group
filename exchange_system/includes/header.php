<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/language_helper.php';

requireLogin();

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$user_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_role = $_SESSION['role'] ?? 'user';
$lang = getCurrentLanguage();
$dir = getLanguageDirection($lang);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('app_name'); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="<?php echo $dir; ?>">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <div class="logo-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <h1 class="brand-name"><?php echo __('app_name'); ?></h1>
            </div>
            
            <button class="mobile-toggle" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="navbar-menu" id="navbarMenu">
                <a href="index.php" class="nav-link <?php echo $current_page == 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span><?php echo __('dashboard'); ?></span>
                </a>
                
                <?php if (hasPermission('offices') || isAdmin()): ?>
                <a href="offices.php" class="nav-link <?php echo $current_page == 'offices' ? 'active' : ''; ?>">
                    <i class="fas fa-building"></i>
                    <span><?php echo __('menu_offices'); ?></span>
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('owners') || isAdmin()): ?>
                <a href="owners.php" class="nav-link <?php echo $current_page == 'owners' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span><?php echo __('menu_owners'); ?></span>
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('owner_safe') || isAdmin()): ?>
                <a href="owner_safe.php" class="nav-link <?php echo $current_page == 'owner_safe' ? 'active' : ''; ?>">
                    <i class="fas fa-vault"></i>
                    <span><?php echo __('menu_owner_safe'); ?></span>
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('expenses') || isAdmin()): ?>
                <a href="expenses.php" class="nav-link <?php echo $current_page == 'expenses' ? 'active' : ''; ?>">
                    <i class="fas fa-receipt"></i>
                    <span><?php echo __('menu_expenses'); ?></span>
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('users') || isAdmin()): ?>
                <a href="users.php" class="nav-link <?php echo $current_page == 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-user-shield"></i>
                    <span><?php echo __('menu_users'); ?></span>
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('transfers') || isAdmin()): ?>
                <a href="send_transfer.php" class="nav-link <?php echo $current_page == 'send_transfer' ? 'active' : ''; ?>">
                    <i class="fas fa-paper-plane"></i>
                    <span><?php echo __('menu_send_transfer'); ?></span>
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('transfers') || isAdmin()): ?>
                <a href="incoming_transfers.php" class="nav-link <?php echo $current_page == 'incoming_transfers' ? 'active' : ''; ?>">
                    <i class="fas fa-inbox"></i>
                    <span><?php echo __('menu_incoming_transfers'); ?></span>
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('exchange') || isAdmin()): ?>
                <a href="exchange_log.php" class="nav-link <?php echo $current_page == 'exchange_log' ? 'active' : ''; ?>">
                    <i class="fas fa-exchange-alt"></i>
                    <span><?php echo __('menu_exchange_log'); ?></span>
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('cash') || isAdmin()): ?>
                <a href="cash_management.php" class="nav-link <?php echo $current_page == 'cash_management' ? 'active' : ''; ?>">
                    <i class="fas fa-money-bill-wave"></i>
                    <span><?php echo __('menu_cash_management'); ?></span>
                </a>
                <?php endif; ?>
                
                <?php if (hasPermission('reports') || isAdmin()): ?>
                <a href="reports.php" class="nav-link <?php echo $current_page == 'reports' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span><?php echo __('menu_reports'); ?></span>
                </a>
                <?php endif; ?>
            </div>
            
            <div class="navbar-actions">
                <!-- Language Switcher -->
                <div class="language-switcher">
                    <button class="lang-btn" id="langBtn">
                        <i class="fas fa-language"></i>
                        <span><?php echo getLanguageName($lang); ?></span>
                    </button>
                    <div class="lang-dropdown" id="langDropdown">
                        <?php foreach (getAvailableLanguages() as $code => $name): ?>
                            <a href="?set_lang=<?php echo $code; ?>" class="lang-option <?php echo $lang == $code ? 'active' : ''; ?>">
                                <?php echo $name; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="user-menu">
                    <button class="user-btn" id="userBtn">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <?php if (isAdmin()): ?>
                        <a href="settings.php" class="user-option">
                            <i class="fas fa-cog"></i>
                            <?php echo __('settings'); ?>
                        </a>
                        <?php endif; ?>
                        <a href="logout.php" class="user-option">
                            <i class="fas fa-sign-out-alt"></i>
                            <?php echo __('logout'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-container">
            <?php 
            // Display flash messages
            $flash = getFlashMessage();
            if ($flash): 
            ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <i class="fas fa-<?php echo $flash['type'] == 'success' ? 'check-circle' : ($flash['type'] == 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
            <?php endif; ?>

<?php
// Handle language change
if (isset($_GET['set_lang'])) {
    setLanguage($_GET['set_lang']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
