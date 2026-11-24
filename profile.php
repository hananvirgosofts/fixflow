<?php
require __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$errors = [];
$old = [
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'phone' => $user['phone'] ? $user['phone'] : '',
    'department' => $user['department'] ? $user['department'] : '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $errors[] = 'The form expired. Refresh the page and try again.';
    }
    $old['full_name'] = clean_line(isset($_POST['full_name']) ? $_POST['full_name'] : '', 120);
    $old['email'] = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
    $old['phone'] = clean_line(isset($_POST['phone']) ? $_POST['phone'] : '', 30);
    $old['department'] = isset($_POST['department']) ? (string) $_POST['department'] : '';
    $currentPassword = isset($_POST['current_password']) ? (string) $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? (string) $_POST['new_password'] : '';
    $confirm = isset($_POST['confirm_password']) ? (string) $_POST['confirm_password'] : '';

    if ($old['full_name'] === '') {
        $errors[] = 'Enter your name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 160) {
        $errors[] = 'Enter a valid email address.';
    }
    $phoneError = valid_phone($old['phone']);
    if ($phoneError !== '') {
        $errors[] = $phoneError;
    }
    if ($user['role'] !== 'worker' && !in_array($old['department'], departments(), true)) {
        $errors[] = 'Select your department.';
    }
    $hash = null;
    if ($currentPassword !== '' || $newPassword !== '' || $confirm !== '') {
        $passwordError = valid_password($newPassword);
        if ($passwordError !== '') {
            $errors[] = $passwordError;
        } elseif ($newPassword !== $confirm) {
            $errors[] = 'The new passwords do not match.';
        } else {
            $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
            $stmt->execute([(int) $user['id']]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
                $errors[] = 'The current password is not correct.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            }
        }
    }

    if (!$errors) {
        try {
            if ($user['role'] === 'worker') {
                if ($hash) {
                    $stmt = db()->prepare('UPDATE users SET full_name = ?, email = ?, phone = ?, password_hash = ? WHERE id = ?');
                    $stmt->execute([$old['full_name'], $old['email'], $old['phone'] !== '' ? $old['phone'] : null, $hash, (int) $user['id']]);
                } else {
                    $stmt = db()->prepare('UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?');
                    $stmt->execute([$old['full_name'], $old['email'], $old['phone'] !== '' ? $old['phone'] : null, (int) $user['id']]);
                }
            } else {
                if ($hash) {
                    $stmt = db()->prepare('UPDATE users SET full_name = ?, email = ?, phone = ?, department = ?, password_hash = ? WHERE id = ?');
                    $stmt->execute([$old['full_name'], $old['email'], $old['phone'] !== '' ? $old['phone'] : null, $old['department'], $hash, (int) $user['id']]);
                } else {
                    $stmt = db()->prepare('UPDATE users SET full_name = ?, email = ?, phone = ?, department = ? WHERE id = ?');
                    $stmt->execute([$old['full_name'], $old['email'], $old['phone'] !== '' ? $old['phone'] : null, $old['department'], (int) $user['id']]);
                }
            }
            flash('success', $hash ? 'Profile and password saved.' : 'Profile saved.');
            redirect('profile.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'An account with this email already exists.';
            } else {
                error_log('FixFlow: ' . $e->getMessage());
                $errors[] = 'The profile could not be saved.';
            }
        }
    }
}

$pageTitle = 'Profile';
$pageIntro = role_label($user['role']) . ($user['specialty'] ? ', ' . $user['specialty'] : '');
$activeNav = 'profile';
require __DIR__ . '/includes/app-header.php';
?>
<form class="form-panel narrow" method="post" action="profile.php">
    <?= csrf_field() ?>
    <?php render_errors($errors); ?>
    <div class="field">
        <label for="full_name">Full name</label>
        <input class="control" type="text" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" maxlength="120" required>
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input class="control" type="email" id="email" name="email" value="<?= e($old['email']) ?>" maxlength="160" required>
    </div>
    <div class="field">
        <label for="phone">Phone</label>
        <input class="control" type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" maxlength="20">
    </div>
    <?php if ($user['role'] === 'worker'): ?>
        <div class="field">
            <label>Specialty</label>
            <p><?= e($user['specialty']) ?></p>
            <p class="help">An administrator changes the specialty from the Workers page.</p>
        </div>
    <?php else: ?>
        <div class="field">
            <label for="department">Department</label>
            <select class="control" id="department" name="department" required>
                <?= department_options($old['department']) ?>
            </select>
        </div>
    <?php endif; ?>
    <h2>Change password</h2>
    <p class="help">Leave these blank to keep your current password.</p>
    <div class="field">
        <label for="current_password">Current password</label>
        <input class="control" type="password" id="current_password" name="current_password" autocomplete="current-password">
    </div>
    <div class="field-row">
        <div class="field">
            <label for="new_password">New password</label>
            <input class="control" type="password" id="new_password" name="new_password" autocomplete="new-password">
        </div>
        <div class="field">
            <label for="confirm_password">Confirm new password</label>
            <input class="control" type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
        </div>
    </div>
    <div class="form-actions">
        <button class="ff-btn" type="submit">Save profile</button>
    </div>
</form>
<?php require __DIR__ . '/includes/app-footer.php'; ?>
