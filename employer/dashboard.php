<?php
require "../config/session.php";
require "../config/database.php";

// Check login
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

// Get employer information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Count posted jobs
$count = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
$count->execute([$user_id]);
$total_jobs = $count->fetchColumn();

// ── Display helpers (no DB changes) ──
$hour       = (int)date('H');
$greeting   = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$cur_date   = date('l, F j, Y');

function getInitials($name) {
    $parts = explode(' ', trim($name));
    $ini = '';
    foreach($parts as $p) { if(!empty($p)) $ini .= strtoupper(substr($p, 0, 1)); }
    return substr($ini, 0, 2);
}

$initials   = getInitials($user['fullname']);
$first_name = explode(' ', trim($user['fullname']))[0];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard | GigBoard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
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
        --purple-paler: #f5f3ff;
        --bg: #f5f3ff;
        --card-bg: #ffffff;
        --text-primary: #1e1b4b;
        --text-secondary: #6b7280;
        --text-muted: #9ca3af;
        --border: #e5e7eb;
        --border-light: #f3f4f6;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        --shadow-md: 0 4px 20px rgba(109, 40, 217, .10);
        --shadow-lg: 0 12px 32px rgba(109, 40, 217, .12);
        --radius: 18px;
        --radius-sm: 12px;
        --sidebar-w: 264px;
        --green: #10b981;
        --green-dim: rgba(16, 185, 129, .10);
        --amber: #f59e0b;
        --amber-dim: rgba(245, 158, 11, .10);
    }

    html,
    body {
        height: 100%;
    }

    body {
        font-family: 'Nunito', sans-serif;
        background: var(--bg);
        color: var(--text-primary);
        display: flex;
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
    }

    /* ═══ SIDEBAR ═══════════════════════════════════════════════════════ */
    .sidebar {
        width: var(--sidebar-w);
        min-height: 100vh;
        background: linear-gradient(180deg, var(--purple-deep) 0%, var(--purple-mid) 100%);
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        z-index: 30;
        transition: transform .3s ease;
    }

    .sidebar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 240px;
        background: radial-gradient(ellipse at 50% 0%, rgba(167, 139, 250, .15) 0%, transparent 70%);
        pointer-events: none;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 26px 22px 22px;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
        position: relative;
        z-index: 1;
    }

    .brand-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: rgba(255, 255, 255, .12);
        border: 1.5px solid rgba(255, 255, 255, .2);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .brand-name {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 800;
        color: #fff;
        letter-spacing: -.01em;
    }

    .brand-name em {
        color: var(--purple-pale);
        font-style: normal;
    }

    /* Nav */
    .sidebar-nav {
        flex: 1;
        padding: 20px 14px;
        display: flex;
        flex-direction: column;
        gap: 4px;
        position: relative;
        z-index: 1;
    }

    .nav-section-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .35);
        padding: 10px 12px 6px;
        margin-top: 4px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 13px;
        border-radius: 11px;
        font-size: 14px;
        font-weight: 600;
        color: rgba(255, 255, 255, .65);
        text-decoration: none;
        cursor: pointer;
        transition: all .18s ease;
        position: relative;
        font-family: 'Nunito', sans-serif;
    }

    .nav-item:hover {
        background: rgba(255, 255, 255, .08);
        color: #fff;
    }

    .nav-item:focus-visible {
        outline: 2px solid var(--purple-light);
        outline-offset: 2px;
    }

    .nav-item svg {
        flex-shrink: 0;
        opacity: .7;
        transition: opacity .18s;
    }

    .nav-item:hover svg {
        opacity: 1;
    }

    .nav-item.active {
        background: rgba(255, 255, 255, .13);
        color: #fff;
        font-weight: 700;
    }

    .nav-item.active svg {
        opacity: 1;
    }

    .nav-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 8px;
        bottom: 8px;
        width: 3px;
        border-radius: 0 4px 4px 0;
        background: #fff;
    }

    /* User bottom */
    .sidebar-user {
        padding: 16px 14px;
        border-top: 1px solid rgba(255, 255, 255, .08);
        position: relative;
        z-index: 1;
    }

    .user-block {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px 10px;
        border-radius: 12px;
        transition: background .18s;
        margin-bottom: 6px;
    }

    .user-block:hover {
        background: rgba(255, 255, 255, .06);
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        background: rgba(255, 255, 255, .12);
        border: 1.5px solid rgba(255, 255, 255, .2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Outfit', sans-serif;
        font-size: 14px;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
        min-width: 0;
    }

    .user-name {
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role {
        font-size: 11px;
        color: rgba(255, 255, 255, .45);
        font-weight: 600;
        margin-top: 1px;
    }

    .logout-link {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 10px 10px;
        border-radius: 10px;
        font-family: 'Nunito', sans-serif;
        font-size: 13px;
        font-weight: 600;
        color: rgba(255, 255, 255, .45);
        text-decoration: none;
        cursor: pointer;
        transition: all .18s;
    }

    .logout-link:hover {
        background: rgba(239, 68, 68, .12);
        color: #fca5a5;
    }

    .logout-link svg {
        opacity: .7;
        transition: opacity .18s;
        flex-shrink: 0;
    }

    .logout-link:hover svg {
        opacity: 1;
    }

    /* ═══ MAIN ═════════════════════════════════════════════════════════ */
    .main {
        flex: 1;
        margin-left: var(--sidebar-w);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow-x: hidden;
    }

    /* Ambient orbs */
    .main::before {
        content: '';
        position: fixed;
        top: -180px;
        right: -120px;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(167, 139, 250, .08) 0%, transparent 65%);
        pointer-events: none;
        z-index: 0;
    }

    .main::after {
        content: '';
        position: fixed;
        bottom: -160px;
        left: 40%;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(109, 40, 217, .05) 0%, transparent 65%);
        pointer-events: none;
        z-index: 0;
    }

    /* Top bar */
    .topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 36px;
        background: rgba(255, 255, 255, .7);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--border-light);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .menu-toggle {
        display: none;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: var(--card-bg);
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--purple-mid);
    }

    .topbar-date {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .topbar-date svg {
        color: var(--purple-light);
    }

    .live-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: var(--green-dim);
        border: 1px solid rgba(16, 185, 129, .2);
        border-radius: 100px;
        padding: 5px 13px;
        font-size: 11px;
        font-weight: 700;
        color: var(--green);
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .live-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--green);
        animation: pulse 2s ease infinite;
    }

    /* Content */
    .content {
        padding: 36px 36px 60px;
        flex: 1;
        position: relative;
        z-index: 1;
    }

    /* Hero */
    .hero {
        margin-bottom: 36px;
        animation: slideUp .5s ease both;
    }

    .greeting-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--purple-mid);
        margin-bottom: 12px;
        display: block;
    }

    h1.greeting-text {
        font-family: 'Outfit', sans-serif;
        font-size: clamp(26px, 3.5vw, 40px);
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.1;
        margin-bottom: 10px;
        letter-spacing: -.02em;
    }

    .greeting-sub {
        font-size: 15px;
        color: var(--text-secondary);
        max-width: 520px;
        line-height: 1.65;
    }

    /* Section header */
    .section-head {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }

    .section-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--text-muted);
        white-space: nowrap;
    }

    .section-line {
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, var(--purple-pale), transparent);
    }

    /* Stats */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        margin-bottom: 36px;
    }

    .stat-card {
        background: var(--card-bg);
        border: 1px solid var(--border-light);
        border-radius: var(--radius);
        padding: 26px 24px;
        position: relative;
        overflow: hidden;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        animation: slideUp .5s ease both;
    }

    .stat-card:nth-child(1) {
        animation-delay: .06s;
    }

    .stat-card:nth-child(2) {
        animation-delay: .12s;
    }

    .stat-card:nth-child(3) {
        animation-delay: .18s;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        border-color: var(--purple-pale);
        box-shadow: var(--shadow-lg);
    }

    .stat-card::after {
        content: '';
        position: absolute;
        top: -24px;
        right: -24px;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        pointer-events: none;
    }

    .stat-card.c-purple::after {
        background: radial-gradient(circle, rgba(109, 40, 217, .08) 0%, transparent 70%);
    }

    .stat-card.c-light::after {
        background: radial-gradient(circle, rgba(167, 139, 250, .08) 0%, transparent 70%);
    }

    .stat-card.c-green::after {
        background: radial-gradient(circle, rgba(16, 185, 129, .08) 0%, transparent 70%);
    }

    .stat-icon-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon.purple {
        background: var(--purple-pale);
        border: 1px solid rgba(109, 40, 217, .12);
    }

    .stat-icon.light {
        background: rgba(167, 139, 250, .10);
        border: 1px solid rgba(167, 139, 250, .15);
    }

    .stat-icon.green {
        background: var(--green-dim);
        border: 1px solid rgba(16, 185, 129, .12);
    }

    .stat-trend {
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 100px;
    }

    .stat-trend.up {
        background: var(--green-dim);
        color: var(--green);
    }

    .stat-trend.neu {
        background: var(--border-light);
        color: var(--text-muted);
    }

    .stat-number {
        font-family: 'Outfit', sans-serif;
        font-size: 42px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 7px;
        display: block;
        letter-spacing: -.02em;
    }

    .stat-number.purple {
        color: var(--purple-mid);
    }

    .stat-number.light {
        color: var(--purple-light);
    }

    .stat-number.green {
        color: var(--green);
    }

    .stat-label {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 600;
    }

    /* Action cards */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }

    .action-card {
        background: var(--card-bg);
        border: 1px solid var(--border-light);
        border-radius: var(--radius);
        padding: 30px;
        text-decoration: none;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        animation: slideUp .5s ease both;
    }

    .action-card:nth-child(1) {
        animation-delay: .22s;
    }

    .action-card:nth-child(2) {
        animation-delay: .28s;
    }

    .action-card:focus-visible {
        outline: 2px solid var(--purple-mid);
        outline-offset: 2px;
    }

    .action-card:hover {
        transform: translateY(-4px);
        border-color: var(--purple-pale);
        box-shadow: var(--shadow-lg);
    }

    .action-card.primary:hover {
        box-shadow: var(--shadow-lg), 0 0 0 1px rgba(109, 40, 217, .15);
    }

    .action-card.secondary:hover {
        box-shadow: var(--shadow-lg), 0 0 0 1px rgba(167, 139, 250, .15);
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: -30px;
        right: -30px;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        pointer-events: none;
    }

    .action-card.primary::before {
        background: radial-gradient(circle, rgba(109, 40, 217, .07) 0%, transparent 70%);
    }

    .action-card.secondary::before {
        background: radial-gradient(circle, rgba(167, 139, 250, .07) 0%, transparent 70%);
    }

    .action-icon-wrap {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        flex-shrink: 0;
    }

    .action-icon-wrap.purple {
        background: var(--purple-pale);
        border: 1px solid rgba(109, 40, 217, .15);
    }

    .action-icon-wrap.light {
        background: rgba(167, 139, 250, .10);
        border: 1px solid rgba(167, 139, 250, .2);
    }

    .action-title {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 9px;
        letter-spacing: -.01em;
    }

    .action-desc {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.65;
        margin-bottom: 24px;
        flex: 1;
    }

    .action-cta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
    }

    .action-cta.purple {
        color: var(--purple-mid);
    }

    .action-cta.light {
        color: var(--purple-light);
    }

    .action-cta svg {
        transition: transform .18s;
    }

    .action-card:hover .action-cta svg {
        transform: translateX(5px);
    }

    /* Animations */
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(22px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: .5;
            transform: scale(.8);
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .stat-card,
        .action-card,
        .hero {
            animation: none;
        }

        .stat-card:hover,
        .action-card:hover {
            transform: none;
        }

        .live-dot {
            animation: none;
        }
    }

    /* Overlay for mobile sidebar */
    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(30, 27, 75, .4);
        backdrop-filter: blur(2px);
        z-index: 25;
    }

    .sidebar-overlay.show {
        display: block;
    }

    /* Responsive */
    @media (max-width: 900px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .main {
            margin-left: 0;
        }

        .menu-toggle {
            display: flex;
        }

        .topbar {
            padding: 14px 20px;
        }

        .content {
            padding: 24px 20px 50px;
        }

        .stats-row {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>

    <!-- Mobile overlay -->
    <div class="sidebar-overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- ═══ SIDEBAR ═════════════════════════════════════════════════════ -->
    <aside class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">

        <div class="sidebar-brand">
            <div class="brand-icon" aria-hidden="true">
                <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="7" width="20" height="14" rx="2" />
                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                </svg>
            </div>
            <span class="brand-name">Gig<em>Board</em></span>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-section-label">Menu</span>

            <a class="nav-item active" href="dashboard.php" aria-current="page">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <rect x="3" y="3" width="7" height="7" rx="1.5" />
                    <rect x="14" y="3" width="7" height="7" rx="1.5" />
                    <rect x="3" y="14" width="7" height="7" rx="1.5" />
                    <rect x="14" y="14" width="7" height="7" rx="1.5" />
                </svg>
                Dashboard
            </a>

            <a class="nav-item" href="post_job.php">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="16" />
                    <line x1="8" y1="12" x2="16" y2="12" />
                </svg>
                Post a Gig
            </a>

            <a class="nav-item" href="my_jobs.php">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                </svg>
                My Gigs
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="user-block">
                <div class="user-avatar" aria-hidden="true"><?=htmlspecialchars($initials);?></div>
                <div class="user-info">
                    <div class="user-name"><?=htmlspecialchars($user['fullname']);?></div>
                    <div class="user-role">Employer</div>
                </div>
            </div>
            <a class="logout-link" href="../logout.php">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>
                Sign out
            </a>
        </div>
    </aside>

    <!-- ═══ MAIN ═══════════════════════════════════════════════════════ -->
    <main class="main">

        <!-- Top bar -->
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:14px;">
                <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"
                        viewBox="0 0 24 24">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>
                <div class="topbar-date">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    <?=$cur_date;?>
                </div>
            </div>
            <div class="live-pill">
                <span class="live-dot" aria-hidden="true"></span>
                Live
            </div>
        </div>

        <div class="content">

            <!-- Hero greeting -->
            <section class="hero" aria-label="Welcome message">
                <span class="greeting-label">Employer Dashboard</span>
                <h1 class="greeting-text"><?=htmlspecialchars($greeting);?>, <?=htmlspecialchars($first_name);?></h1>
                <p class="greeting-sub">Your GigBoard command center — manage gigs, track applicants, and connect with
                    the best talent.</p>
            </section>

            <div class="section-head">
                <span class="section-label">Overview</span>
                <div class="section-line"></div>
            </div>

            <!-- Stats -->
            <div class="stats-row" role="region" aria-label="Statistics">

                <div class="stat-card c-purple">
                    <div class="stat-icon-row">
                        <div class="stat-icon purple" aria-hidden="true">
                            <svg width="20" height="20" fill="none" stroke="#6d28d9" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <rect x="2" y="7" width="20" height="14" rx="2" />
                                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                            </svg>
                        </div>
                        <span class="stat-trend up">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <polyline points="18 15 12 9 6 15" />
                            </svg>
                            Active
                        </span>
                    </div>
                    <span class="stat-number purple"><?=(int)$total_jobs;?></span>
                    <span class="stat-label">Gigs Posted</span>
                </div>

                <div class="stat-card c-light">
                    <div class="stat-icon-row">
                        <div class="stat-icon light" aria-hidden="true">
                            <svg width="20" height="20" fill="none" stroke="#a78bfa" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </div>
                        <span class="stat-trend neu">Total</span>
                    </div>
                    <span class="stat-number light">0</span>
                    <span class="stat-label">Applications Received</span>
                </div>

                <div class="stat-card c-green">
                    <div class="stat-icon-row">
                        <div class="stat-icon green" aria-hidden="true">
                            <svg width="20" height="20" fill="none" stroke="#10b981" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </div>
                        <span class="stat-trend neu">All time</span>
                    </div>
                    <span class="stat-number green">0</span>
                    <span class="stat-label">Completed Jobs</span>
                </div>

            </div>

            <!-- Quick Actions -->
            <div class="section-head">
                <span class="section-label">Quick Actions</span>
                <div class="section-line"></div>
            </div>

            <div class="actions-grid">

                <a class="action-card primary" href="post_job.php">
                    <div class="action-icon-wrap purple" aria-hidden="true">
                        <svg width="24" height="24" fill="none" stroke="#6d28d9" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="16" />
                            <line x1="8" y1="12" x2="16" y2="12" />
                        </svg>
                    </div>
                    <div class="action-title">Post a New Gig</div>
                    <p class="action-desc">Create a job listing to attract skilled workers. Set your budget, describe
                        the role, and start receiving applications right away.</p>
                    <div class="action-cta purple">
                        Get started
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12" />
                            <polyline points="12 5 19 12 12 19" />
                        </svg>
                    </div>
                </a>

                <a class="action-card secondary" href="my_jobs.php">
                    <div class="action-icon-wrap light" aria-hidden="true">
                        <svg width="24" height="24" fill="none" stroke="#a78bfa" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                    </div>
                    <div class="action-title">View My Gigs</div>
                    <p class="action-desc">Review all your posted positions, track applicant counts in real time, and
                        manage acceptances from one organised hub.</p>
                    <div class="action-cta light">
                        View gigs
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12" />
                            <polyline points="12 5 19 12 12 19" />
                        </svg>
                    </div>
                </a>

            </div>

        </div><!-- /content -->
    </main>

    <script>
    function toggleSidebar() {
        var sb = document.getElementById('sidebar');
        var ov = document.getElementById('overlay');
        sb.classList.toggle('open');
        ov.classList.toggle('show');
    }
    </script>

</body>

</html>