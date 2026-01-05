<?php
require_once __DIR__ . '/includes/header.php';
requireAdmin(); // Only admins can manage users

$db = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $username = sanitizeInput($_POST['username']);
        $pin = $_POST['pin'];
        $full_name = sanitizeInput($_POST['full_name']);
        $role = sanitizeInput($_POST['role']);
        $permissions = json_encode($_POST['permissions'] ?? []);
        
        $hashed_pin = password_hash($pin, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (username, pin, full_name, role, permissions) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$username, $hashed_pin, $full_name, $role, $permissions])) {
            logActivity('add_user', "Added user: $username");
            setFlashMessage(__('success_add'), 'success');
        } else {
            setFlashMessage(__('error_occurred'), 'error');
        }
        header('Location: users.php');
        exit;
    }
    
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $username = sanitizeInput($_POST['username']);
        $full_name = sanitizeInput($_POST['full_name']);
        $role = sanitizeInput($_POST['role']);
        $permissions = json_encode($_POST['permissions'] ?? []);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE users SET username=?, full_name=?, role=?, permissions=?, is_active=? WHERE id=?");
        if ($stmt->execute([$username, $full_name, $role, $permissions, $is_active, $id])) {
            logActivity('edit_user', "Edited user: $username");
            setFlashMessage(__('success_update'), 'success');
        }
        header('Location: users.php');
        exit;
    }
    
    if ($action === 'change_pin') {
        $id = intval($_POST['id']);
        $new_pin = $_POST['new_pin'];
        $hashed_pin = password_hash($new_pin, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE users SET pin=? WHERE id=?");
        if ($stmt->execute([$hashed_pin, $id])) {
            logActivity('change_pin', "Changed PIN for user ID: $id");
            setFlashMessage('PIN changed successfully', 'success');
        }
        header('Location: users.php');
        exit;
    }
}

// Get all users
$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$permissions_list = [
    'offices' => 'Offices Management',
    'owners' => 'Owners Management',
    'owner_safe' => 'Owner Safe',
    'expenses' => 'Expenses',
    'transfers' => 'Transfers',
    'exchange' => 'Currency Exchange',
    'cash' => 'Cash Management',
    'reports' => 'Reports'
];
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-user-shield"></i>
        <?php echo __('users_title'); ?>
    </h1>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i>
        <?php echo __('add_user'); ?>
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?php echo __('username'); ?></th>
                        <th><?php echo __('full_name'); ?></th>
                        <th><?php echo __('role'); ?></th>
                        <th><?php echo __('last_login'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><span class="badge badge-primary"><?php echo $user['role']; ?></span></td>
                        <td><?php echo $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge badge-success"><?php echo __('active'); ?></span>
                            <?php else: ?>
                                <span class="badge badge-danger"><?php echo __('inactive'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-warning" onclick='openPinModal(<?php echo $user['id']; ?>)'>
                                    <i class="fas fa-key"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary" onclick='openEditModal(<?php echo json_encode($user); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle"><?php echo __('add_user'); ?></h3>
            <button class="modal-close" onclick="closeModal('userModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="userId">
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required"><?php echo __('username'); ?></label>
                    <input type="text" name="username" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required"><?php echo __('full_name'); ?></label>
                    <input type="text" name="full_name" class="form-input" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group" id="pinGroup">
                    <label class="form-label required"><?php echo __('pin'); ?></label>
                    <input type="password" name="pin" class="form-input" minlength="4" maxlength="6">
                </div>
                
                <div class="form-group">
                    <label class="form-label required"><?php echo __('role'); ?></label>
                    <select name="role" class="form-select" required>
                        <option value="user">User</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo __('permissions'); ?></label>
                <?php foreach ($permissions_list as $key => $label): ?>
                    <label style="display: block; margin: 5px 0;">
                        <input type="checkbox" name="permissions[<?php echo $key; ?>]" value="1">
                        <?php echo $label; ?>
                    </label>
                <?php endforeach; ?>
            </div>
            
            <div class="form-group" id="statusGroup" style="display: none;">
                <label>
                    <input type="checkbox" name="is_active" value="1" checked>
                    <?php echo __('active'); ?>
                </label>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?php echo __('save'); ?>
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('userModal')">
                    <i class="fas fa-times"></i>
                    <?php echo __('cancel'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Change PIN Modal -->
<div class="modal" id="pinModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title"><?php echo __('change_pin'); ?></h3>
            <button class="modal-close" onclick="closeModal('pinModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="change_pin">
            <input type="hidden" name="id" id="pinUserId">
            
            <div class="form-group">
                <label class="form-label required">New PIN</label>
                <input type="password" name="new_pin" class="form-input" minlength="4" maxlength="6" required>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?php echo __('save'); ?>
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('pinModal')">
                    <i class="fas fa-times"></i>
                    <?php echo __('cancel'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('formAction').value = 'add';
    document.getElementById('modalTitle').textContent = '<?php echo __('add_user'); ?>';
    document.getElementById('pinGroup').style.display = 'block';
    document.getElementById('statusGroup').style.display = 'none';
    document.querySelector('form').reset();
    document.getElementById('userModal').classList.add('active');
}

function openEditModal(user) {
    document.getElementById('formAction').value = 'edit';
    document.getElementById('modalTitle').textContent = '<?php echo __('edit_user'); ?>';
    document.getElementById('userId').value = user.id;
    document.querySelector('[name="username"]').value = user.username;
    document.querySelector('[name="full_name"]').value = user.full_name;
    document.querySelector('[name="role"]').value = user.role;
    document.querySelector('[name="is_active"]').checked = user.is_active == 1;
    document.getElementById('pinGroup').style.display = 'none';
    document.getElementById('statusGroup').style.display = 'block';
    
    // Set permissions
    const perms = JSON.parse(user.permissions || '{}');
    document.querySelectorAll('[name^="permissions"]').forEach(cb => {
        const key = cb.name.match(/\[(.*?)\]/)[1];
        cb.checked = perms[key] === true;
    });
    
    document.getElementById('userModal').classList.add('active');
}

function openPinModal(userId) {
    document.getElementById('pinUserId').value = userId;
    document.getElementById('pinModal').classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
