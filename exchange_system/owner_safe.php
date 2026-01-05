<?php
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();

// Get selected owner
$owner_id = $_GET['owner_id'] ?? null;
$selectedOwner = null;

if ($owner_id) {
    $stmt = $db->prepare("SELECT * FROM transaction_owners WHERE id = ?");
    $stmt->execute([$owner_id]);
    $selectedOwner = $stmt->fetch();
}

// Handle transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_transaction') {
    $owner_id = intval($_POST['owner_id']);
    $type = sanitizeInput($_POST['transaction_type']);
    $amount = floatval($_POST['amount']);
    $currency_code = sanitizeInput($_POST['currency_code']);
    $description = sanitizeInput($_POST['description']);
    
    // Convert to IQD
    $amount_iqd = convertCurrency($amount, $currency_code, 'IQD');
    
    // Get current balance
    $stmt = $db->prepare("SELECT balance_iqd FROM transaction_owners WHERE id = ?");
    $stmt->execute([$owner_id]);
    $currentBalance = floatval($stmt->fetch()['balance_iqd']);
    
    $balance_before = $currentBalance;
    $balance_after = $type === 'deposit' ? $currentBalance + $amount_iqd : $currentBalance - $amount_iqd;
    
    // Insert transaction
    $stmt = $db->prepare("INSERT INTO owner_safe_transactions (owner_id, transaction_type, amount, currency_code, amount_iqd, balance_before, balance_after, description, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$owner_id, $type, $amount, $currency_code, $amount_iqd, $balance_before, $balance_after, $description, $_SESSION['user_id']])) {
        // Update owner balance
        $updateStmt = $db->prepare("UPDATE transaction_owners SET balance_iqd = ? WHERE id = ?");
        $updateStmt->execute([$balance_after, $owner_id]);
        
        logActivity('owner_safe_transaction', "Added $type transaction for owner ID: $owner_id");
        setFlashMessage(__('success_add'), 'success');
    } else {
        setFlashMessage(__('error_occurred'), 'error');
    }
    
    header("Location: owner_safe.php?owner_id=$owner_id");
    exit;
}

// Get all owners for dropdown
$stmt = $db->query("SELECT id, name FROM transaction_owners WHERE is_active = 1 ORDER BY name");
$owners = $stmt->fetchAll();

// Get transactions if owner is selected
$transactions = [];
if ($owner_id) {
    $stmt = $db->prepare("SELECT * FROM owner_safe_transactions WHERE owner_id = ? ORDER BY created_at DESC");
    $stmt->execute([$owner_id]);
    $transactions = $stmt->fetchAll();
}

// Get currencies
$stmt = $db->query("SELECT code, symbol, name_" . getCurrentLanguage() . " as name FROM currencies WHERE is_active = 1 ORDER BY code");
$currencies = $stmt->fetchAll();
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-vault"></i>
        <?php echo __('owner_safe_title'); ?>
    </h1>
    <?php if ($selectedOwner): ?>
    <div class="btn-group">
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus"></i>
            Add Transaction
        </button>
        <button class="btn btn-secondary" data-print>
            <i class="fas fa-print"></i>
            <?php echo __('print'); ?>
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Owner Selection -->
<div class="card">
    <div class="card-body">
        <form method="GET">
            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label class="form-label"><?php echo __('select_owner'); ?></label>
                    <select name="owner_id" class="form-select" onchange="this.form.submit()" required>
                        <option value=""><?php echo __('select_owner'); ?>...</option>
                        <?php foreach ($owners as $owner): ?>
                            <option value="<?php echo $owner['id']; ?>" <?php echo $owner_id == $owner['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($owner['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedOwner): ?>
<!-- Owner Balance Info -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-user"></i>
        </div>
        <div class="stat-value"><?php echo htmlspecialchars($selectedOwner['name']); ?></div>
        <div class="stat-label"><?php echo __('owner_name'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-value"><?php echo number_format($selectedOwner['balance_iqd'], 0); ?> IQD</div>
        <div class="stat-label"><?php echo __('current_balance'); ?> (<?php echo __('balance_iqd'); ?>)</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-value"><?php echo number_format($selectedOwner['balance_usd'], 2); ?> USD</div>
        <div class="stat-label"><?php echo __('current_balance'); ?> (<?php echo __('balance_usd'); ?>)</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="stat-value"><?php echo number_format($selectedOwner['total_sent'], 0); ?> IQD</div>
        <div class="stat-label"><?php echo __('total_sent'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-arrow-down"></i>
        </div>
        <div class="stat-value"><?php echo number_format($selectedOwner['total_received'], 0); ?> IQD</div>
        <div class="stat-label"><?php echo __('total_received'); ?></div>
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?php echo __('transaction_history'); ?></h3>
    </div>
    <div class="card-body">
        <?php if (count($transactions) > 0): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?php echo __('date'); ?></th>
                        <th><?php echo __('transaction_type'); ?></th>
                        <th><?php echo __('amount'); ?></th>
                        <th><?php echo __('currency'); ?></th>
                        <th><?php echo __('balance_before'); ?></th>
                        <th><?php echo __('balance_after'); ?></th>
                        <th><?php echo __('description'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $trans): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($trans['created_at'])); ?></td>
                        <td>
                            <?php if ($trans['transaction_type'] === 'deposit'): ?>
                                <span class="badge badge-success"><?php echo __('deposit'); ?></span>
                            <?php else: ?>
                                <span class="badge badge-danger"><?php echo __('withdrawal'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($trans['amount'], 2); ?></td>
                        <td><?php echo $trans['currency_code']; ?></td>
                        <td><?php echo number_format($trans['balance_before'], 0); ?> IQD</td>
                        <td><?php echo number_format($trans['balance_after'], 0); ?> IQD</td>
                        <td><?php echo htmlspecialchars($trans['description']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-center text-muted"><?php echo __('no_data'); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Add Transaction Modal -->
<div class="modal" id="transactionModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add Transaction</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add_transaction">
            <input type="hidden" name="owner_id" value="<?php echo $owner_id; ?>">
            
            <div class="form-group">
                <label class="form-label required"><?php echo __('transaction_type'); ?></label>
                <select name="transaction_type" class="form-select" required>
                    <option value="deposit"><?php echo __('deposit'); ?></option>
                    <option value="withdrawal"><?php echo __('withdrawal'); ?></option>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required"><?php echo __('amount'); ?></label>
                    <input type="number" name="amount" class="form-input" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required"><?php echo __('currency'); ?></label>
                    <select name="currency_code" class="form-select" required>
                        <?php foreach ($currencies as $curr): ?>
                            <option value="<?php echo $curr['code']; ?>"><?php echo $curr['code'] . ' - ' . $curr['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo __('description'); ?></label>
                <textarea name="description" class="form-textarea"></textarea>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?php echo __('save'); ?>
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                    <?php echo __('cancel'); ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openAddModal() {
    document.getElementById('transactionModal').classList.add('active');
}

function closeModal() {
    document.getElementById('transactionModal').classList.remove('active');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
