<?php
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = sanitizeInput($_POST['name']);
        $code = sanitizeInput($_POST['code']);
        $phone = sanitizeInput($_POST['phone']);
        $address = sanitizeInput($_POST['address']);
        $contact_person = sanitizeInput($_POST['contact_person']);
        $commission_rate = floatval($_POST['commission_rate']);
        $notes = sanitizeInput($_POST['notes']);
        
        $stmt = $db->prepare("INSERT INTO offices (name, code, phone, address, contact_person, commission_rate, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $code, $phone, $address, $contact_person, $commission_rate, $notes])) {
            logActivity('add_office', "Added office: $name");
            setFlashMessage(__('success_add'), 'success');
        } else {
            setFlashMessage(__('error_occurred'), 'error');
        }
        header('Location: offices.php');
        exit;
    }
    
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $name = sanitizeInput($_POST['name']);
        $code = sanitizeInput($_POST['code']);
        $phone = sanitizeInput($_POST['phone']);
        $address = sanitizeInput($_POST['address']);
        $contact_person = sanitizeInput($_POST['contact_person']);
        $commission_rate = floatval($_POST['commission_rate']);
        $notes = sanitizeInput($_POST['notes']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE offices SET name=?, code=?, phone=?, address=?, contact_person=?, commission_rate=?, notes=?, is_active=? WHERE id=?");
        if ($stmt->execute([$name, $code, $phone, $address, $contact_person, $commission_rate, $notes, $is_active, $id])) {
            logActivity('edit_office', "Edited office: $name");
            setFlashMessage(__('success_update'), 'success');
        } else {
            setFlashMessage(__('error_occurred'), 'error');
        }
        header('Location: offices.php');
        exit;
    }
    
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $db->prepare("DELETE FROM offices WHERE id=?");
        if ($stmt->execute([$id])) {
            logActivity('delete_office', "Deleted office ID: $id");
            setFlashMessage(__('success_delete'), 'success');
        } else {
            setFlashMessage(__('error_occurred'), 'error');
        }
        header('Location: offices.php');
        exit;
    }
}

// Get all offices
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM offices WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR code LIKE ? OR phone LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

$query .= " ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$offices = $stmt->fetchAll();
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-building"></i>
        <?php echo __('offices_title'); ?>
    </h1>
    <div class="btn-group">
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus"></i>
            <?php echo __('add_office'); ?>
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
            <a href="offices.php" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                <?php echo __('clear', $lang) ?? 'Clear'; ?>
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Offices Table -->
<div class="card">
    <div class="card-body">
        <?php if (count($offices) > 0): ?>
        <div class="table-container">
            <table class="table" id="officesTable">
                <thead>
                    <tr>
                        <th><?php echo __('office_name'); ?></th>
                        <th><?php echo __('office_code'); ?></th>
                        <th><?php echo __('office_phone'); ?></th>
                        <th><?php echo __('contact_person'); ?></th>
                        <th><?php echo __('commission_rate'); ?> (%)</th>
                        <th><?php echo __('balance_iqd'); ?></th>
                        <th><?php echo __('balance_usd'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($offices as $office): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($office['name']); ?></td>
                        <td><?php echo htmlspecialchars($office['code']); ?></td>
                        <td><?php echo htmlspecialchars($office['phone']); ?></td>
                        <td><?php echo htmlspecialchars($office['contact_person']); ?></td>
                        <td><?php echo number_format($office['commission_rate'], 2); ?>%</td>
                        <td><?php echo number_format($office['balance_iqd'], 0); ?></td>
                        <td><?php echo number_format($office['balance_usd'], 2); ?></td>
                        <td>
                            <?php if ($office['is_active']): ?>
                                <span class="badge badge-success"><?php echo __('active'); ?></span>
                            <?php else: ?>
                                <span class="badge badge-danger"><?php echo __('inactive'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-secondary" onclick='openEditModal(<?php echo json_encode($office); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteOffice(<?php echo $office['id']; ?>)">
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
<div class="modal" id="officeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle"><?php echo __('add_office'); ?></h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" id="officeForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="officeId">
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required"><?php echo __('office_name'); ?></label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required"><?php echo __('office_code'); ?></label>
                    <input type="text" name="code" class="form-input" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><?php echo __('office_phone'); ?></label>
                    <input type="text" name="phone" class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo __('contact_person'); ?></label>
                    <input type="text" name="contact_person" class="form-input">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo __('office_address'); ?></label>
                <textarea name="address" class="form-textarea"></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label"><?php echo __('commission_rate'); ?> (%)</label>
                <input type="number" name="commission_rate" class="form-input" step="0.01" value="0.00">
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
    document.getElementById('modalTitle').textContent = '<?php echo __('add_office'); ?>';
    document.getElementById('formAction').value = 'add';
    document.getElementById('officeForm').reset();
    document.getElementById('statusGroup').style.display = 'none';
    document.getElementById('officeModal').classList.add('active');
}

function openEditModal(office) {
    document.getElementById('modalTitle').textContent = '<?php echo __('edit_office'); ?>';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('officeId').value = office.id;
    document.querySelector('[name="name"]').value = office.name;
    document.querySelector('[name="code"]').value = office.code;
    document.querySelector('[name="phone"]').value = office.phone || '';
    document.querySelector('[name="contact_person"]').value = office.contact_person || '';
    document.querySelector('[name="address"]').value = office.address || '';
    document.querySelector('[name="commission_rate"]').value = office.commission_rate;
    document.querySelector('[name="notes"]').value = office.notes || '';
    document.querySelector('[name="is_active"]').checked = office.is_active == 1;
    document.getElementById('statusGroup').style.display = 'block';
    document.getElementById('officeModal').classList.add('active');
}

function closeModal() {
    document.getElementById('officeModal').classList.remove('active');
}

function deleteOffice(id) {
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
