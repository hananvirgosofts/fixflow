<?php
require __DIR__ . '/includes/init.php';
require_role(['admin']);

$errors = [];
$editing = null;
$old = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'specialty' => '',
    'is_active' => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $errors[] = 'The form expired. Refresh the page and try again.';
    } else {
        $action = isset($_POST['action']) ? (string) $_POST['action'] : '';
        if ($action === 'toggle') {
            $workerId = (int) (isset($_POST['id']) ? $_POST['id'] : 0);
            $stmt = db()->prepare("UPDATE users SET is_active = IF(is_active = 1, 0, 1) WHERE id = ? AND role = 'worker'");
            $stmt->execute([$workerId]);
            if ($stmt->rowCount() === 0) {
                $errors[] = 'That worker account could not be changed.';
            } else {
                flash('success', 'Worker account updated.');
                redirect('workers.php');
            }
        } else {
            $workerId = (int) (isset($_POST['id']) ? $_POST['id'] : 0);
            $old['full_name'] = clean_line(isset($_POST['full_name']) ? $_POST['full_name'] : '', 120);
            $old['email'] = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
            $old['phone'] = clean_line(isset($_POST['phone']) ? $_POST['phone'] : '', 30);
            $old['specialty'] = isset($_POST['specialty']) ? (string) $_POST['specialty'] : '';
            $old['is_active'] = isset($_POST['is_active']) && (string) $_POST['is_active'] === '1' ? 1 : 0;
            $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
            $specialties = [];
            foreach (all_categories() as $category) {
                $specialties[] = $category['name'];
            }
            if ($old['full_name'] === '') {
                $errors[] = 'Enter the worker name.';
            }
            if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 160) {
                $errors[] = 'Enter a valid email address.';
            }
            if (!in_array($old['specialty'], $specialties, true)) {
                $errors[] = 'Choose a specialty.';
            }
            $phoneError = valid_phone($old['phone']);
            if ($phoneError !== '') {
                $errors[] = $phoneError;
            }
            if ($action === 'create' || $password !== '') {
                $passwordError = valid_password($password);
                if ($passwordError !== '') {
                    $errors[] = $passwordError;
                }
            }
            if ($action === 'save') {
                $existing = db()->prepare("SELECT id FROM users WHERE id = ? AND role = 'worker'");
                $existing->execute([$workerId]);
                if (!$existing->fetch()) {
                    $errors[] = 'That worker account was not found.';
                }
            }
            if (!$errors) {
                try {
                    if ($action === 'create') {
                        $stmt = db()->prepare('INSERT INTO users (full_name, email, password_hash, role, phone, department, specialty, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)');
                        $stmt->execute([
                            $old['full_name'],
                            $old['email'],
                            password_hash($password, PASSWORD_DEFAULT),
                            'worker',
                            $old['phone'] !== '' ? $old['phone'] : null,
                            'Campus Maintenance',
                            $old['specialty'],
                            date('Y-m-d H:i:s'),
                        ]);
                        flash('success', 'Worker account created for ' . $old['email'] . '.');
                        redirect('workers.php');
                    } else {
                        if ($password !== '') {
                            $stmt = db()->prepare('UPDATE users SET full_name = ?, email = ?, phone = ?, specialty = ?, is_active = ?, password_hash = ? WHERE id = ? AND role = ?');
                            $stmt->execute([
                                $old['full_name'],
                                $old['email'],
                                $old['phone'] !== '' ? $old['phone'] : null,
                                $old['specialty'],
                                $old['is_active'],
                                password_hash($password, PASSWORD_DEFAULT),
                                $workerId,
                                'worker',
                            ]);
                        } else {
                            $stmt = db()->prepare('UPDATE users SET full_name = ?, email = ?, phone = ?, specialty = ?, is_active = ? WHERE id = ? AND role = ?');
                            $stmt->execute([
                                $old['full_name'],
                                $old['email'],
                                $old['phone'] !== '' ? $old['phone'] : null,
                                $old['specialty'],
                                $old['is_active'],
                                $workerId,
                                'worker',
                            ]);
                        }
                        flash('success', 'Worker account saved.');
                        redirect('workers.php');
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000') {
                        $errors[] = 'An account with this email already exists.';
                    } else {
                        error_log('FixFlow: ' . $e->getMessage());
                        $errors[] = 'The worker account could not be saved.';
                    }
                }
            }
            if ($action === 'save') {
                $editing = ['id' => $workerId];
            }
        }
    }
}

