<?php

require "../config/session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== "worker") {
    header("Location: ../login.php");
    exit();
}

require "../config/database.php";

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Count available jobs
$totalJobs = $conn->query("SELECT COUNT(*) FROM jobs WHERE status='open'")->fetchColumn();

// Count applications this worker has sent
$appStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE worker_id = ?");
$appStmt->execute([$user_id]);
$totalApplications = $appStmt->fetchColumn();

// Count jobs this worker has completed
// (accepted application whose linked job is marked 'completed')
$completedStmt = $conn->prepare("
    SELECT COUNT(*)
    FROM applications a
    INNER JOIN jobs j ON a.job_id = j.id
    WHERE a.worker_id = ?
    AND a.status = 'Accepted'
    AND j.status = 'completed'
");
$completedStmt->execute([$user_id]);
$totalCompleted = $completedStmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard | GigBoard</title>

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
        --success-bg: #dcfce7;
        --success-text: #166534;
    }

    html,
    body {
        font-family: 'Nunito', sans-serif;
        color: var(--text-primary);
        background: var(--bg);
        height: 100%;
    }

    /* ── Sidebar ────────────────────────────────────────────────────── */
    .sidebar {
        width: 260px;
        background: linear-gradient(180deg, var(--purple-deep), var(--purple-mid) 130%);
        color: #fff;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        padding: 32px 26px;
        overflow-y: auto;
    }

    .sidebar h2 {
        font-family: 'Outfit', sans-serif;
        display: flex;
        align-items: center;
        gap: .6rem;
        color: #fff;
        margin-bottom: 40px;
        font-size: 21px;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .sidebar h2 .brand-mark {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: rgba(255, 255, 255, .16);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sidebar nav a {
        display: flex;
        align-items: center;
        gap: 13px;
        color: rgba(255, 255, 255, .72);
        text-decoration: none;
        padding: 13px 16px;
        border-radius: 12px;
        margin-bottom: 6px;
        transition: all 0.25s ease;
        font-size: 14.5px;
        font-weight: 600;
        font-family: 'Outfit', sans-serif;
    }

    .sidebar nav a svg {
        width: 19px;
        height: 19px;
        flex-shrink: 0;
        stroke: currentColor;
    }

    .sidebar nav a:hover {
        background: rgba(255, 255, 255, .12);
        color: #fff;
        transform: translateX(4px);
    }

    .sidebar nav a.logout {
        margin-top: 18px;
        border-top: 1px solid rgba(255, 255, 255, .14);
        padding-top: 20px;
        color: rgba(255, 255, 255, .55);
    }

    .sidebar nav a.logout:hover {
        color: #fecaca;
        background: rgba(239, 68, 68, .12);
    }

    /* ── Main ───────────────────────────────────────────────────────── */
    .main {
        margin-left: 260px;
        padding: 44px 48px;
        min-height: 100vh;
    }

    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 36px;
    }

    .topbar h1 {
        font-family: 'Outfit', sans-serif;
        color: var(--text-primary);
        font-size: 30px;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .topbar p {
        color: var(--text-muted);
        font-size: 15px;
        margin-top: 5px;
        font-weight: 500;
    }

    /* ── Stat Cards ─────────────────────────────────────────────────── */
    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 22px;
        margin-bottom: 36px;
    }

    .card {
        background: var(--card-bg);
        padding: 26px;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
        animation: slideUp 0.5s ease both;
    }

    .card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-md);
        border-color: var(--purple-light);
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(18px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card .icon-badge {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--purple-pale);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
    }

    .card .icon-badge svg {
        width: 22px;
        height: 22px;
        stroke: var(--purple-mid);
    }

    .card h2 {
        font-family: 'Outfit', sans-serif;
        color: var(--text-primary);
        margin-bottom: 6px;
        font-size: 32px;
        font-weight: 800;
    }

    .card p {
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* ── Quick Actions ──────────────────────────────────────────────── */
    .action {
        background: var(--card-bg);
        padding: 30px;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
    }

    .action h2 {
        font-family: 'Outfit', sans-serif;
        color: var(--text-primary);
        margin-bottom: 20px;
        font-size: 18px;
        font-weight: 700;
        padding-bottom: 14px;
        border-bottom: 2px solid var(--purple-pale);
    }

    .btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        background: linear-gradient(135deg, var(--purple-mid), #9333ea);
        color: #fff;
        text-decoration: none;
        padding: 13px 26px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 16px rgba(109, 40, 217, .3);
    }

    .btn svg {
        width: 17px;
        height: 17px;
        stroke: currentColor;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(109, 40, 217, .4);
    }

    .btn:active {
        transform: translateY(-1px);
    }

    .btn.btn-secondary {
        background: var(--purple-pale);
        color: var(--purple-mid);
        box-shadow: none;
    }

    .btn.btn-secondary:hover {
        background: #ded6fb;
        box-shadow: 0 4px 14px rgba(109, 40, 217, .18);
    }

    /* ── Responsive ─────────────────────────────────────────────────── */
    @media (max-width: 900px) {
        .sidebar {
            width: 84px;
            padding: 24px 14px;
        }

        .sidebar h2 span.label {
            display: none;
        }

        .sidebar h2 {
            justify-content: center;
        }

        .sidebar nav a span {
            display: none;
        }

        .sidebar nav a {
            justify-content: center;
            padding: 13px;
        }

        .main {
            margin-left: 84px;
            padding: 28px 22px;
        }
    }
    </style>

</head>

<body>

    <div class="sidebar">

        <h2>
            <span class="brand-mark">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                </svg>
            </span>
            <span class="label">GigBoard</span>
        </h2>

        <nav>
            <a href="dashboard.php">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9.5 12 3l9 6.5" />
                    <path d="M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="browse_jobs.php">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="7" width="18" height="13" rx="2" />
                    <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    <path d="M3 12h18" />
                </svg>
                <span>Browse Jobs</span>
            </a>

            <a href="my_applications.php">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                </svg>
                <span>Applications</span>
            </a>

            <a href="profile.php">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M4 21c0-4 4-7 8-7s8 3 8 7" />
                </svg>
                <span>Profile</span>
            </a>

            <a href="../logout.php" class="logout">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>
                <span>Logout</span>
            </a>
        </nav>

    </div>


    <div class="main">

        <div class="topbar">

            <div>
                <h1>Welcome, <?php echo htmlspecialchars($user['fullname']); ?></h1>
                <p>Find your next opportunity today.</p>
            </div>

        </div>


        <div class="cards">

            <div class="card" style="animation-delay: 0.1s;">
                <div class="icon-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                </div>
                <h2><?php echo $totalJobs; ?></h2>
                <p>Available Jobs</p>
            </div>

            <div class="card" style="animation-delay: 0.2s;">
                <div class="icon-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13" />
                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                    </svg>
                </div>
                <h2><?php echo $totalApplications; ?></h2>
                <p>Applications Sent</p>
            </div>

            <div class="card" style="animation-delay: 0.3s;">
                <div class="icon-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                </div>
                <h2><?php echo $totalCompleted; ?></h2>
                <p>Jobs Completed</p>
            </div>

        </div>


        <div class="action">

            <h2>Quick Actions</h2>

            <div class="btn-group">
                <a href="browse_jobs.php" class="btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    Browse Jobs
                </a>

                <a href="profile.php" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                    Edit Profile
                </a>
            </div>

        </div>

    </div>

</body>

</html>