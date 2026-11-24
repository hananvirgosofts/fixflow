<?php
/**
 * Creates the FixFlow database, tables, demo accounts, and sample complaints.
 * Run once from the browser, then sign in.
 */
require __DIR__ . '/includes/init.php';

$lock = __DIR__ . '/storage/installed.lock';
$checks = [
    'PHP 7.4 or newer' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'PDO MySQL extension' => extension_loaded('pdo_mysql'),
    'Uploads folder is writable' => is_writable(__DIR__ . '/uploads'),
    'Storage folder is writable' => is_writable(__DIR__ . '/storage'),
];
$ready = !in_array(false, $checks, true);
$message = '';
$error = '';
$installed = is_file($lock) || database_has_accounts();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$ready) {
        $error = 'Fix the checks below, then run the installation again.';
    } elseif (!csrf_ok()) {
        $error = 'The form expired. Refresh the page and try again.';
    } elseif ($installed) {
        $error = 'FixFlow is already installed.';
    } else {
        mysqli_report(MYSQLI_REPORT_OFF);
        $mysqli = open_install_mysql();
        if ($mysqli->connect_errno) {
            $error = 'Could not connect to MySQL. Start MySQL in XAMPP and check includes/config.php. ' . $mysqli->connect_error;
        } else {
            $mysqli->set_charset(DB_CHARSET);
            $dbName = str_replace('`', '', DB_NAME);
            if (!$mysqli->select_db($dbName)) {
                $created = $mysqli->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                if (!$created || !$mysqli->select_db($dbName)) {
                    $error = 'The database could not be created. Create an empty MySQL database named ' . $dbName . ' and try again. ' . $mysqli->error;
                }
            }
            if ($error === '') {
                $sql = file_get_contents(__DIR__ . '/database/schema.sql');
                if (!$mysqli->multi_query($sql)) {
                    $error = 'The tables could not be created. ' . $mysqli->error;
                } else {
                    do {
                        if ($result = $mysqli->store_result()) {
                            $result->free();
                        }
                    } while ($mysqli->more_results() && $mysqli->next_result());
                    if ($mysqli->errno) {
                        $error = 'The tables could not be created. ' . $mysqli->error;
                    }
                }
            }
            $mysqli->close();
        }

        if ($error === '') {
            try {
                $outcome = seed_fixflow();
                @file_put_contents($lock, 'Installed ' . date('c'));
                $installed = true;
                $message = $outcome === 'exists'
                    ? 'The database already had accounts. Installation was marked complete.'
                    : 'FixFlow is installed. Sign in with a demo account, or create a student account.';
            } catch (Exception $e) {
                error_log('FixFlow install: ' . $e->getMessage());
                $error = 'The sample records could not be saved. ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Install';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require __DIR__ . '/includes/head.php'; ?>
</head>
<body class="public">
<main class="install-wrap" id="content">
    <a class="wordmark" href="index.php"><span class="mark" aria-hidden="true"></span>FixFlow</a>
    <h1>Install the desk</h1>
    <p>This creates the MySQL database <strong>fixflow</strong>, the tables, nine demo accounts, and ten sample complaints. It is meant for a local XAMPP server.</p>
    <?php if ($message): ?>
        <div class="flash flash-success" role="status"><?= e($message) ?></div>
        <?php render_demo_accounts(); ?>
        <p class="form-actions"><a class="ff-btn" href="login.php">Sign in</a></p>
    <?php elseif ($installed): ?>
        <div class="flash flash-success" role="status">FixFlow is already installed.</div>
        <p class="form-actions"><a class="ff-btn" href="login.php">Sign in</a></p>
        <p class="help">To install again, drop the fixflow database in phpMyAdmin and delete storage/installed.lock.</p>
    <?php else: ?>
        <?php if ($error): ?><div class="flash flash-error" role="alert"><?= e($error) ?></div><?php endif; ?>
        <ul class="check-list">
            <?php foreach ($checks as $label => $ok): ?>
                <li><?= e($label) ?>: <?= $ok ? 'ready' : 'needs attention' ?></li>
            <?php endforeach; ?>
        </ul>
        <form method="post" action="install.php">
            <?= csrf_field() ?>
            <button class="ff-btn" type="submit" <?= $ready ? '' : 'disabled' ?>>Install FixFlow</button>
        </form>
        <p class="help">Database user <strong><?= e(DB_USER) ?></strong> on <strong><?= e(DB_HOST) ?></strong>. Change includes/config.php if your MySQL password is not empty.</p>
    <?php endif; ?>
</main>
</body>
</html>
<?php

function open_install_mysql()
{
    if (DB_SSL) {
        $mysqli = mysqli_init();
        $mysqli->ssl_set(null, null, '/etc/ssl/certs/ca-certificates.crt', null, null);
        @$mysqli->real_connect(DB_HOST, DB_USER, DB_PASS, '', (int) DB_PORT, null, MYSQLI_CLIENT_SSL);
        return $mysqli;
    }
    return @new mysqli(DB_HOST, DB_USER, DB_PASS, '', (int) DB_PORT);
}

function database_has_accounts()
{
    try {
        $pdo = connect_database(true);
        if (!$pdo) {
            return false;
        }
        return (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function seed_fixflow()
{
    $pdo = db();
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        return 'exists';
    }

    $photo = write_sample_photo(__DIR__ . '/uploads/sample-projector.png');
    $hash = password_hash(DEMO_PASSWORD, PASSWORD_DEFAULT);
    $now = date('Y-m-d H:i:s');

    $pdo->beginTransaction();
    try {
        $categoryIds = [];
        $insertCategory = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
        foreach (category_catalog() as $category) {
            $insertCategory->execute($category);
            $categoryIds[$category[0]] = (int) $pdo->lastInsertId();
        }

        $accounts = [
            'admin' => ['System Administrator', 'admin@tub.test', 'admin', 'Administration', null, '0300-0000001'],
            'hanan' => ['Abdul Hanan', 'hanan@student.tub.test', 'student', 'Department of CS & IT', null, '0300-0000008'],
            'staff' => ['Faculty Office', 'staff@tub.test', 'staff', 'Department of Physics', null, '0300-0000003'],
            'electrical' => ['Electrical Unit', 'electrical@tub.test', 'worker', 'Campus Maintenance', 'Electrical', '0300-0000011'],
            'plumbing' => ['Plumbing Unit', 'plumbing@tub.test', 'worker', 'Campus Maintenance', 'Plumbing', '0300-0000012'],
            'furniture' => ['Furniture Unit', 'furniture@tub.test', 'worker', 'Campus Maintenance', 'Furniture', '0300-0000013'],
            'laboratory' => ['Laboratory Unit', 'laboratory@tub.test', 'worker', 'Campus Maintenance', 'Laboratory Equipment', '0300-0000014'],
            'building' => ['Building Unit', 'building@tub.test', 'worker', 'Campus Maintenance', 'Civil / Building', '0300-0000015'],
            'housekeeping' => ['Housekeeping Unit', 'housekeeping@tub.test', 'worker', 'Campus Maintenance', 'Housekeeping', '0300-0000016'],
        ];
        $userIds = [];
        $insertUser = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role, phone, department, specialty, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)');
        foreach ($accounts as $key => $account) {
            $insertUser->execute([$account[0], $account[1], $hash, $account[2], $account[5], $account[3], $account[4], $now]);
            $userIds[$key] = (int) $pdo->lastInsertId();
        }

        $samples = sample_complaints();
        $insertComplaint = $pdo->prepare('INSERT INTO complaints (ticket_no, user_id, category_id, title, description, location, priority, status, image_path, assigned_to, rejection_reason, created_at, updated_at, resolved_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insertUpdate = $pdo->prepare('INSERT INTO complaint_updates (complaint_id, user_id, status, note, created_at) VALUES (?, ?, ?, ?, ?)');
        $insertFeedback = $pdo->prepare('INSERT INTO feedback (complaint_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?)');
        $year = date('Y');

        foreach ($samples as $index => $sample) {
            $created = hours_ago($sample['created_hours']);
            $latest = $sample['created_hours'];
            $resolvedAt = null;
            foreach ($sample['updates'] as $update) {
                if ($update[2] < $latest) {
                    $latest = $update[2];
                }
                if ($update[0] === 'resolved') {
                    $resolvedAt = hours_ago($update[2]);
                }
            }
            $ticket = 'FF-' . $year . '-' . (1001 + $index);
            $workerId = $sample['worker'] ? $userIds[$sample['worker']] : null;
            $image = ($index === 0 && $photo) ? 'sample-projector.png' : null;
            $insertComplaint->execute([
                $ticket,
                $userIds[$sample['owner']],
                $categoryIds[$sample['category']],
                $sample['title'],
                $sample['description'],
                $sample['location'],
                $sample['priority'],
                $sample['status'],
                $image,
                $workerId,
                $sample['rejection'],
                $created,
                hours_ago($latest),
                $resolvedAt,
            ]);
            $complaintId = (int) $pdo->lastInsertId();
            foreach ($sample['updates'] as $update) {
                $actor = $update[3] === 'owner' ? $userIds[$sample['owner']] : ($update[3] === 'admin' ? $userIds['admin'] : $userIds[$sample['worker']]);
                $insertUpdate->execute([$complaintId, $actor, $update[0], $update[1], hours_ago($update[2])]);
            }
            if (!empty($sample['feedback'])) {
                $insertFeedback->execute([
                    $complaintId,
                    $userIds[$sample['owner']],
                    $sample['feedback'][0],
                    $sample['feedback'][1],
                    hours_ago($sample['feedback'][2]),
                ]);
            }
        }

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
    return 'seeded';
}

function hours_ago($hours)
{
    $dt = new DateTime('now');
    $dt->modify('-' . (int) $hours . ' hours');
    return $dt->format('Y-m-d H:i:s');
}

function write_sample_photo($path)
{
    if (!function_exists('imagecreatetruecolor')) {
        return false;
    }
    $image = imagecreatetruecolor(960, 540);
    $navy = imagecolorallocate($image, 22, 56, 74);
    $paper = imagecolorallocate($image, 247, 249, 249);
    $ink = imagecolorallocate($image, 23, 48, 58);
    $brass = imagecolorallocate($image, 111, 67, 28);
    imagefilledrectangle($image, 0, 0, 960, 540, $navy);
    imagefilledrectangle($image, 36, 36, 924, 504, $paper);
    imagefilledrectangle($image, 36, 36, 52, 504, $brass);
    imagestring($image, 5, 80, 80, 'CS Lab 2', $ink);
    imagestring($image, 5, 80, 120, 'Projector does not power on.', $ink);
    imagestring($image, 3, 80, 170, 'Sample photograph for the first practice complaint.', $ink);
    $saved = imagepng($image, $path);
    imagedestroy($image);
    return (bool) $saved;
}

function sample_complaints()
{
    return [
        [
            'owner' => 'hanan',
            'worker' => 'electrical',
            'category' => 'Electrical',
            'title' => 'Projector will not power on',
            'description' => 'The ceiling projector in CS Lab 2 does not power on. The remote and the wall switch were both tried. Classes in this lab cannot use slides until it is repaired.',
            'location' => 'CS Lab 2, Academic Block',
            'priority' => 'high',
            'status' => 'in_progress',
            'rejection' => null,
            'created_hours' => 30,
            'updates' => [
                ['submitted', 'Complaint filed.', 30, 'owner'],
                ['assigned', 'Assigned to Electrical Unit.', 26, 'admin'],
                ['in_progress', 'Power supply checked. The lamp needs replacement.', 5, 'worker'],
            ],
        ],
        [
            'owner' => 'hanan',
            'worker' => null,
            'category' => 'Plumbing',
            'title' => 'Washroom tap will not close',
            'description' => 'The tap in the common washroom on the first floor keeps running after it is closed. Water is pooling on the floor.',
            'location' => 'Boys Hostel Block A',
            'priority' => 'urgent',
            'status' => 'submitted',
            'rejection' => null,
            'created_hours' => 2,
            'updates' => [
                ['submitted', 'Complaint filed.', 2, 'owner'],
            ],
        ],
        [
            'owner' => 'hanan',
            'worker' => 'furniture',
            'category' => 'Furniture',
            'title' => 'Chair leg is broken',
            'description' => 'A student chair beside the third window has a loose leg and is unsafe to sit on.',
            'location' => 'Room CS-14',
            'priority' => 'low',
            'status' => 'resolved',
            'rejection' => null,
            'created_hours' => 300,
            'updates' => [
                ['submitted', 'Complaint filed.', 300, 'owner'],
                ['assigned', 'Assigned to Furniture Unit.', 290, 'admin'],
                ['in_progress', 'The chair was removed from the row.', 280, 'worker'],
                ['resolved', 'The broken chair was replaced with a spare from the store.', 260, 'worker'],
            ],
            'feedback' => [5, 'The chair was replaced and the row is safe to use.', 250],
        ],
        [
            'owner' => 'staff',
            'worker' => 'laboratory',
            'category' => 'Laboratory Equipment',
            'title' => 'Microscope lamp has failed',
            'description' => 'The lamp in microscope station 2 does not light. Tomorrow\'s lab session needs this station.',
            'location' => 'Botany Laboratory',
            'priority' => 'medium',
            'status' => 'assigned',
            'rejection' => null,
            'created_hours' => 50,
            'updates' => [
                ['submitted', 'Complaint filed.', 50, 'owner'],
                ['assigned', 'Assigned to Laboratory Unit.', 40, 'admin'],
            ],
        ],
        [
            'owner' => 'staff',
            'worker' => 'electrical',
            'category' => 'Electrical',
            'title' => 'Ceiling fan vibrates at full speed',
            'description' => 'The ceiling fan above the clerk\'s desk shakes when it runs on the highest setting. The noise carries into the next office.',
            'location' => 'Admin Office 3',
            'priority' => 'medium',
            'status' => 'resolved',
            'rejection' => null,
            'created_hours' => 450,
            'updates' => [
                ['submitted', 'Complaint filed.', 450, 'owner'],
                ['assigned', 'Assigned to Electrical Unit.', 440, 'admin'],
                ['in_progress', 'The fan was switched off and the bracket was inspected.', 430, 'worker'],
                ['resolved', 'The bracket bolts were tightened and the fan no longer vibrates.', 400, 'worker'],
            ],
            'feedback' => [4, 'The fan is steady now.', 390],
        ],
        [
            'owner' => 'staff',
            'worker' => null,
            'category' => 'Civil / Building',
            'title' => 'Window pane is cracked',
            'description' => 'A glass pane on the north window of the reading hall is cracked. It is still in the frame.',
            'location' => 'Central Library, reading hall',
            'priority' => 'high',
            'status' => 'submitted',
            'rejection' => null,
            'created_hours' => 70,
            'updates' => [
                ['submitted', 'Complaint filed.', 70, 'owner'],
            ],
        ],
        [
            'owner' => 'hanan',
            'worker' => null,
            'category' => 'HVAC',
            'title' => 'Air conditioner is not cooling',
            'description' => 'The split air conditioner in the CS faculty room blows warm air and does not cool the room.',
            'location' => 'CS Faculty Room',
            'priority' => 'high',
            'status' => 'rejected',
            'rejection' => 'Duplicate of an open complaint for the same room.',
            'created_hours' => 150,
            'updates' => [
                ['submitted', 'Complaint filed.', 150, 'owner'],
                ['rejected', 'Rejected. Duplicate of an open complaint for the same room.', 140, 'admin'],
            ],
        ],
        [
            'owner' => 'staff',
            'worker' => 'furniture',
            'category' => 'Furniture',
            'title' => 'Whiteboard is pulling away from the wall',
            'description' => 'The right side of the main whiteboard has come away from the wall. It shifts when it is written on.',
            'location' => 'Physics Lecture Hall',
            'priority' => 'medium',
            'status' => 'in_progress',
            'rejection' => null,
            'created_hours' => 100,
            'updates' => [
                ['submitted', 'Complaint filed.', 100, 'owner'],
                ['assigned', 'Assigned to Furniture Unit.', 90, 'admin'],
                ['in_progress', 'The loose side was propped. New wall anchors are needed.', 20, 'worker'],
            ],
        ],
        [
            'owner' => 'hanan',
            'worker' => 'housekeeping',
            'category' => 'Housekeeping',
            'title' => 'Wet floor outside the washroom',
            'description' => 'The floor outside the ground-floor washroom stays wet through the afternoon. People are slipping.',
            'location' => 'Academic Block, ground floor',
            'priority' => 'low',
            'status' => 'resolved',
            'rejection' => null,
            'created_hours' => 220,
            'updates' => [
                ['submitted', 'Complaint filed.', 220, 'owner'],
                ['assigned', 'Assigned to Housekeeping Unit.', 210, 'admin'],
                ['in_progress', 'Warning signs were placed and the floor was mopped.', 200, 'worker'],
                ['resolved', 'The floor was dried and the afternoon check was added to the round.', 190, 'worker'],
            ],
            'feedback' => [3, 'The floor was dried, but it was wet again the next afternoon.', 180],
        ],
        [
            'owner' => 'staff',
            'worker' => 'building',
            'category' => 'General',
            'title' => 'Door closer is broken',
            'description' => 'The door closer on the department office door no longer pulls the door shut. The door stays open.',
            'location' => 'Department of CS & IT office',
            'priority' => 'medium',
            'status' => 'assigned',
            'rejection' => null,
            'created_hours' => 36,
            'updates' => [
                ['submitted', 'Complaint filed.', 36, 'owner'],
                ['assigned', 'Assigned to Building Unit.', 30, 'admin'],
            ],
        ],
    ];
}