if (!$editing && isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT id, full_name, email, phone, specialty, is_active FROM users WHERE id = ? AND role = 'worker'");
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch();
    if ($editing && !$errors) {
        $old = [
            'full_name' => $editing['full_name'],
            'email' => $editing['email'],
            'phone' => $editing['phone'] ? $editing['phone'] : '',
            'specialty' => $editing['specialty'],
            'is_active' => (int) $editing['is_active'],
        ];
    }
}

$rows = worker_rows();
$categories = all_categories();
$pageTitle = 'Workers';
$pageIntro = 'Maintenance accounts that can receive jobs. Deactivate a worker instead of deleting the record.';
$activeNav = 'workers';
require __DIR__ . '/includes/app-header.php';
?>
<form class="form-panel" method="post" action="workers.php<?= $editing ? '?edit=' . (int) $editing['id'] : '' ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="<?= $editing ? 'save' : 'create' ?>">
    <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>
    <h2><?= $editing ? 'Update worker' : 'Add a worker' ?></h2>
    <?php render_errors($errors); ?>
    <div class="field-row">
        <div class="field">
            <label for="full_name">Name</label>
            <input class="control" type="text" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" maxlength="120" required>
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input class="control" type="email" id="email" name="email" value="<?= e($old['email']) ?>" maxlength="160" required>
        </div>
    </div>
    <div class="field-row">
        <div class="field">
            <label for="specialty">Specialty</label>
            <select class="control" id="specialty" name="specialty" required>
                <option value="">Select a specialty</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category['name']) ?>" <?= $old['specialty'] === $category['name'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="phone">Phone</label>
            <input class="control" type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" maxlength="20">
        </div>
    </div>
    <div class="field">
        <label for="password"><?= $editing ? 'New password' : 'Password' ?></label>
        <div class="password-field">
            <input class="control" type="password" id="password" name="password" autocomplete="new-password" <?= $editing ? '' : 'required' ?>>
            <button class="text-link" type="button" data-toggle-password="password">Show</button>
        </div>
        <p class="help"><?= $editing ? 'Leave this blank to keep the current password.' : 'At least 8 characters, with a letter and a number. Share it with the worker.' ?></p>
    </div>
    <?php if ($editing): ?>
        <input type="hidden" name="is_active" value="0">
        <label class="check-line"><input type="checkbox" name="is_active" value="1" <?= (int) $old['is_active'] === 1 ? 'checked' : '' ?>> Account is active</label>
    <?php endif; ?>
    <div class="form-actions">
        <button class="ff-btn" type="submit"><?= $editing ? 'Save worker' : 'Add worker' ?></button>
        <?php if ($editing): ?><a class="ff-btn secondary" href="workers.php">Cancel</a><?php endif; ?>
    </div>
</form>

<h2>Maintenance staff</h2>
<p class="sheet-hint">Swipe sideways to see every column.</p>
<div class="sheet-wrap">
    <table class="sheet">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Specialty</th>
                <th>Phone</th>
                <th>Open</th>
                <th>Resolved</th>
                <th>Rating</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['full_name']) ?></td>
                    <td><?= e($row['email']) ?></td>
                    <td><?= e($row['specialty']) ?></td>
                    <td><?= e($row['phone'] ? $row['phone'] : '—') ?></td>
                    <td><?= (int) $row['open_jobs'] ?></td>
                    <td><?= (int) $row['done_jobs'] ?></td>
                    <td><?= $row['avg_rating'] === null ? '—' : e($row['avg_rating']) ?></td>
                    <td><?= (int) $row['is_active'] === 1 ? 'Active' : 'Inactive' ?></td>
                    <td class="row-actions">
                        <a class="text-link" href="workers.php?edit=<?= (int) $row['id'] ?>">Edit</a>
                        <form method="post" action="workers.php" data-confirm="<?= (int) $row['is_active'] === 1 ? 'Deactivate this worker? Open jobs stay assigned, but new jobs cannot be given to them.' : 'Activate this worker?' ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                            <button class="text-link" type="submit"><?= (int) $row['is_active'] === 1 ? 'Deactivate' : 'Activate' ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/app-footer.php'; ?>
