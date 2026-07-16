<?php
session_start();

// ── Redirect if not logged in ─────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ── Database Connection (adjust credentials) ──────────────────────────────────
$conn = new mysqli("localhost", "root", "", "gigboard");
if ($conn->connect_error) {
    die("<p style='color:red;padding:2rem;text-align:center;'>DB Error: " . $conn->connect_error . "</p>");
}

$user_id = (int) $_SESSION['user_id'];

// ── Fetch user ────────────────────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user_data) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// ── Application stats ─────────────────────────────────────────────────────────
// Note: Changed 'user_id' to 'id' to fix the unknown column error
$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total_applied,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) AS total_accepted
    FROM applications
    WHERE id = ?
");

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Completed = accepted applications whose linked job is 'completed'
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total_completed
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE a.id = ? AND j.status = 'completed'
");

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $completed_row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$total_applied   = (int)($stats['total_applied']           ?? 0);
$total_accepted  = (int)($stats['total_accepted']          ?? 0);
$total_completed = (int)($completed_row['total_completed'] ?? 0);

// ── Recent applications ───────────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT a.status, a.created_at, j.title AS job_title
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE a.id = ?
    ORDER BY a.created_at DESC
    LIMIT 5
");

$recent = [];
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $recent = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();

// ── Safe display values ───────────────────────────────────────────────────────
$full_name   = htmlspecialchars($user_data['full_name']   ?? 'Unknown');
$username    = htmlspecialchars($user_data['username']    ?? '');
$email       = htmlspecialchars($user_data['email']       ?? '');
$phone       = htmlspecialchars($user_data['phone']       ?? '');
$bio         = htmlspecialchars($user_data['bio']         ?? '');
$location    = htmlspecialchars($user_data['location']    ?? '');
$role        = ucfirst($user_data['role']                 ?? 'worker');

// Fields added later — safe fallback if column doesn't exist yet
$gender      = htmlspecialchars($user_data['gender']      ?? '');
$dob         = htmlspecialchars($user_data['dob']         ?? '');
$profession  = htmlspecialchars($user_data['profession']  ?? '');
$experience  = htmlspecialchars($user_data['experience']  ?? '');
$education   = htmlspecialchars($user_data['education']   ?? '');

// Skills: comma-separated → array
$skills = [];
if (!empty($user_data['skills'])) {
    $skills = array_filter(array_map('trim', explode(',', $user_data['skills'])));
}

// Profile picture — fallback to generated avatar if missing
$pic_path = !empty($user_data['profile_pic'])
    ? 'uploads/' . htmlspecialchars($user_data['profile_pic'])
    : 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=6d28d9&color=fff&size=120';

