<?php
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = sanitizeInput($_POST['name']);
        $phone = sanitizeInput($_POST['phone']);
        $id_number = sanitizeInput($_POST['id_number']);
        $address = sanitizeInput($_POST['address']);
        $notes = sanitizeInput($_POST['notes']);
        
        $stmt = $db->prepare("INSERT INTO transaction_owners (name, phone, id_number, address, notes) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $phone, $id_number, $address, $notes])) {
            logActivity('add_owner', "Added owner: $name");
            setFlashMessage(__('success_add'), 'success');
        } else {
            setFlashMessage(__('error_occurred'), 'error');
        }
        header('Location: owners.php');
        exit;
    }
    
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $name = sanitizeInput($_POST['name']);
        $phone = sanitizeInput($_POST['phone']);
        $id_number = sanitizeInput($_POST['id_number']);
        $address = sanitizeInput($_POST['address']);
        $notes = sanitizeInput($_POST['notes']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE transaction_owners SET name=?, phone=?, id_number=?, address=?, notes=?, is_active=? WHERE id=?");
        if ($stmt->execute([$name, $phone, $id_number, $address, $notes, $is_active, $id])) {
            logActivity('edit_owner', "Edited owner: $name");
            setFlashMessage(__('success_update'), 'success');
        } else {
            setFlashMessage(__('error_occurred'), 'error');
        }
        header('Location: owners.php');
        exit;
    }
    
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $db->prepare("DELETE FROM transaction_owners WHERE id=?");
        if ($stmt->execute([$id])) {
            logActivity('delete_owner', "Deleted owner ID: $id");
            setFlashMessage(__('success_delete'), 'success');
        } else {
            setFlashMessage(__('error_occurred'), 'error');
        }
        header('Location: owners.php');
        exit;
    }
}

// Get all owners
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM transaction_owners WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR phone LIKE ? OR id_number LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

$query .= " ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$owners = $stmt->fetchAll();
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-users"></i>
        <?php echo __('owners_title'); ?>
    </h1>
    <div class="btn-group">
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus"></i>
            <?php echo __('add_owner'); ?>
        </button>
        <button class="btn btn-secondary" data-print>
            <i class="fas fa-print"></i>
            <?php echo __('print'); ?>
        </button>
    </div>
</div>

<!-- Search Bar -->
<div class="card">
    <div class="card-body">
        <form method="GET" class="search-bar">
            <input type="text" name="search" class="search-input" placeholder="<?php echo __('search'); ?>..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
                <?php echo __('search'); ?>
            </button>
            <?php if ($search): ?>
            <a href="owners.php" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Clear
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Owners Table -->
<div class="card">
    <div class="card-body">
        <?php if (count($owners) > 0): ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?php echo __('owner_name'); ?></th>
                        <th><?php echo __('owner_phone'); ?></th>
                        <th><?php echo __('id_number'); ?></th>
                        <th><?php echo __('balance_iqd'); ?></th>
                        <th><?php echo __('balance_usd'); ?></th>
                        <th><?php echo __('total_sent'); ?></th>
                        <th><?php echo __('total_received'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($owners as $owner): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($owner['name']); ?></td>
                        <td><?php echo htmlspecialchars($owner['phone']); ?></td>
                        <td><?php echo htmlspecialchars($owner['id_number']); ?></td>
                        <td><?php echo number_format($owner['balance_iqd'], 0); ?></td>
                        <td><?php echo number_format($owner['balance_usd'], 2); ?></td>
                        <td><?php echo number_format($owner['total_sent'], 0); ?></td>
                        <td><?php echo number_format($owner['total_received'], 0); ?></td>
                        <td>
                            <?php if ($owner['is_active']): ?>
                                <span class="badge badge-success"><?php echo __('active'); ?></span>
                            <?php else: ?>
                                <span class="badge badge-danger"><?php echo __('inactive'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="owner_safe.php?owner_id=<?php echo $owner['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-vault"></i>
                                </a>
                                <button class="btn btn-sm btn-secondary" onclick='openEditModal(<?php echo json_encode($owner); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteOwner(<?php echo $owner['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
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

<!-- Add/Edit Modal -->
<div class="modal" id="ownerModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle"><?php echo __('add_owner'); ?></h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" id="ownerForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="ownerId">
            
            <div class="form-group">
                <label class="form-label required"><?php echo __('owner_name'); ?></label>
                <input type="text" name="name" class="form-input" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><?php echo __('owner_phone'); ?></label>
                    <input type="text" name="phone" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo __('id_number'); ?></label>
                    <input type="text" name="id_number" class="form-input">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo __('office_address'); ?></label>
                <textarea name="address" class="form-textarea"></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo __('notes'); ?></label>
                <textarea name="notes" class="form-textarea"></textarea>
            </div>
            
            <div class="form-group" id="statusGroup" style="display: none;">
                <label class="form-label">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <?php echo __('active'); ?>
                </label>
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
    document.getElementById('modalTitle').textContent = '<?php echo __('add_owner'); ?>';
    document.getElementById('formAction').value = 'add';
    document.getElementById('ownerForm').reset();
    document.getElementById('statusGroup').style.display = 'none';
    document.getElementById('ownerModal').classList.add('active');
}

function openEditModal(owner) {
    document.getElementById('modalTitle').textContent = '<?php echo __('edit_owner'); ?>';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('ownerId').value = owner.id;
    document.querySelector('[name="name"]').value = owner.name;
    document.querySelector('[name="phone"]').value = owner.phone || '';
    document.querySelector('[name="id_number"]').value = owner.id_number || '';
    document.querySelector('[name="address"]').value = owner.address || '';
    document.querySelector('[name="notes"]').value = owner.notes || '';
    document.querySelector('[name="is_active"]').checked = owner.is_active == 1;
    document.getElementById('statusGroup').style.display = 'block';
    document.getElementById('ownerModal').classList.add('active');
}

function closeModal() {
    document.getElementById('ownerModal').classList.remove('active');
}

function deleteOwner(id) {
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
