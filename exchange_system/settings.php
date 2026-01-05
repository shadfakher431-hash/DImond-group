<?php
require_once __DIR__ . '/includes/header.php';
requireAdmin();

$db = getDBConnection();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'update_settings') {
        updateSetting('company_name', sanitizeInput($_POST['company_name']));
        updateSetting('company_name_ku', sanitizeInput($_POST['company_name_ku']));
        updateSetting('company_name_ar', sanitizeInput($_POST['company_name_ar']));
        updateSetting('default_language', sanitizeInput($_POST['default_language']));
        updateSetting('default_commission_rate', floatval($_POST['default_commission_rate']));
        
        logActivity('update_settings', 'Updated system settings');
        setFlashMessage('Settings updated successfully', 'success');
        header('Location: settings.php');
        exit;
    }
    
    if ($action === 'backup_database') {
        $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backup_path = __DIR__ . '/exports/' . $backup_file;
        
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            DB_USER,
            DB_PASS,
            DB_HOST,
            DB_NAME,
            $backup_path
        );
        
        exec($command, $output, $return_var);
        
        if ($return_var === 0 && file_exists($backup_path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $backup_file . '"');
            header('Content-Length: ' . filesize($backup_path));
            readfile($backup_path);
            unlink($backup_path);
            exit;
        } else {
            setFlashMessage('Backup failed', 'error');
        }
    }
}

// Get current settings
$company_name = getSetting('company_name', 'Diamond Money Exchange');
$company_name_ku = getSetting('company_name_ku', 'گۆڕینی پارەی دایمۆند');
$company_name_ar = getSetting('company_name_ar', 'صرافة الماس');
$default_language = getSetting('default_language', 'ku');
$default_commission = getSetting('default_commission_rate', '2.5');
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-cog"></i>
        <?php echo __('settings_title'); ?>
    </h1>
</div>

<div class="grid-2">
    <!-- Company Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php echo __('company_info'); ?></h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="update_settings">
                
                <div class="form-group">
                    <label class="form-label"><?php echo __('company_name'); ?> (English)</label>
                    <input type="text" name="company_name" class="form-input" value="<?php echo htmlspecialchars($company_name); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo __('company_name'); ?> (Kurdish)</label>
                    <input type="text" name="company_name_ku" class="form-input" value="<?php echo htmlspecialchars($company_name_ku); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo __('company_name'); ?> (Arabic)</label>
                    <input type="text" name="company_name_ar" class="form-input" value="<?php echo htmlspecialchars($company_name_ar); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo __('default_language'); ?></label>
                    <select name="default_language" class="form-select">
                        <option value="en" <?php echo $default_language === 'en' ? 'selected' : ''; ?>>English</option>
                        <option value="ku" <?php echo $default_language === 'ku' ? 'selected' : ''; ?>>کوردی</option>
                        <option value="ar" <?php echo $default_language === 'ar' ? 'selected' : ''; ?>>عربي</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo __('default_commission'); ?> (%)</label>
                    <input type="number" name="default_commission_rate" class="form-input" step="0.01" value="<?php echo $default_commission; ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?php echo __('save'); ?>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Database Backup -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php echo __('database_backup'); ?></h3>
        </div>
        <div class="card-body">
            <p>Backup and restore your database.</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="backup_database">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-download"></i>
                    <?php echo __('backup_database'); ?>
                </button>
            </form>
            
            <hr style="margin: 20px 0;">
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="restore_database">
                <div class="form-group">
                    <label class="form-label">Restore from File</label>
                    <input type="file" name="backup_file" class="form-input" accept=".sql">
                </div>
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-upload"></i>
                    <?php echo __('restore_database'); ?>
                </button>
            </form>
            
            <div class="alert alert-warning mt-md">
                <i class="fas fa-exclamation-triangle"></i>
                Warning: Restoring will overwrite all existing data!
            </div>
        </div>
    </div>
</div>

<!-- System Information -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">System Information</h3>
    </div>
    <div class="card-body">
        <div class="grid-3">
            <div>
                <strong>PHP Version:</strong> <?php echo phpversion(); ?>
            </div>
            <div>
                <strong>Database:</strong> MySQL/MariaDB
            </div>
            <div>
                <strong>System Version:</strong> <?php echo APP_VERSION; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