// ── Helpers ───────────────────────────────────────────────────────────────────
function badge_class(string $s): string {
    return match(strtolower($s)) {
        'accepted'  => 'badge-success',
        'pending'   => 'badge-pending',
        'rejected'  => 'badge-rejected',
        default     => 'badge-default',
    };
}
function status_icon(string $s): string {
    return match(strtolower($s)) {
        'accepted' => '✔',
        'pending'  => '⏳',
        'rejected' => '✖',
        default    => '•',
    };
}
function val(string $v, string $fallback = 'Not specified'): string {
    return !empty($v) ? $v : '<span class="empty">' . $fallback . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – GigBoard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    /* ── Reset ──────────────────────────────────────────────────────── */
    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    :root {
        --purple-deep: #3b0764;
        --purple-mid: #6d28d9;
        --purple-light: #a78bfa;
        --purple-pale: #ede9fe;
        --bg: #f5f3ff;
        --card-bg: #ffffff;
        --text-primary: #1e1b4b;
        --text-muted: #6b7280;
        --border: #e5e7eb;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08), 0 1px 2px rgba(0, 0, 0, .06);
        --shadow-md: 0 4px 20px rgba(109, 40, 217, .14);
        --radius: 16px;
    }

    body {
        font-family: 'Nunito', sans-serif;
        background: var(--bg);
        color: var(--text-primary);
        min-height: 100vh;
    }

    /* ── Header ─────────────────────────────────────────────────────── */
    .profile-header {
        background: linear-gradient(135deg, var(--purple-deep) 0%, var(--purple-mid) 60%, #9333ea 100%);
        padding: 2.75rem 1.5rem 5rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .back-link {
        position: absolute;
        top: 1.2rem;
        left: 1.2rem;
        color: rgba(255, 255, 255, .8);
        text-decoration: none;
        font-family: 'Outfit', sans-serif;
        font-size: .85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: .35rem;
        transition: color .2s;
    }

    .back-link:hover {
        color: #fff;
    }

    .avatar-wrap {
        position: relative;
        display: inline-block;
        margin-bottom: .875rem;
    }

    .avatar-wrap img {
        width: 116px;
        height: 116px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid rgba(255, 255, 255, .9);
        box-shadow: 0 8px 32px rgba(0, 0, 0, .28);
    }

    .avatar-online {
        width: 17px;
        height: 17px;
        background: #22c55e;
        border: 3px solid #fff;
        border-radius: 50%;
        position: absolute;
        bottom: 5px;
        right: 5px;
    }

    .profile-name {
        font-family: 'Outfit', sans-serif;
        font-size: 1.7rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: -.3px;
    }

    .profile-role-text {
        font-size: .92rem;
        color: rgba(255, 255, 255, .75);
        margin-top: .3rem;
        font-weight: 500;
    }

    .worker-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: rgba(255, 255, 255, .15);
        border: 1px solid rgba(255, 255, 255, .3);
        color: #fff;
        border-radius: 999px;
        padding: .32rem .95rem;
        font-size: .75rem;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase;
        margin-top: .7rem;
        backdrop-filter: blur(4px);
    }

    /* ── Content ─────────────────────────────────────────────────────── */
    .content-wrap {
        max-width: 720px;
        margin: -3rem auto 3rem;
        padding: 0 1rem;
    }

    /* ── Stats ───────────────────────────────────────────────────────── */
    .stats-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        overflow: hidden;
        margin-bottom: 1.15rem;
        animation: fadeUp .4s ease both;
    }

    .stat-item {
        text-align: center;
        padding: 1.5rem 1rem;
        border-right: 1px solid var(--border);
    }

    .stat-item:last-child {
        border-right: none;
    }

    .stat-number {
        font-family: 'Outfit', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        color: var(--purple-mid);
        display: block;
    }

    .stat-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--text-muted);
        margin-top: .25rem;
    }

    /* ── Section Cards ───────────────────────────────────────────────── */
    .card {
        background: var(--card-bg);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        padding: 1.5rem;
        margin-bottom: 1.15rem;
    }

    .card:nth-child(2) {
        animation: fadeUp .4s .05s ease both;
    }

    .card:nth-child(3) {
        animation: fadeUp .4s .10s ease both;
    }

    .card:nth-child(4) {
        animation: fadeUp .4s .15s ease both;
    }

    .card:nth-child(5) {
        animation: fadeUp .4s .20s ease both;
    }

    .card:nth-child(6) {
        animation: fadeUp .4s .25s ease both;
    }

    .card-title {
        font-family: 'Outfit', sans-serif;
        font-size: .97rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1.2rem;
        padding-bottom: .7rem;
        border-bottom: 2px solid var(--purple-pale);
        display: flex;
        align-items: center;
        gap: .45rem;
    }

    .card-title svg {
        color: var(--purple-mid);
        width: 17px;
        height: 17px;
        flex-shrink: 0;
    }

    /* Bio */
    .bio-text {
        color: var(--text-muted);
        line-height: 1.75;
        font-size: .92rem;
    }

    /* Info grid */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .9rem;
    }

    .info-label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: var(--purple-mid);
        margin-bottom: .22rem;
    }

    .info-value {
        font-size: .9rem;
        color: var(--text-primary);
        font-weight: 500;
    }

    .info-value .empty {
        color: var(--text-muted);
        font-style: italic;
    }

    /* Skills */
    .skills-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        margin-top: .3rem;
    }

    .skill-tag {
        background: var(--purple-pale);
        color: var(--purple-mid);
        border-radius: 999px;
        padding: .28rem .85rem;
        font-size: .78rem;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        letter-spacing: .3px;
    }

    /* Applications */
    .app-list {
        display: flex;
        flex-direction: column;
        gap: .7rem;
    }

    .app-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .85rem 1rem;
        background: var(--bg);
        border-radius: 10px;
        border-left: 4px solid var(--purple-light);
    }

    .app-job-title {
        font-weight: 700;
        font-size: .9rem;
        color: var(--text-primary);
    }

    .app-date {
        font-size: .73rem;
        color: var(--text-muted);
        margin-top: .18rem;
    }

    .badge {
        font-family: 'Outfit', sans-serif;
        font-size: .7rem;
        font-weight: 700;
        padding: .3rem .75rem;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: .4px;
        white-space: nowrap;
    }

    .badge-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-pending {
        background: #fef9c3;
        color: #854d0e;
    }

    .badge-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-default {
        background: #f3f4f6;
        color: #374151;
    }

    /* Edit button */
    .edit-btn-wrap {
        text-align: center;
        margin-top: .5rem;
    }

    .edit-btn {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        background: linear-gradient(135deg, var(--purple-mid), #9333ea);
        color: #fff;
        text-decoration: none;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: .95rem;
        padding: .875rem 2.5rem;
        border-radius: 999px;
        box-shadow: 0 4px 16px rgba(109, 40, 217, .35);
        transition: transform .2s, box-shadow .2s;
    }

    .edit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(109, 40, 217, .45);
    }

    .edit-btn svg {
        width: 16px;
        height: 16px;
    }

    .empty-state {
        text-align: center;
        padding: 1.5rem;
        color: var(--text-muted);
        font-size: .9rem;
    }

    /* ── Animations ──────────────────────────────────────────────────── */
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(18px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ── Responsive ──────────────────────────────────────────────────── */
    @media (max-width: 500px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .profile-name {
            font-size: 1.4rem;
        }

        .stat-number {
            font-size: 1.55rem;
        }
    }
    </style>
</head>

<body>

    <!-- ═══ HEADER ════════════════════════════════════════════════════════════════ -->
    <header class="profile-header">
        <a href="dashboard.php" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor"
                stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M19 12H5M12 5l-7 7 7 7" />
            </svg>
            Dashboard
        </a>

        <div class="avatar-wrap">
            <img src="<?= $pic_path ?>" alt="Profile of <?= $full_name ?>"
                onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?= urlencode($full_name) ?>&background=6d28d9&color=fff&size=120'">
            <span class="avatar-online" title="Online"></span>
        </div>

        <h1 class="profile-name"><?= $full_name ?></h1>
        <p class="profile-role-text">
            <?= !empty($profession) ? $profession : $role ?>
            <?= !empty($location) ? ' &nbsp;·&nbsp; ' . $location : '' ?>
        </p>

        <div style="margin-top:.7rem;">
            <span class="worker-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor"
                    stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 2a5 5 0 1 0 0 10A5 5 0 0 0 12 2zM20 21a8 8 0 1 0-16 0" />
                </svg>
                <?= $role ?>
            </span>
        </div>
    </header>

    <!-- ═══ CONTENT ══════════════════════════════════════════════════════════════ -->
    <main class="content-wrap">

        <!-- Stats -->
        <div class="stats-card">
            <div class="stat-item">
                <span class="stat-number"><?= $total_applied ?></span>
                <span class="stat-label">Jobs Applied</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= $total_accepted ?></span>
                <span class="stat-label">Accepted</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= $total_completed ?></span>
                <span class="stat-label">Completed</span>
            </div>
        </div>

        <!-- About Me -->
        <div class="card">
            <h2 class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4M12 8h.01" />
                </svg>
                About Me
            </h2>
            <p class="bio-text">
                <?= !empty($bio) ? nl2br($bio) : '<em style="color:#9ca3af;">No bio added yet. Click Edit Profile to add one.</em>' ?>
            </p>
        </div>

        <!-- Personal Information -->
        <div class="card">
            <h2 class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <path d="M16 2v4M8 2v4M3 10h18" />
                </svg>
                Personal Information
            </h2>
            <div class="info-grid">
                <div>
                    <div class="info-label">Full Name</div>
                    <div class="info-value"><?= $full_name ?></div>
                </div>
                <div>
                    <div class="info-label">Username</div>
                    <div class="info-value"><?= val($username) ?></div>
                </div>
                <div>
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= $email ?></div>
                </div>
                <div>
                    <div class="info-label">Phone</div>
                    <div class="info-value"><?= val($phone) ?></div>
                </div>
                <div>
                    <div class="info-label">Gender</div>
                    <div class="info-value"><?= val($gender) ?></div>
                </div>
                <div>
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value">
                        <?= !empty($dob) ? date('F j, Y', strtotime($dob)) : '<span class="empty">Not specified</span>' ?>
                    </div>
                </div>
                <div style="grid-column: 1/-1;">
                    <div class="info-label">Location / Address</div>
                    <div class="info-value"><?= val($location) ?></div>
                </div>
            </div>
        </div>

        <!-- Professional Information -->
        <div class="card">
            <h2 class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" />
                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                </svg>
                Professional Information
            </h2>
            <div class="info-grid">
                <div>
                    <div class="info-label">Profession</div>
                    <div class="info-value"><?= val($profession) ?></div>
                </div>
                <div>
                    <div class="info-label">Experience</div>
                    <div class="info-value"><?= val($experience) ?></div>
                </div>
                <div style="grid-column: 1/-1;">
                    <div class="info-label">Education</div>
                    <div class="info-value"><?= val($education) ?></div>
                </div>
            </div>

            <?php if (!empty($skills)) : ?>
            <div style="margin-top: 1.25rem;">
                <div class="info-label" style="margin-bottom:.45rem;">Skills</div>
                <div class="skills-wrap">
                    <?php foreach ($skills as $skill) : ?>
                    <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent Applications -->
        <div class="card">
            <h2 class="card-title">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                </svg>
                Recent Applications
            </h2>

            <?php if (!empty($recent)) : ?>
            <div class="app-list">
                <?php foreach ($recent as $app) :
                $bc   = badge_class($app['status']);
                $icon = status_icon($app['status']);
                $date = !empty($app['created_at'])
                      ? 'Applied ' . date('M j, Y', strtotime($app['created_at']))
                      : '';
            ?>
                <div class="app-item">
                    <div>
                        <div class="app-job-title"><?= $icon ?> <?= htmlspecialchars($app['job_title']) ?></div>
                        <?php if ($date) : ?>
                        <div class="app-date"><?= $date ?></div>
                        <?php endif; ?>
                    </div>
                    <span class="badge <?= $bc ?>"><?= ucfirst($app['status']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <div class="empty-state">No applications yet.</div>
            <?php endif; ?>
        </div>

        <!-- Edit Profile -->
        <div class="edit-btn-wrap">
            <a href="edit_profile.php" class="edit-btn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5"
                    viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
                Edit Profile
            </a>
        </div>

    </main>
</body>

</html>