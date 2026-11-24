<?php
require __DIR__ . '/includes/init.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $errors[] = 'The form expired. Refresh the page and try again.';
    } else {
        $email = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
        $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
        ensure_schema();
        $account = find_user_by_email($email);
        if (!$account || !password_verify($password, $account['password_hash'])) {
            $errors[] = 'Email or password does not match our records.';
        } elseif ((int) $account['is_active'] !== 1) {
            $errors[] = 'This account is inactive. Ask an administrator.';
        } else {
            login_user($account);
            redirect('dashboard.php');
        }
    }
}

$pageTitle = 'Sign in';
$lead = 'Sign in to file a complaint, assign a worker, or update a job.';
require __DIR__ . '/includes/auth-open.php';
?>
<h1>Sign in</h1>
<?php render_errors($errors); ?>
<form method="post" action="login.php" novalidate>
    <?= csrf_field() ?>
    <div class="field">
        <label for="email">Email</label>
        <input class="control" type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="username" required>
    </div>
    <div class="field">
        <label for="password">Password</label>
        <div class="password-field">
            <input class="control" type="password" id="password" name="password" autocomplete="current-password" required>
            <button class="text-link" type="button" data-toggle-password="password">Show</button>
        </div>
    </div>
    <div class="form-actions">
        <button class="ff-btn" type="submit">Sign in</button>
    </div>
</form>
<p class="form-switch">New to the desk? <a class="text-link" href="register.php">Create an account</a></p>
<details class="demo">
    <summary>Demo accounts for evaluation</summary>
    <?php render_demo_accounts(); ?>
</details>
<?php require __DIR__ . '/includes/auth-close.php'; ?>
