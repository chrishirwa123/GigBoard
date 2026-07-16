<?php

require "../config/session.php";
require "../config/database.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

if($_SESSION['role'] !== "employer"){
    header("Location: ../login.php");
    exit();
}

$employer_id = $_SESSION['user_id'];

// Get employer jobs and application count
$stmt = $conn->prepare("
SELECT
    jobs.*,
    COUNT(applications.id) AS total_applications
FROM jobs
LEFT JOIN applications ON jobs.id = applications.job_id
WHERE jobs.employer_id = ?
GROUP BY jobs.id
ORDER BY jobs.created_at DESC
");
$stmt->execute([$employer_id]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display helpers (no DB changes)
$total_apps   = array_sum(array_column($jobs, 'total_applications'));
$total_jobs   = count($jobs);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Gigs | GigBoard</title>
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
        --green: #10b981;
        --green-dim: rgba(16, 185, 129, .10);
        --amber: #f59e0b;
        --amber-dim: rgba(245, 158, 11, .10);
        --sidebar-w: 264px;
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

    .topbar-title {
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .topbar-title svg {
        color: var(--purple-mid);
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
        max-width: 960px;
    }

    /* Page header */
    .page-header {
        margin-bottom: 32px;
        animation: slideUp .5s ease both;
    }

    .page-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--purple-mid);
        margin-bottom: 10px;
        display: block;
    }

    h1.page-title {
        font-family: 'Outfit', sans-serif;
        font-size: clamp(26px, 4vw, 38px);
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.1;
        margin-bottom: 8px;
        letter-spacing: -.02em;
    }

    .page-sub {
        font-size: 15px;
        color: var(--text-secondary);
        line-height: 1.6;
    }

    /* Stats bar */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 36px;
    }

    .stat-box {
        display: flex;
        align-items: center;
        gap: 14px;
        background: var(--card-bg);
        border: 1px solid var(--border-light);
        border-radius: var(--radius);
        padding: 20px 22px;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        animation: slideUp .5s ease both;
    }

    .stat-box:nth-child(1) {
        animation-delay: .06s;
    }

    .stat-box:nth-child(2) {
        animation-delay: .12s;
    }

    .stat-box:hover {
        transform: translateY(-3px);
        border-color: var(--purple-pale);
        box-shadow: var(--shadow-md);
    }

    .stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon.purple {
        background: var(--purple-pale);
        border: 1px solid rgba(109, 40, 217, .12);
    }

    .stat-icon.light {
        background: rgba(167, 139, 250, .10);
        border: 1px solid rgba(167, 139, 250, .15);
    }

    .stat-val {
        font-family: 'Outfit', sans-serif;
        font-size: 28px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1;
        display: block;
        letter-spacing: -.02em;
    }

    .stat-lbl {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .08em;
        display: block;
        margin-top: 4px;
    }

    /* Section head */
    .section-head {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
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

    /* Gig card */
    .gig-card {
        background: var(--card-bg);
        border: 1px solid var(--border-light);
        border-radius: var(--radius);
        margin-bottom: 16px;
        overflow: hidden;
        position: relative;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        animation: slideUp .45s ease both;
    }

    .gig-card:nth-child(1) {
        animation-delay: .05s;
    }

    .gig-card:nth-child(2) {
        animation-delay: .10s;
    }

    .gig-card:nth-child(3) {
        animation-delay: .15s;
    }

    .gig-card:nth-child(4) {
        animation-delay: .20s;
    }

    .gig-card:nth-child(5) {
        animation-delay: .25s;
    }

    .gig-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, var(--purple-mid) 0%, var(--purple-light) 100%);
    }

    .gig-card:hover {
        transform: translateY(-3px);
        border-color: var(--purple-pale);
        box-shadow: var(--shadow-lg);
    }

    /* Card layout: header row + body grid + footer */
    .card-header-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 24px 28px 16px 32px;
    }

    .card-tags {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        font-family: 'Outfit', sans-serif;
    }

    .tag-category {
        background: var(--purple-pale);
        color: var(--purple-mid);
        border: 1px solid rgba(109, 40, 217, .12);
    }

    .tag-location {
        background: var(--border-light);
        color: var(--text-secondary);
        border: 1px solid var(--border);
    }

    .job-title {
        font-family: 'Outfit', sans-serif;
        font-size: 19px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.25;
        margin-bottom: 8px;
        letter-spacing: -.01em;
    }

    .job-date {
        font-size: 12px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 600;
    }

    .job-date svg {
        color: var(--purple-light);
    }

    /* Apps badge in header */
    .apps-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--green-dim);
        border: 1px solid rgba(16, 185, 129, .18);
        color: var(--green);
        border-radius: 100px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .apps-pill.zero {
        background: var(--border-light);
        border-color: var(--border);
        color: var(--text-muted);
    }

    /* Card body: info grid */
    .card-body {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0;
        padding: 0 28px 20px 32px;
    }

    .info-cell {
        padding: 16px 0 0;
    }

    .info-cell+.info-cell {
        padding-left: 20px;
        border-left: 1px solid var(--border-light);
    }

    .info-lbl {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .info-lbl svg {
        color: var(--purple-light);
    }

    .info-val {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.2;
        letter-spacing: -.01em;
    }

    .info-val.accent {
        color: var(--purple-mid);
    }

    .info-val.muted {
        color: var(--text-muted);
    }

    .info-sub {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 3px;
        font-weight: 600;
    }

    /* Card footer */
    .card-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 28px 16px 32px;
        border-top: 1px solid var(--border-light);
        background: var(--purple-paler);
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 10px;
        font-family: 'Outfit', sans-serif;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: all .18s ease;
        white-space: nowrap;
    }

    .btn:focus-visible {
        outline: 2px solid var(--purple-mid);
        outline-offset: 2px;
    }

    .btn-view {
        background: var(--purple-pale);
        color: var(--purple-mid);
        border: 1px solid rgba(109, 40, 217, .15);
    }

    .btn-view:hover {
        background: var(--purple-mid);
        color: #fff;
        box-shadow: 0 4px 16px rgba(109, 40, 217, .3);
        transform: translateY(-1px);
    }

    .btn-view-apps {
        background: var(--green-dim);
        color: var(--green);
        border: 1px solid rgba(16, 185, 129, .18);
    }

    .btn-view-apps:hover {
        background: var(--green);
        color: #fff;
        box-shadow: 0 4px 16px rgba(16, 185, 129, .28);
        transform: translateY(-1px);
    }

    .apps-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(16, 185, 129, .15);
        color: var(--green);
        border-radius: 100px;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        min-width: 20px;
    }

    .btn-view-apps:hover .apps-badge {
        background: rgba(255, 255, 255, .25);
        color: #fff;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 80px 40px;
        background: var(--card-bg);
        border: 1px solid var(--border-light);
        border-radius: var(--radius);
        animation: slideUp .4s ease both;
    }

    .empty-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 22px;
        background: var(--purple-pale);
        border: 1px solid rgba(109, 40, 217, .12);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .empty-title {
        font-family: 'Outfit', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
        letter-spacing: -.01em;
    }

    .empty-sub {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.6;
    }

    .empty-cta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 24px;
        padding: 12px 28px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--purple-mid), #9333ea);
        color: #fff;
        text-decoration: none;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14px;
        box-shadow: 0 4px 16px rgba(109, 40, 217, .3);
        transition: transform .2s, box-shadow .2s;
    }

    .empty-cta:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(109, 40, 217, .4);
    }

    /* Overlay */
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

        .gig-card,
        .stat-box,
        .empty-state,
        .page-header {
            animation: none;
        }

        .btn:hover,
        .gig-card:hover,
        .stat-box:hover {
            transform: none;
        }

        .live-dot {
            animation: none;
        }
    }

    /* Responsive */
    @media (max-width: 900px) {
        .card-body {
            grid-template-columns: 1fr 1fr;
        }

        .info-cell:nth-child(3) {
            grid-column: 1 / -1;
            padding-left: 0;
            border-left: none;
            padding-top: 16px;
            border-top: 1px solid var(--border-light);
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

        .stats-bar {
            grid-template-columns: 1fr;
        }

        .card-header-row {
            flex-direction: column;
            padding: 20px 20px 14px 24px;
        }

        .card-body {
            grid-template-columns: 1fr;
            padding: 0 20px 16px 24px;
        }

        .info-cell+.info-cell {
            padding-left: 0;
            border-left: none;
            padding-top: 14px;
            border-top: 1px solid var(--border-light);
        }

        .info-cell:nth-child(3) {
            border-top: 1px solid var(--border-light);
        }

        .card-footer {
            padding: 12px 20px 14px 24px;
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

            <a class="nav-item" href="dashboard.php">
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

            <a class="nav-item active" href="my_jobs.php" aria-current="page">
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
                <div class="user-avatar" aria-hidden="true">GB</div>
                <div class="user-info">
                    <div class="user-name">Employer</div>
                    <div class="user-role">GigBoard</div>
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
                <div class="topbar-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                    My Gigs
                </div>
            </div>
            <div class="live-pill">
                <span class="live-dot" aria-hidden="true"></span>
                Live
            </div>
        </div>

        <div class="content">

            <!-- Page header -->
            <header class="page-header">
                <span class="page-label">Gig Management</span>
                <h1 class="page-title">My Posted Gigs</h1>
                <p class="page-sub">Manage your open positions and review applicants in one organised hub.</p>
            </header>

            <!-- Stats bar -->
            <section class="stats-bar" aria-label="Summary statistics">
                <div class="stat-box">
                    <div class="stat-icon purple" aria-hidden="true">
                        <svg width="20" height="20" fill="none" stroke="#6d28d9" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="2" y="7" width="20" height="14" rx="2" />
                            <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                            <line x1="12" y1="12" x2="12" y2="16" />
                            <line x1="10" y1="14" x2="14" y2="14" />
                        </svg>
                    </div>
                    <div>
                        <span class="stat-val"><?=$total_jobs;?></span>
                        <span class="stat-lbl"><?=$total_jobs === 1 ? 'Active Gig' : 'Active Gigs';?></span>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon light" aria-hidden="true">
                        <svg width="20" height="20" fill="none" stroke="#a78bfa" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <div>
                        <span class="stat-val"><?=$total_apps;?></span>
                        <span class="stat-lbl">Total Applicants</span>
                    </div>
                </div>
            </section>

            <div class="section-head">
                <span class="section-label">All Gigs</span>
                <div class="section-line"></div>
            </div>

            <!-- Empty state -->
            <?php if(count($jobs) == 0): ?>
            <div class="empty-state" role="status">
                <div class="empty-icon" aria-hidden="true">
                    <svg width="32" height="32" fill="none" stroke="#6d28d9" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect x="2" y="7" width="20" height="14" rx="2" />
                        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                        <line x1="12" y1="12" x2="12" y2="16" />
                        <line x1="10" y1="14" x2="14" y2="14" />
                    </svg>
                </div>
                <div class="empty-title">No gigs posted yet</div>
                <p class="empty-sub">Your posted jobs will appear here with applicant counts.<br>Start by posting your
                    first gig to attract talent.</p>
                <a href="post_job.php" class="empty-cta">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="16" />
                        <line x1="8" y1="12" x2="16" y2="12" />
                    </svg>
                    Post a New Gig
                </a>
            </div>
            <?php endif; ?>

            <!-- Gig cards -->
            <?php foreach($jobs as $job):
            $has_apps = (int)$job['total_applications'] > 0;
            $posted   = !empty($job['created_at']) ? date('M j, Y', strtotime($job['created_at'])) : '';
        ?>
            <article class="gig-card" aria-label="<?=htmlspecialchars($job['title']);?>">

                <!-- Header row: tags + title on left, apps pill on right -->
                <div class="card-header-row">
                    <div style="flex:1;min-width:0;">
                        <div class="card-tags">
                            <span class="tag tag-category" aria-label="Category">
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="2" y="7" width="20" height="14" rx="2" />
                                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                </svg>
                                <?=htmlspecialchars($job['category']);?>
                            </span>
                            <span class="tag tag-location" aria-label="Location">
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <?=htmlspecialchars($job['location']);?>
                            </span>
                        </div>

                        <h2 class="job-title"><?=htmlspecialchars($job['title']);?></h2>

                        <?php if($posted): ?>
                        <div class="job-date" aria-label="Date posted">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            Posted <?=$posted;?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Apps pill -->
                    <div class="apps-pill <?=$has_apps ? '' : 'zero';?>">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        <?=(int)$job['total_applications'];?>
                        <?=((int)$job['total_applications'] === 1) ? 'Applicant' : 'Applicants';?>
                    </div>
                </div>

                <!-- Body: 3-column info grid -->
                <div class="card-body">
                    <div class="info-cell">
                        <div class="info-lbl">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                            Budget
                        </div>
                        <div class="info-val accent">RWF <?=number_format($job['budget']);?></div>
                    </div>
                    <div class="info-cell">
                        <div class="info-lbl">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            Status
                        </div>
                        <div class="info-val <?= $has_apps ? '' : 'muted';?>">
                            <?=$has_apps ? 'Active' : 'Open';?>
                        </div>
                        <div class="info-sub">
                            <?=$has_apps ? 'awaiting review' : 'no applicants yet';?>
                        </div>
                    </div>
                    <div class="info-cell">
                        <div class="info-lbl">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                            </svg>
                            Applications
                        </div>
                        <div class="info-val <?= $has_apps ? 'accent' : 'muted';?>">
                            <?=(int)$job['total_applications'];?>
                        </div>
                        <div class="info-sub">
                            <?=$has_apps ? 'received' : 'none yet';?>
                        </div>
                    </div>
                </div>

                <!-- Footer: CTA -->
                <div class="card-footer">
                    <a class="btn <?=$has_apps ? 'btn-view-apps' : 'btn-view';?>"
                        href="application_received.php?job_id=<?=(int)$job['id'];?>"
                        aria-label="View applications for <?=htmlspecialchars($job['title']);?>">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        View Applications
                        <?php if($has_apps): ?>
                        <span class="apps-badge"><?=(int)$job['total_applications'];?></span>
                        <?php endif; ?>
                    </a>
                </div>

            </article>
            <?php endforeach; ?>

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