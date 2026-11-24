<?php

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function icon($name)
{
    static $paths = null;
    if ($paths === null) {
        $paths = [
            'overview' => '<rect x="3.5" y="3.5" width="7" height="7" rx="1.2"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.2"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.2"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.2"/>',
            'complaints' => '<path d="M9 4.5h6"/><rect x="6.5" y="6.5" width="11" height="14" rx="1.6"/><path d="M9 12h6M9 15.5h4"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'wrench' => '<path d="M14.5 6.2a3.6 3.6 0 0 0-4.8 4.7L4.5 16.1 7.9 19.5l5.2-5.2a3.6 3.6 0 0 0 4.7-4.8L15 12.3l-3.3-3.3 2.8-2.8z"/>',
            'bars' => '<path d="M4 19.5V11M10 19.5V5.5M16 19.5V9M20.5 19.5h-18"/>',
            'user' => '<circle cx="12" cy="8" r="3.2"/><path d="M5.5 19.5a6.5 6.5 0 0 1 13 0"/>',
            'out' => '<path d="M10 7V5.5h8.5v13H10V17M4 12h10"/><path d="M11 9l3 3-3 3"/>',
            'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
            'inbox' => '<path d="M4 13h4.2L10 16h4l1.8-3H20"/><path d="M4 13V7.5h16V13"/>',
            'user-check' => '<circle cx="9" cy="8" r="3"/><path d="M3.5 19a6 6 0 0 1 9.2-2.2"/><path d="M14.5 16.5l2 2 4-4.2"/>',
            'check' => '<path d="M5 12.5l4.2 4.2L19 7"/>',
            'x' => '<path d="M7 7l10 10M17 7L7 17"/>',
            'bolt' => '<path d="M13 3L5 13.5h6.2L10 21l8-10.5h-6.2L13 3z"/>',
            'drop' => '<path d="M12 3.5s6 6.4 6 10.2a6 6 0 0 1-12 0C6 9.9 12 3.5 12 3.5z"/>',
            'chair' => '<path d="M7.5 10V6h9v4M6 10h12v3H6zM8.5 13v6.5M15.5 13v6.5"/>',
            'flask' => '<path d="M10 3.5h4M11 3.5v5.2L6.2 20h11.6L13 8.7V3.5"/>',
            'building' => '<path d="M4.5 20.5h15M6.5 20.5V8.5L12 4.5l5.5 4v12M10 20.5v-4.5h4v4.5"/>',
            'fan' => '<circle cx="12" cy="12" r="2.2"/><path d="M12 9.2V4.5M12 14.8v4.7M9.2 12H4.5M14.8 12h4.7"/>',
            'spark' => '<path d="M12 3.5l1.4 5.1L18.5 10l-5.1 1.4L12 16.5l-1.4-5.1L5.5 10l5.1-1.4z"/>',
            'tag' => '<path d="M4.5 12.2L12 4.7h7v7l-7.5 7.5-7-7z"/><circle cx="15.2" cy="8.8" r=".8" fill="currentColor" stroke="none"/>',
            'pin' => '<path d="M12 21s6.5-5.6 6.5-10.2a6.5 6.5 0 1 0-13 0C5.5 15.4 12 21 12 21z"/><circle cx="12" cy="10.5" r="1.8"/>',
            'star' => '<path d="M12 3.8l2.1 4.6 5 .6-3.7 3.4 1.1 5L12 15.4 7.5 17.4l1.1-5L4.9 9l5-.6z"/>',
            'arrow-up' => '<path d="M12 19V6M7 10.5L12 5.5l5 5"/>',
            'arrow-down' => '<path d="M12 5v13M7 13.5L12 18.5l5-5"/>',
            'minus' => '<path d="M6 12h12"/>',
            'alert' => '<path d="M12 4.5l8 14.5H4L12 4.5z"/><path d="M12 10v4M12 16.8h.01"/>',
        ];
    }
    $body = isset($paths[$name]) ? $paths[$name] : $paths['tag'];
    return '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $body . '</svg>';
}

function nav_icon($key)
{
    $map = [
        'dashboard' => 'overview',
        'complaints' => 'complaints',
        'new' => 'plus',
        'workers' => 'wrench',
        'reports' => 'bars',
        'profile' => 'user',
    ];
    return isset($map[$key]) ? $map[$key] : 'tag';
}

function status_icon($status)
{
    $map = [
        'submitted' => 'inbox',
        'assigned' => 'user-check',
        'in_progress' => 'wrench',
        'resolved' => 'check',
        'rejected' => 'x',
        'pending' => 'inbox',
        'open' => 'wrench',
    ];
    return isset($map[$status]) ? $map[$status] : 'tag';
}

function category_icon_name($name)
{
    $map = [
        'Electrical' => 'bolt',
        'Plumbing' => 'drop',
        'Furniture' => 'chair',
        'Laboratory Equipment' => 'flask',
        'Civil / Building' => 'building',
        'HVAC' => 'fan',
        'Housekeeping' => 'spark',
        'General' => 'tag',
    ];
    return isset($map[$name]) ? $map[$name] : 'tag';
}

function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

function flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function consume_flash()
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function render_flash()
{
    $flash = consume_flash();
    if (!$flash) {
        return;
    }
    $type = $flash['type'] === 'error' ? 'error' : 'success';
    $role = $type === 'error' ? 'alert' : 'status';
    echo '<div class="flash flash-' . e($type) . '" role="' . $role . '">' . e($flash['message']) . '</div>';
}

function render_errors(array $errors)
{
    if (!$errors) {
        return;
    }
    echo '<div class="flash flash-error" role="alert"><ul>';
    foreach ($errors as $error) {
        echo '<li>' . e($error) . '</li>';
    }
    echo '</ul></div>';
}

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok()
{
    $sent = isset($_POST['csrf']) ? (string) $_POST['csrf'] : '';
    $real = isset($_SESSION['csrf']) ? (string) $_SESSION['csrf'] : '';
    return $real !== '' && hash_equals($real, $sent);
}

function departments()
{
    return [
        'Department of CS & IT',
        'Department of Physics',
        'Department of Chemistry',
        'Department of Mathematics',
        'Department of English',
        'Department of Zoology',
        'Department of Botany',
        'Administration',
        'Library',
        'Hostel',
        'Other',
    ];
}

function category_catalog()
{
    return [
        ['Electrical', 'Fans, lights, projectors, switches, and wiring.'],
        ['Plumbing', 'Taps, leaks, drains, and washroom fittings.'],
        ['Furniture', 'Chairs, desks, boards, and broken fittings.'],
        ['Laboratory Equipment', 'Apparatus and fixtures inside teaching laboratories.'],
        ['Civil / Building', 'Doors, windows, walls, and structural repairs.'],
        ['HVAC', 'Air conditioners and ventilation.'],
        ['Housekeeping', 'Cleaning issues in shared campus spaces.'],
        ['General', 'Anything else the maintenance desk should see.'],
    ];
}

function role_label($role)
{
    $map = [
        'student' => 'Student',
        'staff' => 'Staff',
        'admin' => 'Administrator',
        'worker' => 'Maintenance worker',
    ];
    return isset($map[$role]) ? $map[$role] : $role;
}

function status_label($status)
{
    $map = [
        'submitted' => 'Submitted',
        'assigned' => 'Assigned',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'rejected' => 'Rejected',
        'pending' => 'Pending',
        'open' => 'Still open',
    ];
    return isset($map[$status]) ? $map[$status] : $status;
}

function priority_label($priority)
{
    $map = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];
    return isset($map[$priority]) ? $map[$priority] : $priority;
}

function format_when($value)
{
    $time = strtotime((string) $value);
    if (!$time) {
        return '';
    }
    return date('d M Y, H:i', $time);
}

function format_day($value)
{
    $time = strtotime((string) $value);
    if (!$time) {
        return '';
    }
    return date('d M Y', $time);
}

function valid_date($value)
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
        return false;
    }
    $time = strtotime($value . ' 00:00:00');
    return $time !== false;
}

function text_length($value)
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    return strlen($value);
}

function limit_str($value, $max)
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max, 'UTF-8');
    }
    return substr($value, 0, $max);
}

function clean_line($value, $max)
{
    $value = str_replace("\0", '', (string) $value);
    $value = trim(preg_replace('/\s+/u', ' ', $value));
    return limit_str($value, $max);
}

function clean_block($value, $max)
{
    $value = str_replace("\0", '', (string) $value);
    $value = str_replace("\r\n", "\n", $value);
    $value = str_replace("\r", "\n", $value);
    return limit_str(trim($value), $max);
}

function valid_password($password)
{
    if (strlen($password) < 8) {
        return 'Use at least 8 characters.';
    }
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        return 'Include at least one letter and one number.';
    }
    return '';
}

function valid_phone($phone)
{
    if ($phone === '') {
        return '';
    }
    if (!preg_match('/^[0-9+\- ]{7,20}$/', $phone)) {
        return 'Enter a phone number using digits, spaces, or a plus sign.';
    }
    return '';
}

function blank_filters()
{
    return [
        'q' => '',
        'status' => '',
        'priority' => '',
        'category' => 0,
        'from' => '',
        'to' => '',
    ];
}

function read_filters()
{
    $filters = blank_filters();
    $filters['q'] = clean_line(isset($_GET['q']) ? $_GET['q'] : '', 80);
    $status = isset($_GET['status']) ? (string) $_GET['status'] : '';
    $allowedStatus = ['open', 'pending', 'submitted', 'assigned', 'in_progress', 'resolved', 'rejected'];
    if (in_array($status, $allowedStatus, true)) {
        $filters['status'] = $status;
    }
    $priority = isset($_GET['priority']) ? (string) $_GET['priority'] : '';
    if (in_array($priority, ['low', 'medium', 'high', 'urgent'], true)) {
        $filters['priority'] = $priority;
    }
    $filters['category'] = max(0, (int) (isset($_GET['category']) ? $_GET['category'] : 0));
    $from = isset($_GET['from']) ? (string) $_GET['from'] : '';
    $to = isset($_GET['to']) ? (string) $_GET['to'] : '';
    if (valid_date($from)) {
        $filters['from'] = $from;
    }
    if (valid_date($to)) {
        $filters['to'] = $to;
    }
    return $filters;
}

