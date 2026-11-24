<?php
require __DIR__ . '/includes/init.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$errors = [];
$old = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'department' => '',
    'role' => 'student',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $errors[] = 'The form expired. Refresh the page and try again.';
    }
    $old['full_name'] = clean_line(isset($_POST['full_name']) ? $_POST['full_name'] : '', 120);
    $old['email'] = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
    $old['phone'] = clean_line(isset($_POST['phone']) ? $_POST['phone'] : '', 30);
    $old['department'] = isset($_POST['department']) ? (string) $_POST['department'] : '';
    $old['role'] = isset($_POST['role']) && $_POST['role'] === 'staff' ? 'staff' : 'student';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $confirm = isset($_POST['confirm_password']) ? (string) $_POST['confirm_password'] : '';

    if ($old['full_name'] === '') {
        $errors[] = 'Enter your name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 160) {
        $errors[] = 'Enter a valid email address.';
    }
    if (!in_array($old['department'], departments(), true)) {
        $errors[] = 'Select your department.';
    }
    $phoneError = valid_phone($old['phone']);
    if ($phoneError !== '') {
        $errors[] = $phoneError;
    }
    $passwordError = valid_password($password);
    if ($passwordError !== '') {
        $errors[] = $passwordError;
    } elseif ($password !== $confirm) {
        $errors[] = 'The passwords do not match.';
    }

    if (!$errors) {
        ensure_schema();
        try {
            $stmt = db()->prepare('INSERT INTO users (full_name, email, password_hash, role, phone, department, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, ?)');
            $stmt->execute([
                $old['full_name'],
                $old['email'],
                password_hash($password, PASSWORD_DEFAULT),
                $old['role'],
                $old['phone'] !== '' ? $old['phone'] : null,
                $old['department'],
                date('Y-m-d H:i:s'),
            ]);
            login_user(['id' => (int) db()->lastInsertId()]);
            flash('success', 'Your account is ready. File a complaint when something on campus needs repair.');
            redirect('dashboard.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'An account with this email already exists. Sign in instead.';
            } else {
                error_log('FixFlow: ' . $e->getMessage());
                $errors[] = 'The account could not be created. Try again.';
            }
        }
    }
}

$pageTitle = 'Create account';
$lead = 'Students and staff open an account here. An administrator adds maintenance workers.';
require __DIR__ . '/includes/auth-open.php';
?>
<h1>Create account</h1>
<p class="req-note">All fields are required except phone.</p>
<?php render_errors($errors); ?>
<form method="post" action="register.php" novalidate>
    <?= csrf_field() ?>
    <div class="field">
        <label for="full_name">Full name</label>
        <input class="control" type="text" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" maxlength="120" autocomplete="name" required>
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input class="control" type="email" id="email" name="email" value="<?= e($old['email']) ?>" maxlength="160" autocomplete="email" required>
    </div>
    <fieldset class="field">
        <legend>I am</legend>
        <div class="choices">
            <label><input type="radio" name="role" value="student" <?= $old['role'] === 'student' ? 'checked' : '' ?>> Student</label>
            <label><input type="radio" name="role" value="staff" <?= $old['role'] === 'staff' ? 'checked' : '' ?>> Staff</label>
        </div>
    </fieldset>
    <div class="field">
        <label for="department">Department</label>
        <select class="control" id="department" name="department" required>
            <?= department_options($old['department']) ?>
        </select>
    </div>
    <div class="field">
        <label for="phone">Phone</label>
        <input class="control" type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" maxlength="20" autocomplete="tel">
        <p class="help">Optional. Workers and administrators can see it on your complaint.</p>
    </div>
    <div class="field-row">
        <div class="field">
            <label for="password">Password</label>
            <div class="password-field">
                <input class="control" type="password" id="password" name="password" autocomplete="new-password" required>
                <button class="text-link" type="button" data-toggle-password="password">Show</button>
            </div>
            <p class="help">At least 8 characters, with a letter and a number.</p>
        </div>
        <div class="field">
            <label for="confirm_password">Confirm password</label>
            <input class="control" type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
        </div>
    </div>
    <div class="form-actions">
        <button class="ff-btn" type="submit">Create account</button>
    </div>
</form>
<p class="form-switch">Already registered? <a class="text-link" href="login.php">Sign in</a></p>
<?php require __DIR__ . '/includes/auth-close.php'; ?>
