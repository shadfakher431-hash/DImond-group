<?php
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();

// Get statistics
$stats = [];

// Total offices
$stmt = $db->query("SELECT COUNT(*) as count FROM offices WHERE is_active = 1");
$stats['total_offices'] = $stmt->fetch()['count'];

// Total owners
$stmt = $db->query("SELECT COUNT(*) as count FROM transaction_owners WHERE is_active = 1");
$stats['total_owners'] = $stmt->fetch()['count'];

// Total users
$stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
$stats['total_users'] = $stmt->fetch()['count'];

// Today's transfers
$stmt = $db->query("SELECT COUNT(*) as count, COALESCE(SUM(amount_iqd), 0) as total FROM transfers WHERE DATE(transfer_date) = CURDATE()");
$todayTransfers = $stmt->fetch();
$stats['transfers_today'] = $todayTransfers['count'];
$stats['transfers_amount_today'] = $todayTransfers['total'];

// Today's expenses
$stmt = $db->query("SELECT COUNT(*) as count, COALESCE(SUM(amount_iqd), 0) as total FROM expenses WHERE DATE(expense_date) = CURDATE()");
$todayExpenses = $stmt->fetch();
$stats['expenses_today'] = $todayExpenses['count'];
$stats['expenses_amount_today'] = $todayExpenses['total'];

// Safe balances
$safeBalanceIQD = floatval(getSetting('safe_balance_iqd', 0));
$safeBalanceUSD = floatval(getSetting('safe_balance_usd', 0));

// Recent transfers
$stmt = $db->prepare("SELECT t.*, o.name as office_name FROM transfers t 
                      LEFT JOIN offices o ON t.office_id = o.id 
                      ORDER BY t.created_at DESC LIMIT 5");
$stmt->execute();
$recentTransfers = $stmt->fetchAll();

// Recent expenses
$stmt = $db->prepare("SELECT * FROM expenses ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recentExpenses = $stmt->fetchAll();

// Pending transfers count
$stmt = $db->query("SELECT COUNT(*) as count FROM transfers WHERE status = 'pending'");
$pendingTransfers = $stmt->fetch()['count'];
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-chart-line"></i>
        <?php echo __('dashboard'); ?>
    </h1>
    <div class="btn-group">
        <button class="btn btn-primary" onclick="location.reload()">
            <i class="fas fa-sync"></i>
            <?php echo __('refresh', $lang) ?? 'Refresh'; ?>
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-value"><?php echo number_format($stats['total_offices']); ?></div>
        <div class="stat-label"><?php echo __('total_offices'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-value"><?php echo number_format($stats['total_owners']); ?></div>
        <div class="stat-label"><?php echo __('total_owners'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-paper-plane"></i>
        </div>
        <div class="stat-value"><?php echo number_format($stats['transfers_today']); ?></div>
        <div class="stat-label"><?php echo __('total_transfers_today'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-inbox"></i>
        </div>
        <div class="stat-value"><?php echo number_format($pendingTransfers); ?></div>
        <div class="stat-label"><?php echo __('pending_transfers'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-value"><?php echo number_format($stats['expenses_today']); ?></div>
        <div class="stat-label"><?php echo __('total_expenses_today'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-value"><?php echo number_format($safeBalanceIQD, 0); ?> IQD</div>
        <div class="stat-label"><?php echo __('safe_balance_iqd'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-value"><?php echo number_format($safeBalanceUSD, 2); ?> USD</div>
        <div class="stat-label"><?php echo __('safe_balance_usd'); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="stat-value"><?php echo number_format($stats['transfers_amount_today'], 0); ?> IQD</div>
        <div class="stat-label"><?php echo __('total'); ?> - <?php echo __('today', $lang) ?? 'Today'; ?></div>
    </div>
</div>

<!-- Recent Transfers and Expenses -->
<div class="grid-2">
    <!-- Recent Transfers -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-paper-plane"></i>
                <?php echo __('recent_transfers'); ?>
            </h3>
        </div>
        <div class="card-body">
            <?php if (count($recentTransfers) > 0): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php echo __('receipt_no'); ?></th>
                            <th><?php echo __('sender_name'); ?></th>
                            <th><?php echo __('receiver_name'); ?></th>
                            <th><?php echo __('amount'); ?></th>
                            <th><?php echo __('status'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTransfers as $transfer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transfer['receipt_number']); ?></td>
                            <td><?php echo htmlspecialchars($transfer['sender_name']); ?></td>
                            <td><?php echo htmlspecialchars($transfer['receiver_name']); ?></td>
                            <td><?php echo number_format($transfer['amount'], 2) . ' ' . $transfer['currency_code']; ?></td>
                            <td>
                                <?php if ($transfer['status'] === 'completed'): ?>
                                    <span class="badge badge-success"><?php echo __('completed'); ?></span>
                                <?php elseif ($transfer['status'] === 'pending'): ?>
                                    <span class="badge badge-warning"><?php echo __('pending'); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-danger"><?php echo __('cancelled'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-md">
                <a href="send_transfer.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye"></i>
                    <?php echo __('view', $lang) ?? 'View All'; ?>
                </a>
            </div>
            <?php else: ?>
            <p class="text-center text-muted"><?php echo __('no_data'); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Expenses -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-receipt"></i>
                <?php echo __('recent_expenses'); ?>
            </h3>
        </div>
        <div class="card-body">
            <?php if (count($recentExpenses) > 0): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php echo __('expense_category'); ?></th>
                            <th><?php echo __('amount'); ?></th>
                            <th><?php echo __('expense_date'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentExpenses as $expense): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($expense['category']); ?></td>
                            <td><?php echo number_format($expense['amount'], 2) . ' ' . $expense['currency_code']; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($expense['expense_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-md">
                <a href="expenses.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye"></i>
                    <?php echo __('view', $lang) ?? 'View All'; ?>
                </a>
            </div>
            <?php else: ?>
            <p class="text-center text-muted"><?php echo __('no_data'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mt-xl">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bolt"></i>
            <?php echo __('quick_actions', $lang) ?? 'Quick Actions'; ?>
        </h3>
    </div>
    <div class="card-body">
        <div class="grid-4">
            <a href="send_transfer.php" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i>
                <?php echo __('menu_send_transfer'); ?>
            </a>
            <a href="incoming_transfers.php" class="btn btn-secondary">
                <i class="fas fa-inbox"></i>
                <?php echo __('menu_incoming_transfers'); ?>
            </a>
            <a href="exchange_log.php" class="btn btn-secondary">
                <i class="fas fa-exchange-alt"></i>
                <?php echo __('menu_exchange_log'); ?>
            </a>
            <a href="expenses.php" class="btn btn-secondary">
                <i class="fas fa-receipt"></i>
                <?php echo __('menu_expenses'); ?>
            </a>
            <a href="cash_management.php" class="btn btn-secondary">
                <i class="fas fa-money-bill-wave"></i>
                <?php echo __('menu_cash_management'); ?>
            </a>
            <a href="owners.php" class="btn btn-secondary">
                <i class="fas fa-users"></i>
                <?php echo __('menu_owners'); ?>
            </a>
            <a href="offices.php" class="btn btn-secondary">
                <i class="fas fa-building"></i>
                <?php echo __('menu_offices'); ?>
            </a>
            <a href="reports.php" class="btn btn-secondary">
                <i class="fas fa-chart-line"></i>
                <?php echo __('menu_reports'); ?>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