function complaint_conditions(array $user, array $filters)
{
    $where = ['1=1'];
    $params = [];
    if ($user['role'] === 'worker') {
        $where[] = 'c.assigned_to = ?';
        $params[] = (int) $user['id'];
    } elseif ($user['role'] === 'student' || $user['role'] === 'staff') {
        $where[] = 'c.user_id = ?';
        $params[] = (int) $user['id'];
    }

    if ($filters['status'] === 'pending') {
        $where[] = "c.status IN ('submitted', 'assigned')";
    } elseif ($filters['status'] === 'open') {
        $where[] = "c.status IN ('submitted', 'assigned', 'in_progress')";
    } elseif (in_array($filters['status'], ['submitted', 'assigned', 'in_progress', 'resolved', 'rejected'], true)) {
        $where[] = 'c.status = ?';
        $params[] = $filters['status'];
    }

    if (in_array($filters['priority'], ['low', 'medium', 'high', 'urgent'], true)) {
        $where[] = 'c.priority = ?';
        $params[] = $filters['priority'];
    }
    if ((int) $filters['category'] > 0) {
        $where[] = 'c.category_id = ?';
        $params[] = (int) $filters['category'];
    }
    if ($filters['from'] !== '') {
        $where[] = 'c.created_at >= ?';
        $params[] = $filters['from'] . ' 00:00:00';
    }
    if ($filters['to'] !== '') {
        $where[] = 'c.created_at < ?';
        $params[] = date('Y-m-d', strtotime($filters['to'] . ' +1 day')) . ' 00:00:00';
    }
    if ($filters['q'] !== '') {
        $like = '%' . str_replace(['%', '_'], '', $filters['q']) . '%';
        $where[] = '(c.title LIKE ? OR c.ticket_no LIKE ? OR c.location LIKE ?)';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    return [implode(' AND ', $where), $params];
}

function find_user($id)
{
    $stmt = db()->prepare('SELECT id, full_name, email, role, phone, department, specialty, is_active FROM users WHERE id = ?');
    $stmt->execute([(int) $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function find_user_by_email($email)
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([strtolower(trim($email))]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function ensure_schema()
{
    static $ready = false;
    if ($ready) {
        return;
    }
    try {
        db()->query('SELECT 1 FROM users LIMIT 1');
        $ready = true;
    } catch (PDOException $e) {
        fail_database($e->getMessage());
    }
}

function require_login()
{
    if (empty($_SESSION['user_id'])) {
        flash('error', 'Sign in to continue.');
        redirect('login.php');
    }
    ensure_schema();
    $user = find_user((int) $_SESSION['user_id']);
    if (!$user || (int) $user['is_active'] !== 1) {
        $_SESSION = [];
        session_destroy();
        session_start();
        flash('error', 'This account is inactive. Ask an administrator.');
        redirect('login.php');
    }
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'phone' => $user['phone'],
        'department' => $user['department'],
        'specialty' => $user['specialty'],
    ];
}

function current_user()
{
    return $_SESSION['user'];
}

function require_role(array $roles)
{
    require_login();
    if (!in_array(current_user()['role'], $roles, true)) {
        http_response_code(403);
        $pageTitle = 'Not available';
        $pageIntro = 'This page belongs to a different account type.';
        $activeNav = 'dashboard';
        require __DIR__ . '/app-header.php';
        empty_box('That page is not available for your account.', 'Use the menu to return to your overview.', 'dashboard.php', 'Go to overview');
        require __DIR__ . '/app-footer.php';
        exit;
    }
}

function nav_items(array $user)
{
    $items = [
        ['dashboard.php', 'Overview', 'dashboard'],
        ['complaints.php', $user['role'] === 'worker' ? 'My jobs' : 'Complaints', 'complaints'],
    ];
    if ($user['role'] === 'student' || $user['role'] === 'staff') {
        $items[] = ['complaint-new.php', 'File a complaint', 'new'];
    }
    if ($user['role'] === 'admin') {
        $items[] = ['workers.php', 'Workers', 'workers'];
        $items[] = ['reports.php', 'Reports', 'reports'];
    }
    $items[] = ['profile.php', 'Profile', 'profile'];
    return $items;
}

function render_nav(array $user, $active)
{
    echo '<nav class="rail-nav" aria-label="Account">';
    foreach (nav_items($user) as $item) {
        $class = $item[2] === $active ? ' class="active"' : '';
        $current = $item[2] === $active ? ' aria-current="page"' : '';
        echo '<a' . $class . $current . ' href="' . e($item[0]) . '">' . icon(nav_icon($item[2])) . '<span>' . e($item[1]) . '</span></a>';
    }
    echo '</nav>';
}

function render_user_block(array $user)
{
    echo '<div class="rail-user">';
    echo '<strong>' . e($user['full_name']) . '</strong>';
    $detail = role_label($user['role']);
    if ($user['role'] === 'worker' && !empty($user['specialty'])) {
        $detail .= ', ' . $user['specialty'];
    } elseif (!empty($user['department'])) {
        $detail .= ', ' . $user['department'];
    }
    echo '<span>' . e($detail) . '</span>';
    echo '<a class="with-ico" href="logout.php">' . icon('out') . 'Sign out</a>';
    echo '</div>';
}

function all_categories()
{
    return db()->query('SELECT id, name, description FROM categories ORDER BY name')->fetchAll();
}

function active_workers()
{
    $stmt = db()->query("SELECT id, full_name, specialty, phone, email FROM users WHERE role = 'worker' AND is_active = 1 ORDER BY full_name");
    return $stmt->fetchAll();
}

function counts_for(array $user)
{
    list($where, $params) = complaint_conditions($user, blank_filters());
    $stmt = db()->prepare("SELECT c.status, COUNT(*) AS total FROM complaints c WHERE $where GROUP BY c.status");
    $stmt->execute($params);
    $counts = [
        'submitted' => 0,
        'assigned' => 0,
        'in_progress' => 0,
        'resolved' => 0,
        'rejected' => 0,
    ];
    foreach ($stmt as $row) {
        if (isset($counts[$row['status']])) {
            $counts[$row['status']] = (int) $row['total'];
        }
    }
    $counts['total'] = array_sum($counts);
    $counts['pending'] = $counts['submitted'] + $counts['assigned'];
    $counts['open'] = $counts['pending'] + $counts['in_progress'];
    return $counts;
}

function count_complaints(array $user, array $filters)
{
    list($where, $params) = complaint_conditions($user, $filters);
    $stmt = db()->prepare("SELECT COUNT(*) FROM complaints c WHERE $where");
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function fetch_complaints(array $user, array $filters, $limit = null, $offset = 0)
{
    list($where, $params) = complaint_conditions($user, $filters);
    $sql = 'SELECT c.id, c.ticket_no, c.title, c.location, c.priority, c.status, c.created_at,
                   cat.name AS category_name, u.full_name AS reporter_name, w.full_name AS worker_name
            FROM complaints c
            JOIN categories cat ON cat.id = c.category_id
            JOIN users u ON u.id = c.user_id
            LEFT JOIN users w ON w.id = c.assigned_to
            WHERE ' . $where . '
            ORDER BY c.id DESC';
    if ($limit !== null) {
        $sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function recent_updates(array $user, $limit = 6)
{
    $where = '1=1';
    $params = [];
    if ($user['role'] === 'worker') {
        $where = 'c.assigned_to = ?';
        $params[] = (int) $user['id'];
    } elseif ($user['role'] !== 'admin') {
        $where = 'c.user_id = ?';
        $params[] = (int) $user['id'];
    }
    $sql = 'SELECT cu.created_at, cu.note, cu.status, c.id, c.ticket_no, u.full_name
            FROM complaint_updates cu
            JOIN complaints c ON c.id = cu.complaint_id
            JOIN users u ON u.id = cu.user_id
            WHERE ' . $where . '
            ORDER BY cu.id DESC
            LIMIT ' . (int) $limit;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function worker_rows()
{
    $sql = "SELECT u.id, u.full_name, u.email, u.phone, u.specialty, u.is_active,
                   SUM(CASE WHEN c.status IN ('assigned', 'in_progress') THEN 1 ELSE 0 END) AS open_jobs,
                   SUM(CASE WHEN c.status = 'resolved' THEN 1 ELSE 0 END) AS done_jobs,
                   ROUND(AVG(f.rating), 1) AS avg_rating
            FROM users u
            LEFT JOIN complaints c ON c.assigned_to = u.id
            LEFT JOIN feedback f ON f.complaint_id = c.id
            WHERE u.role = 'worker'
            GROUP BY u.id, u.full_name, u.email, u.phone, u.specialty, u.is_active
            ORDER BY u.full_name";
    return db()->query($sql)->fetchAll();
}

function category_stats(array $filters)
{
    $join = 'LEFT JOIN complaints c ON c.category_id = cat.id';
    $params = [];
    if ($filters['status'] === 'pending') {
        $join .= " AND c.status IN ('submitted', 'assigned')";
    } elseif ($filters['status'] === 'open') {
        $join .= " AND c.status IN ('submitted', 'assigned', 'in_progress')";
    } elseif (in_array($filters['status'], ['submitted', 'assigned', 'in_progress', 'resolved', 'rejected'], true)) {
        $join .= ' AND c.status = ?';
        $params[] = $filters['status'];
    }
    if (in_array($filters['priority'], ['low', 'medium', 'high', 'urgent'], true)) {
        $join .= ' AND c.priority = ?';
        $params[] = $filters['priority'];
    }
    if ((int) $filters['category'] > 0) {
        $join .= ' AND c.category_id = ?';
        $params[] = (int) $filters['category'];
    }
    if ($filters['from'] !== '') {
        $join .= ' AND c.created_at >= ?';
        $params[] = $filters['from'] . ' 00:00:00';
    }
    if ($filters['to'] !== '') {
        $join .= ' AND c.created_at < ?';
        $params[] = date('Y-m-d', strtotime($filters['to'] . ' +1 day')) . ' 00:00:00';
    }
    $sql = 'SELECT cat.name, COUNT(c.id) AS total
            FROM categories cat ' . $join . '
            GROUP BY cat.id, cat.name
            ORDER BY total DESC, cat.name';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function average_rating_for($userId)
{
    $stmt = db()->prepare('SELECT ROUND(AVG(f.rating), 1) FROM feedback f JOIN complaints c ON c.id = f.complaint_id WHERE c.assigned_to = ?');
    $stmt->execute([(int) $userId]);
    $value = $stmt->fetchColumn();
    return $value === null ? null : $value;
}

function fetch_complaint($id)
{
    $stmt = db()->prepare('SELECT c.*, cat.name AS category_name,
            u.full_name AS reporter_name, u.email AS reporter_email, u.phone AS reporter_phone,
            u.department AS reporter_department,
            w.full_name AS worker_name, w.specialty AS worker_specialty, w.phone AS worker_phone
        FROM complaints c
        JOIN categories cat ON cat.id = c.category_id
        JOIN users u ON u.id = c.user_id
        LEFT JOIN users w ON w.id = c.assigned_to
        WHERE c.id = ?');
    $stmt->execute([(int) $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function fetch_updates($complaintId)
{
    $stmt = db()->prepare('SELECT cu.created_at, cu.note, cu.status, u.full_name
        FROM complaint_updates cu
        JOIN users u ON u.id = cu.user_id
        WHERE cu.complaint_id = ?
        ORDER BY cu.id ASC');
    $stmt->execute([(int) $complaintId]);
    return $stmt->fetchAll();
}

function fetch_feedback($complaintId)
{
    $stmt = db()->prepare('SELECT f.rating, f.comment, f.created_at, u.full_name
        FROM feedback f
        JOIN users u ON u.id = f.user_id
        WHERE f.complaint_id = ?');
    $stmt->execute([(int) $complaintId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function can_view_complaint(array $user, array $complaint)
{
    if ($user['role'] === 'admin') {
        return true;
    }
    if ($user['role'] === 'worker') {
        return (int) $complaint['assigned_to'] === (int) $user['id'];
    }
    return (int) $complaint['user_id'] === (int) $user['id'];
}

function next_ticket(PDO $pdo)
{
    $prefix = 'FF-' . date('Y') . '-';
    $stmt = $pdo->prepare('SELECT ticket_no FROM complaints WHERE ticket_no LIKE ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    $number = 1000;
    if ($last && preg_match('/(\d+)$/', $last, $matches)) {
        $number = (int) $matches[1];
    }
    return $prefix . str_pad((string) ($number + 1), 4, '0', STR_PAD_LEFT);
}

function add_update(PDO $pdo, $complaintId, $userId, $status, $note)
{
    $stmt = $pdo->prepare('INSERT INTO complaint_updates (complaint_id, user_id, status, note, created_at) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([(int) $complaintId, (int) $userId, $status, $note, date('Y-m-d H:i:s')]);
}

function store_upload(array $file)
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['name' => null];
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['error' => 'Use an image smaller than 2 MB.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'The image could not be uploaded. Try a smaller JPG, PNG, or WebP file.'];
    }
    if ((int) $file['size'] > MAX_UPLOAD) {
        return ['error' => 'Use an image smaller than 2 MB.'];
    }
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
    } elseif (function_exists('mime_content_type')) {
        $mime = mime_content_type($file['tmp_name']);
    } else {
        return ['error' => 'This server cannot check image types.'];
    }
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($extensions[$mime])) {
        return ['error' => 'Use a JPG, PNG, or WebP image.'];
    }
    $name = 'complaint-' . bin2hex(random_bytes(8)) . '.' . $extensions[$mime];
    $destination = dirname(__DIR__) . '/uploads/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['error' => 'The image could not be saved.'];
    }
    return ['name' => $name];
}

function public_image($name)
{
    $name = basename((string) $name);
    if ($name === '') {
        return '';
    }
    $allowed = ($name === 'sample-projector.png') || preg_match('/^complaint-[a-f0-9]{16}\.(jpg|png|webp)$/', $name);
    if (!$allowed) {
        return '';
    }
    if (!is_file(dirname(__DIR__) . '/uploads/' . $name)) {
        return '';
    }
    return 'uploads/' . $name;
}

function create_complaint(array $user, array $data, $imageName)
{
    $pdo = db();
    $attempt = 0;
    while ($attempt < 2) {
        $attempt++;
        try {
            $pdo->beginTransaction();
            $ticket = next_ticket($pdo);
            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare('INSERT INTO complaints
                (ticket_no, user_id, category_id, title, description, location, priority, status, image_path, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $ticket,
                (int) $user['id'],
                (int) $data['category_id'],
                $data['title'],
                $data['description'],
                $data['location'],
                $data['priority'],
                'submitted',
                $imageName,
                $now,
                $now,
            ]);
            $id = (int) $pdo->lastInsertId();
            add_update($pdo, $id, $user['id'], 'submitted', 'Complaint filed.');
            $pdo->commit();
            return ['errors' => [], 'id' => $id, 'ticket_no' => $ticket];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e->getCode() === '23000' && $attempt < 2) {
                continue;
            }
            error_log('FixFlow: ' . $e->getMessage());
            return ['errors' => ['The complaint could not be saved. Try again.']];
        }
    }
    return ['errors' => ['The complaint could not be saved. Try again.']];
}

function assign_complaint(array $admin, $complaintId, $workerId, $note)
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id, status FROM complaints WHERE id = ? FOR UPDATE');
        $stmt->execute([(int) $complaintId]);
        $row = $stmt->fetch();
        if (!$row || !in_array($row['status'], ['submitted', 'assigned', 'in_progress'], true)) {
            $pdo->rollBack();
            return ['errors' => ['This complaint can no longer be assigned.']];
        }
        $workerStmt = $pdo->prepare("SELECT id, full_name FROM users WHERE id = ? AND role = 'worker' AND is_active = 1");
        $workerStmt->execute([(int) $workerId]);
        $worker = $workerStmt->fetch();
        if (!$worker) {
            $pdo->rollBack();
            return ['errors' => ['Choose an active maintenance worker.']];
        }
        $status = $row['status'] === 'in_progress' ? 'in_progress' : 'assigned';
        $now = date('Y-m-d H:i:s');
        $update = $pdo->prepare('UPDATE complaints SET assigned_to = ?, status = ?, rejection_reason = NULL, updated_at = ? WHERE id = ?');
        $update->execute([(int) $worker['id'], $status, $now, (int) $complaintId]);
        $text = 'Assigned to ' . $worker['full_name'] . '.';
        if ($note !== '') {
            $text .= ' ' . $note;
        }
        add_update($pdo, (int) $complaintId, (int) $admin['id'], $status, $text);
        $pdo->commit();
        return ['errors' => [], 'message' => 'Complaint assigned to ' . $worker['full_name'] . '.'];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('FixFlow: ' . $e->getMessage());
        return ['errors' => ['The assignment could not be saved. Try again.']];
    }
}

function transition_complaint(array $user, $complaintId, $action, $note)
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id, status, assigned_to FROM complaints WHERE id = ? FOR UPDATE');
        $stmt->execute([(int) $complaintId]);
        $row = $stmt->fetch();
        if (!$row) {
            $pdo->rollBack();
            return ['errors' => ['Complaint not found.']];
        }
        $status = $row['status'];
        $isAssignee = (int) $row['assigned_to'] === (int) $user['id'];
        $isAdmin = $user['role'] === 'admin';
        $newStatus = $status;
        $message = 'Update added.';
        $text = $note;

        if ($action === 'start') {
            if ($user['role'] !== 'worker' || !$isAssignee || $status !== 'assigned') {
                $pdo->rollBack();
                return ['errors' => ['This job cannot be started from its current status.']];
            }
            $newStatus = 'in_progress';
            $text = $note !== '' ? $note : 'Work started.';
            $message = 'Job marked in progress.';
        } elseif ($action === 'resolve') {
            if ($user['role'] !== 'worker' || !$isAssignee || $status !== 'in_progress') {
                $pdo->rollBack();
                return ['errors' => ['This job cannot be resolved from its current status.']];
            }
            if (text_length($note) < 10) {
                $pdo->rollBack();
                return ['errors' => ['Describe the repair in at least a short sentence.']];
            }
            $newStatus = 'resolved';
            $message = 'Job marked resolved.';
        } elseif ($action === 'note') {
            $allowed = ($isAdmin && !in_array($status, ['resolved', 'rejected'], true))
                || ($user['role'] === 'worker' && $isAssignee && $status === 'in_progress');
            if (!$allowed) {
                $pdo->rollBack();
                return ['errors' => ['A progress note cannot be added to this complaint.']];
            }
            if ($note === '') {
                $pdo->rollBack();
                return ['errors' => ['Write a progress note.']];
            }
            $message = 'Progress note added.';
        } elseif ($action === 'reject') {
            if (!$isAdmin || in_array($status, ['resolved', 'rejected'], true)) {
                $pdo->rollBack();
                return ['errors' => ['This complaint cannot be rejected.']];
            }
            if ($note === '') {
                $pdo->rollBack();
                return ['errors' => ['Enter the reason for rejecting this complaint.']];
            }
            $newStatus = 'rejected';
            $text = 'Rejected. ' . $note;
            $message = 'Complaint rejected.';
        } else {
            $pdo->rollBack();
            return ['errors' => ['Choose a valid action.']];
        }

        $now = date('Y-m-d H:i:s');
        if ($newStatus === 'resolved') {
            $update = $pdo->prepare('UPDATE complaints SET status = ?, resolved_at = ?, updated_at = ? WHERE id = ?');
            $update->execute([$newStatus, $now, $now, (int) $complaintId]);
        } elseif ($newStatus === 'rejected') {
            $update = $pdo->prepare('UPDATE complaints SET status = ?, rejection_reason = ?, updated_at = ? WHERE id = ?');
            $update->execute([$newStatus, $note, $now, (int) $complaintId]);
        } elseif ($newStatus !== $status) {
            $update = $pdo->prepare('UPDATE complaints SET status = ?, updated_at = ? WHERE id = ?');
            $update->execute([$newStatus, $now, (int) $complaintId]);
        } else {
            $update = $pdo->prepare('UPDATE complaints SET updated_at = ? WHERE id = ?');
            $update->execute([$now, (int) $complaintId]);
        }
        add_update($pdo, (int) $complaintId, (int) $user['id'], $newStatus, $text);
        $pdo->commit();
        return ['errors' => [], 'message' => $message];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('FixFlow: ' . $e->getMessage());
        return ['errors' => ['The complaint could not be updated. Try again.']];
    }
}

function save_feedback(array $user, array $complaint, $rating, $comment)
{
    if ((int) $complaint['user_id'] !== (int) $user['id']) {
        return ['errors' => ['Only the person who filed this complaint can leave feedback.']];
    }
    if ($complaint['status'] !== 'resolved') {
        return ['errors' => ['Feedback is available after the complaint is resolved.']];
    }
    if ($rating < 1 || $rating > 5) {
        return ['errors' => ['Choose a rating from 1 to 5.']];
    }
    try {
        $stmt = db()->prepare('INSERT INTO feedback (complaint_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            (int) $complaint['id'],
            (int) $user['id'],
            (int) $rating,
            $comment !== '' ? $comment : null,
            date('Y-m-d H:i:s'),
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            return ['errors' => ['Feedback has already been saved for this complaint.']];
        }
        error_log('FixFlow: ' . $e->getMessage());
        return ['errors' => ['The feedback could not be saved. Try again.']];
    }
    return ['errors' => [], 'message' => 'Feedback saved.'];
}

function render_state($status)
{
    echo '<span class="state state-' . e($status) . '">' . icon(status_icon($status)) . e(status_label($status)) . '</span>';
}

function render_priority($priority)
{
    $icons = [
        'low' => 'arrow-down',
        'medium' => 'minus',
        'high' => 'arrow-up',
        'urgent' => 'alert',
    ];
    $name = isset($icons[$priority]) ? $icons[$priority] : 'minus';
    echo '<span class="chip priority priority-' . e($priority) . '">' . icon($name) . e(priority_label($priority)) . '</span>';
}

function render_status_chart(array $counts)
{
    $parts = [
        ['submitted', 'Submitted', 0],
        ['assigned', 'Assigned', 80],
        ['in_progress', 'In Progress', 160],
        ['resolved', 'Resolved', 240],
        ['rejected', 'Rejected', 320],
    ];
    $total = isset($counts['total']) ? (int) $counts['total'] : 0;
    $circumference = 263.89;
    $gap = 2.4;
    $angle = -90;
    echo '<figure class="load-chart">';
    echo '<div class="ring-wrap">';
    echo '<svg viewBox="0 0 120 120" aria-hidden="true">';
    echo '<circle class="ring-track" cx="60" cy="60" r="42"/>';
    if ($total > 0) {
        foreach ($parts as $part) {
            $count = isset($counts[$part[0]]) ? (int) $counts[$part[0]] : 0;
            if ($count < 1) {
                continue;
            }
            $raw = ($count / $total) * $circumference;
            $length = $raw > ($gap + 1) ? $raw - $gap : $raw;
            echo '<circle class="ring-seg ring-' . e($part[0]) . '" cx="60" cy="60" r="42" transform="rotate(' . round($angle, 2) . ' 60 60)" style="--len:' . round($length, 2) . ';--c:' . $circumference . ';--delay:' . (int) $part[2] . 'ms"/>';
            $angle += ($raw / $circumference) * 360;
        }
    }
    echo '</svg>';
    echo '<div class="ring-total"><strong>' . $total . '</strong><span>' . ($total === 1 ? 'complaint' : 'complaints') . '</span></div>';
    echo '</div>';
    echo '<figcaption>';
    echo '<ul class="ring-legend">';
    foreach ($parts as $part) {
        $count = isset($counts[$part[0]]) ? (int) $counts[$part[0]] : 0;
        echo '<li class="legend-' . e($part[0]) . '">' . icon(status_icon($part[0])) . '<span>' . e($part[1]) . '</span><b>' . $count . '</b></li>';
    }
    echo '</ul>';
    echo '</figcaption>';
    echo '</figure>';
}

function greeting_name($fullName)
{
    $fullName = trim((string) $fullName);
    if (strpos($fullName, 'Unit') !== false || strpos($fullName, 'Office') !== false || strpos($fullName, 'Administrator') !== false) {
        return $fullName;
    }
    $space = strpos($fullName, ' ');
    return $space === false ? $fullName : substr($fullName, 0, $space);
}

function flow_hint($status)
{
    $hints = [
        'submitted' => 'An administrator still needs to choose a worker.',
        'assigned' => 'A worker has this job and has not started the repair.',
        'in_progress' => 'The repair is underway. New notes appear in the progress list.',
        'resolved' => 'The job is closed. Feedback can be saved once.',
        'rejected' => 'This complaint was closed without a repair.',
    ];
    return isset($hints[$status]) ? $hints[$status] : '';
}

function render_flow($status)
{
    if ($status === 'rejected') {
        return;
    }
    $order = ['submitted' => 1, 'assigned' => 2, 'in_progress' => 3, 'resolved' => 4];
    $current = isset($order[$status]) ? $order[$status] : 1;
    $steps = [1 => 'Submitted', 2 => 'Assigned', 3 => 'In Progress', 4 => 'Resolved'];
    echo '<ol class="flow" aria-label="Complaint progress">';
    foreach ($steps as $number => $label) {
        if ($status === 'resolved' || $number < $current) {
            $class = 'is-done';
        } elseif ($number === $current) {
            $class = 'is-current';
        } else {
            $class = '';
        }
        $stepIcons = [1 => 'inbox', 2 => 'user-check', 3 => 'wrench', 4 => 'check'];
        $stepIcon = isset($stepIcons[$number]) ? $stepIcons[$number] : 'tag';
        echo '<li class="' . $class . '"><span>' . icon($stepIcon) . '</span>' . e($label) . '</li>';
    }
    echo '</ol>';
    echo '<p class="help flow-hint">' . e(flow_hint($status)) . '</p>';
}

function render_ticket(array $row, $role)
{
    echo '<a class="ticket ticket-' . e($row['status']) . '" href="complaint.php?id=' . (int) $row['id'] . '">';
    echo '<span class="ticket-no">' . e($row['ticket_no']) . '</span>';
    echo '<span class="ticket-main">';
    echo '<span class="ticket-title">' . e($row['title']) . '</span>';
    echo '<span class="ticket-meta">';
    echo '<span class="with-ico">' . icon('pin') . e($row['location']) . '</span>';
    echo '<span class="with-ico">' . icon(category_icon_name($row['category_name'])) . e($row['category_name']) . '</span>';
    render_priority($row['priority']);
    if ($role === 'admin' || $role === 'worker') {
        echo '<span>' . e($row['reporter_name']) . '</span>';
    }
    if ($role !== 'worker' && !empty($row['worker_name'])) {
        echo '<span>' . e($row['worker_name']) . '</span>';
    }
    echo '</span></span>';
    echo '<span class="ticket-side">';
    render_state($row['status']);
    $stamp = strtotime($row['created_at']);
    echo '<time datetime="' . e($stamp ? date('c', $stamp) : '') . '">' . e(format_day($row['created_at'])) . '</time>';
    echo '</span></a>';
}

function page_url($script, $page)
{
    $params = $_GET;
    unset($params['export']);
    $params['page'] = $page;
    return $script . '?' . http_build_query($params);
}

function render_pager($page, $pages, $script)
{
    if ($pages <= 1) {
        return;
    }
    echo '<nav class="pager" aria-label="Pages">';
    if ($page > 1) {
        echo '<a class="ff-btn secondary" href="' . e(page_url($script, $page - 1)) . '">Previous</a>';
    }
    echo '<span>Page ' . (int) $page . ' of ' . (int) $pages . '</span>';
    if ($page < $pages) {
        echo '<a class="ff-btn secondary" href="' . e(page_url($script, $page + 1)) . '">Next</a>';
    }
    echo '</nav>';
}

function empty_box($heading, $text, $href = '', $label = '')
{
    echo '<div class="empty">';
    echo '<span class="empty-mark" aria-hidden="true">' . icon('complaints') . '</span>';
    echo '<h2>' . e($heading) . '</h2>';
    echo '<p>' . e($text) . '</p>';
    if ($href !== '') {
        echo '<a class="ff-btn" href="' . e($href) . '">' . e($label) . '</a>';
    }
    echo '</div>';
}

function department_options($selected)
{
    $html = '<option value="">Select a department</option>';
    foreach (departments() as $department) {
        $html .= '<option value="' . e($department) . '"' . ($department === $selected ? ' selected' : '') . '>' . e($department) . '</option>';
    }
    return $html;
}

function status_filter_options($selected)
{
    $options = [
        '' => 'All statuses',
        'open' => 'Still open',
        'pending' => 'Pending',
        'submitted' => 'Submitted',
        'assigned' => 'Assigned',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'rejected' => 'Rejected',
    ];
    $html = '';
    foreach ($options as $value => $label) {
        $html .= '<option value="' . e($value) . '"' . ((string) $selected === (string) $value ? ' selected' : '') . '>' . e($label) . '</option>';
    }
    return $html;
}

function priority_options($selected, $includeAll = false)
{
    $options = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];
    $html = '';
    if ($includeAll) {
        $html .= '<option value="">All priorities</option>';
    }
    foreach ($options as $value => $label) {
        $html .= '<option value="' . e($value) . '"' . ($selected === $value ? ' selected' : '') . '>' . e($label) . '</option>';
    }
    return $html;
}

function render_demo_accounts()
{
    $rows = [
        ['Administrator', 'admin@tub.test'],
        ['Student, Abdul Hanan', 'hanan@student.tub.test'],
        ['Staff', 'staff@tub.test'],
        ['Electrical worker', 'electrical@tub.test'],
        ['Plumbing worker', 'plumbing@tub.test'],
        ['Furniture worker', 'furniture@tub.test'],
        ['Laboratory worker', 'laboratory@tub.test'],
        ['Building worker', 'building@tub.test'],
        ['Housekeeping worker', 'housekeeping@tub.test'],
    ];
    echo '<div class="sheet-wrap"><table class="sheet compact">';
    echo '<thead><tr><th>Account</th><th>Email</th><th>Password</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr><td>' . e($row[0]) . '</td><td>' . e($row[1]) . '</td><td>' . e(DEMO_PASSWORD) . '</td></tr>';
    }
    echo '</tbody></table></div>';
}

function login_user(array $user)
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    unset($_SESSION['csrf']);
}

function export_complaints_csv(array $user, array $filters)
{
    $rows = fetch_complaints($user, $filters, null, 0);
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="fixflow-report-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Ticket', 'Problem', 'Category', 'Location', 'Priority', 'Status', 'Filed by', 'Assigned to', 'Filed']);
    foreach ($rows as $row) {
        fputcsv($out, [
            $row['ticket_no'],
            $row['title'],
            $row['category_name'],
            $row['location'],
            priority_label($row['priority']),
            status_label($row['status']),
            $row['reporter_name'],
            $row['worker_name'] ? $row['worker_name'] : '',
            format_when($row['created_at']),
        ]);
    }
    fclose($out);
    exit;
}
