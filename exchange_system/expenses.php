<?php
require_once __DIR__ . '/includes/header.php';
$db = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $category = sanitizeInput($_POST['category']);
        $amount = floatval($_POST['amount']);
        $currency_code = sanitizeInput($_POST['currency_code']);
        $expense_date = sanitizeInput($_POST['expense_date']);
        $receipt_number = sanitizeInput($_POST['receipt_number']);
        $description = sanitizeInput($_POST['description']);
        
        $amount_iqd = convertCurrency($amount, $currency_code, 'IQD');
        
        $stmt = $db->prepare("INSERT INTO expenses (category, amount, currency_code, amount_iqd, expense_date, receipt_number, description, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$category, $amount, $currency_code, $amount_iqd, $expense_date, $receipt_number, $description, $_SESSION['user_id']])) {
            logActivity('add_expense', "Added expense: $category - $amount $currency_code");
            setFlashMessage(__('success_add'), 'success');
        } else {
            setFlashMessage(__('error_occurred'), 'error');
        }
        header('Location: expenses.php');
        exit;
    }
    
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $db->prepare("DELETE FROM expenses WHERE id=?");
        if ($stmt->execute([$id])) {
            logActivity('delete_expense', "Deleted expense ID: $id");
            setFlashMessage(__('success_delete'), 'success');
        }
        header('Location: expenses.php');
        exit;
    }
}

// Get expenses
$date_filter = $_GET['date'] ?? date('Y-m-d');
$stmt = $db->prepare("SELECT * FROM expenses WHERE DATE(expense_date) = ? ORDER BY expense_date DESC");
$stmt->execute([$date_filter]);
$expenses = $stmt->fetchAll();

// Calculate totals
$total_iqd = array_sum(array_column($expenses, 'amount_iqd'));

// Get currencies
$stmt = $db->query("SELECT code, symbol, name_" . getCurrentLanguage() . " as name FROM currencies WHERE is_active = 1 ORDER BY code");
$currencies = $stmt->fetchAll();

// Get expense categories
$categories = ['Salary', 'Rent', 'Utilities', 'Supplies', 'Transportation', 'Other'];
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-receipt"></i>
        <?php echo __('expenses_title'); ?>
    </h1>
    <div class="btn-group">
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus"></i>
            <?php echo __('add_expense'); ?>
        </button>
        <button class="btn btn-secondary" data-print>
            <i class="fas fa-print"></i>
            <?php echo __('print'); ?>
        </button>
    </div>
</div>

<!-- Date Filter -->
<div class="card">
    <div class="card-body">
        <form method="GET" class="search-bar">
            <input type="date" name="date" class="form-input" value="<?php echo $date_filter; ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i>
                Filter
            </button>
            <a href="expenses.php" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Today
            </a>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-calculator"></i>
        </div>
        <div class="stat-value"><?php echo count($expenses); ?></div>
        <div class="stat-label"><?php echo __('total'); ?> <?php echo __('expenses_title'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-value"><?php echo number_format($total_iqd, 0); ?> IQD</div>
        <div class="stat-label"><?php echo __('total'); ?> <?php echo __('amount'); ?></div>
    </div>
</div>

<!-- Expenses Table -->
<div class="card">
    <div class="card-body">
        <?php if (count($expenses) > 0): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?php echo __('expense_date'); ?></th>
                        <th><?php echo __('expense_category'); ?></th>
                        <th><?php echo __('amount'); ?></th>
                        <th><?php echo __('currency'); ?></th>
                        <th><?php echo __('amount'); ?> (IQD)</th>
                        <th><?php echo __('receipt_number'); ?></th>
                        <th><?php echo __('description'); ?></th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?php echo date('Y-m-d', strtotime($expense['expense_date'])); ?></td>
                        <td><?php echo htmlspecialchars($expense['category']); ?></td>
                        <td><?php echo number_format($expense['amount'], 2); ?></td>
                        <td><?php echo $expense['currency_code']; ?></td>
                        <td><?php echo number_format($expense['amount_iqd'], 0); ?></td>
                        <td><?php echo htmlspecialchars($expense['receipt_number']); ?></td>
                        <td><?php echo htmlspecialchars($expense['description']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="deleteExpense(<?php echo $expense['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right"><?php echo __('total'); ?>:</th>
                        <th><?php echo number_format($total_iqd, 0); ?> IQD</th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <p class="text-center text-muted"><?php echo __('no_data'); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Add Modal -->
<div class="modal" id="expenseModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><?php echo __('add_expense'); ?></h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required"><?php echo __('expense_category'); ?></label>
                    <select name="category" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label required"><?php echo __('expense_date'); ?></label>
                    <input type="date" name="expense_date" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
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
                            <option value="<?php echo $curr['code']; ?>"><?php echo $curr['code']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo __('receipt_number'); ?></label>
                <input type="text" name="receipt_number" class="form-input">
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

<script>
function openAddModal() {
    document.getElementById('expenseModal').classList.add('active');
}

function closeModal() {
    document.getElementById('expenseModal').classList.remove('active');
}

function deleteExpense(id) {
    if (confirm('<?php echo __('confirm_delete'); ?>')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
