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

if(!isset($_GET['job_id'])){
    die("Job not found");
}

$job_id = (int)$_GET['job_id'];

// ACCEPT / REJECT APPLICATION
if(isset($_GET['action']) && isset($_GET['application_id'])){

    $application_id = (int)$_GET['application_id'];
    $action = $_GET['action'];

    if($action == "accept"){
        $new_status = "accepted";
    } elseif($action == "reject"){
        $new_status = "rejected";
    } else {
        $new_status = "pending";
    }

    // Check ownership
    $check = $conn->prepare("
        SELECT applications.id
        FROM applications
        INNER JOIN jobs ON applications.job_id = jobs.id
        WHERE applications.id = ? AND jobs.employer_id = ?
    ");
    $check->execute([$application_id, $employer_id]);

    if($check->rowCount() > 0){
        $update = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $update->execute([$new_status, $application_id]);
    }

    header("Location: application_received.php?job_id=" . $job_id);
    exit();
}

// Verify job belongs to employer
$verify = $conn->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
$verify->execute([$job_id, $employer_id]);
$job = $verify->fetch(PDO::FETCH_ASSOC);

if(!$job){
    die("You cannot access this job.");
}

// Get applications
$stmt = $conn->prepare("
    SELECT applications.*, users.fullname, users.email, users.phone
    FROM applications
    INNER JOIN users ON applications.worker_id = users.id
    WHERE applications.job_id = ?
    ORDER BY applications.id DESC
");
$stmt->execute([$job_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$stats = ['total' => 0, 'pending' => 0, 'accepted' => 0, 'rejected' => 0];
foreach($applications as $app){
    $stats['total']++;
    $s = strtolower($app['status']);
    if(isset($stats[$s])) $stats[$s]++;
}

// Helper: initials
function getInitials($name){
    $words = explode(' ', trim($name));
    $initials = '';
    foreach($words as $w){ if(!empty($w)) $initials .= strtoupper($w[0]); }
    return substr($initials, 0, 2);
}

// Helper: avatar gradient
function getAvatarStyle($id){
    $gradients = [
        ['#3b82f6','#06b6d4'],
        ['#10b981','#14b8a6'],
        ['#f97316','#f59e0b'],
        ['#ef4444','#f43f5e'],
        ['#8b5cf6','#a855f7'],
        ['#0ea5e9','#3b82f6'],
    ];
    $c = $gradients[$id % count($gradients)];
    return "background:linear-gradient(135deg,{$c[0]},{$c[1]});";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications — <?=htmlspecialchars($job['title']);?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    :root {
        --bg: #f8fafc;
        --white: #ffffff;
        --border: #e2e8f0;
        --border-light: #f1f5f9;
        --text-900: #0f172a;
        --text-700: #334155;
        --text-500: #64748b;
        --text-400: #94a3b8;
        --shadow-sm: 0 1px 2px rgba(0, 0, 0, .05);
        --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, .07), 0 2px 4px -1px rgba(0, 0, 0, .04);
        --shadow-lg: 0 10px 20px -3px rgba(0, 0, 0, .09), 0 4px 6px -2px rgba(0, 0, 0, .04);
    }

    body {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        background: var(--bg);
        color: var(--text-900);
        -webkit-font-smoothing: antialiased;
        min-height: 100vh;
    }

    /* ─── HEADER ─────────────────────────────────── */
    .header {
        background: var(--white);
        border-bottom: 1px solid var(--border-light);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .header-inner {
        max-width: 980px;
        margin: 0 auto;
        padding: 0 24px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .back-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-500);
        text-decoration: none;
        flex-shrink: 0;
        transition: background .15s, color .15s, border-color .15s;
    }

    .back-btn:hover {
        background: var(--bg);
        color: var(--text-900);
    }

    .divider-v {
        width: 1px;
        height: 20px;
        background: var(--border);
        flex-shrink: 0;
    }

    .job-identity {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .job-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--text-900);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .job-label {
        font-size: 11px;
        color: var(--text-400);
        margin-bottom: 2px;
    }

    .job-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-900);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 340px;
    }

    .header-tags {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
    }

    .tag {
        font-size: 11px;
        font-weight: 500;
        background: var(--border-light);
        color: var(--text-500);
        padding: 4px 10px;
        border-radius: 20px;
    }

    /* ─── MAIN ────────────────────────────────────── */
    .main {
        max-width: 980px;
        margin: 0 auto;
        padding: 32px 24px 56px;
    }

    /* ─── STATS ───────────────────────────────────── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: var(--white);
        border: 1px solid var(--border-light);
        border-radius: 16px;
        padding: 20px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: var(--shadow);
    }

    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon.s-total {
        background: #f1f5f9;
        color: #475569;
    }

    .stat-icon.s-pending {
        background: #fffbeb;
        color: #d97706;
    }

    .stat-icon.s-accepted {
        background: #f0fdf4;
        color: #16a34a;
    }

    .stat-icon.s-rejected {
        background: #fff1f2;
        color: #dc2626;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 800;
        color: var(--text-900);
        line-height: 1;
    }

    .stat-label {
        font-size: 12px;
        color: var(--text-400);
        font-weight: 500;
        margin-top: 2px;
    }

    /* ─── TOOLBAR ─────────────────────────────────── */
    .toolbar {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: center;
    }

    .tabs {
        display: flex;
        background: var(--white);
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 4px;
        gap: 2px;
        box-shadow: var(--shadow);
    }

    .tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: var(--text-500);
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        font-family: inherit;
        transition: background .15s, color .15s;
        white-space: nowrap;
    }

    .tab-btn:hover:not(.active) {
        color: var(--text-900);
        background: var(--bg);
    }

    .tab-btn.active {
        background: var(--text-900);
        color: white;
    }

    .tab-count {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 20px;
    }

    .tab-btn.active .tab-count {
        background: rgba(255, 255, 255, .18);
        color: white;
    }

    .tab-btn:not(.active) .tab-count {
        background: var(--border-light);
        color: var(--text-400);
    }

    .search-wrap {
        flex: 1;
        min-width: 220px;
        position: relative;
    }

    .search-ico {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-400);
        pointer-events: none;
        display: flex;
    }

    .search-input {
        width: 100%;
        padding: 10px 14px 10px 38px;
        border: 1px solid var(--border-light);
        border-radius: 12px;
        font-size: 13px;
        font-family: inherit;
        color: var(--text-900);
        background: var(--white);
        outline: none;
        box-shadow: var(--shadow);
        transition: border-color .15s, box-shadow .15s;
    }

    .search-input::placeholder {
        color: var(--text-400);
    }

    .search-input:focus {
        border-color: #cbd5e1;
        box-shadow: 0 0 0 3px rgba(148, 163, 184, .15);
    }

    /* ─── APPLICATION CARD ────────────────────────── */
    .app-card {
        background: var(--white);
        border: 1px solid var(--border-light);
        border-radius: 20px;
        margin-bottom: 16px;
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: box-shadow .2s, transform .2s;
        animation: fadeUp .35s ease both;
    }

    .app-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .card-stripe {
        height: 3px;
    }

    .card-stripe.pending {
        background: linear-gradient(90deg, #f59e0b, #fb923c);
    }

    .card-stripe.accepted {
        background: linear-gradient(90deg, #10b981, #14b8a6);
    }

    .card-stripe.rejected {
        background: linear-gradient(90deg, #ef4444, #f43f5e);
    }

    .card-body {
        padding: 24px;
    }

    .card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .applicant-row {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 15px;
        font-weight: 700;
        flex-shrink: 0;
        box-shadow: 0 3px 8px rgba(0, 0, 0, .14);
    }

    .applicant-name {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-900);
        margin-bottom: 3px;
    }

    .applicant-id {
        font-size: 12px;
        color: var(--text-400);
    }

    /* Status badge */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 11px;
        border-radius: 20px;
        border: 1px solid;
        font-size: 12px;
        font-weight: 600;
        flex-shrink: 0;
    }

    .badge svg {
        width: 12px;
        height: 12px;
    }

    .badge.pending {
        background: #fffbeb;
        border-color: #fde68a;
        color: #92400e;
    }

    .badge.accepted {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #14532d;
    }

    .badge.rejected {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #9f1239;
    }

    /* Contact */
    .contact-row {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        margin-bottom: 18px;
    }

    .contact-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--text-500);
        text-decoration: none;
        transition: color .15s;
    }

    .contact-link:hover {
        color: #2563eb;
    }

    .contact-link svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    /* Cover letter */
    .cover-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text-400);
        margin-bottom: 8px;
    }

    .cover-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .cover-text {
        font-size: 13.5px;
        line-height: 1.65;
        color: var(--text-700);
        white-space: pre-wrap;
        word-break: break-word;
    }

    .cover-full {
        display: none;
    }

    .read-more-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 10px;
        background: none;
        border: none;
        font-size: 12px;
        font-weight: 600;
        color: #2563eb;
        cursor: pointer;
        font-family: inherit;
        padding: 0;
        transition: color .15s;
    }

    .read-more-btn:hover {
        color: #1d4ed8;
    }

    .read-more-btn svg {
        width: 13px;
        height: 13px;
    }

    /* Action buttons */
    .actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid transparent;
        transition: all .15s;
        line-height: 1;
        font-family: inherit;
        cursor: pointer;
    }

    .btn:active {
        transform: scale(.97);
    }

    .btn svg {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
    }

    .btn-accept {
        background: #16a34a;
        color: white;
        border-color: #16a34a;
    }

    .btn-accept:hover {
        background: #15803d;
        border-color: #15803d;
        box-shadow: 0 4px 14px rgba(22, 163, 74, .28);
    }

    .btn-reject {
        background: white;
        color: #dc2626;
        border-color: #fca5a5;
    }

    .btn-reject:hover {
        background: #fff1f2;
        border-color: #f87171;
    }

    .btn-chat {
        background: #2563eb;
        color: white;
        border-color: #2563eb;
    }

    .btn-chat:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        box-shadow: 0 4px 14px rgba(37, 99, 235, .28);
    }

    /* Empty / no-results */
    .empty-state,
    .no-results {
        background: var(--white);
        border: 1px solid var(--border-light);
        border-radius: 20px;
        padding: 72px 32px;
        text-align: center;
        box-shadow: var(--shadow);
    }

    .no-results {
        display: none;
    }

    .empty-icon-wrap {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: var(--bg);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        color: var(--text-400);
    }

    .empty-title {
        font-size: 17px;
        font-weight: 700;
        color: var(--text-900);
        margin-bottom: 8px;
    }

    .empty-sub {
        font-size: 14px;
        color: var(--text-400);
        max-width: 300px;
        margin: 0 auto;
        line-height: 1.55;
    }

    /* Footer */
    .result-footer {
        text-align: center;
        font-size: 12px;
        color: var(--text-400);
        margin-top: 24px;
    }

    /* Animations */
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(14px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    <?php foreach($applications as $idx=> $app): ?>.app-card[data-id="<?=(int)$app['id'];?>"] {
        animation-delay: <?=number_format($idx * 0.05, 2);
        ?>s;
    }

    <?php endforeach;
    ?>

    /* Responsive */
    @media (max-width: 700px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .header-tags {
            display: none;
        }

        .job-title {
            max-width: 200px;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .main {
            padding: 20px 16px 48px;
        }
    }

    @media (max-width: 480px) {
        .toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .tabs {
            overflow-x: auto;
        }
    }
    </style>
</head>

<body>

    <!-- ─── HEADER ──────────────────────────────────────────────────────────── -->
    <header class="header">
        <div class="header-inner">
            <div class="header-left">
                <a class="back-btn" href="../employer/dashboard.php" title="Back to dashboard">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 19-7-7 7-7" />
                        <path d="M19 12H5" />
                    </svg>
                </a>
                <div class="divider-v"></div>
                <div class="job-identity">
                    <div class="job-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="14" x="2" y="7" rx="2" />
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                        </svg>
                    </div>
                    <div>
                        <div class="job-label">Applications for</div>
                        <div class="job-title"><?=htmlspecialchars($job['title']);?></div>
                    </div>
                </div>
            </div>
            <div class="header-tags">
                <?php if(!empty($job['location'])): ?>
                <span class="tag"><?=htmlspecialchars($job['location']);?></span>
                <?php endif; ?>
                <?php if(!empty($job['type'])): ?>
                <span class="tag"><?=htmlspecialchars($job['type']);?></span>
                <?php endif; ?>
                <span class="tag"><?=$stats['total'];?> applicant<?=$stats['total'] !== 1 ? 's' : '';?></span>
            </div>
        </div>
    </header>

    <!-- ─── MAIN ─────────────────────────────────────────────────────────────── -->
    <main class="main">

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon s-total">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>
                <div>
                    <div class="stat-value"><?=$stats['total'];?></div>
                    <div class="stat-label">Total</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon s-pending">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                </div>
                <div>
                    <div class="stat-value"><?=$stats['pending'];?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon s-accepted">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>
                <div>
                    <div class="stat-value"><?=$stats['accepted'];?></div>
                    <div class="stat-label">Accepted</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon s-rejected">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="m15 9-6 6" />
                        <path d="m9 9 6 6" />
                    </svg>
                </div>
                <div>
                    <div class="stat-value"><?=$stats['rejected'];?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
        </div>

        <?php if(count($applications) > 0): ?>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="tabs">
                <button class="tab-btn active" onclick="setTab('all', this)">
                    All <span class="tab-count"><?=$stats['total'];?></span>
                </button>
                <button class="tab-btn" onclick="setTab('pending', this)">
                    Pending <span class="tab-count"><?=$stats['pending'];?></span>
                </button>
                <button class="tab-btn" onclick="setTab('accepted', this)">
                    Accepted <span class="tab-count"><?=$stats['accepted'];?></span>
                </button>
                <button class="tab-btn" onclick="setTab('rejected', this)">
                    Rejected <span class="tab-count"><?=$stats['rejected'];?></span>
                </button>
            </div>
            <div class="search-wrap">
                <span class="search-ico">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                </span>
                <input type="text" class="search-input" id="searchInput" placeholder="Search by name or email…"
                    oninput="filterCards()">
            </div>
        </div>

        <!-- No-results message (JS-controlled) -->
        <div class="no-results" id="noResults">
            <div class="empty-icon-wrap">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
            </div>
            <div class="empty-title">No results found</div>
            <p class="empty-sub" id="noResultsText">No applicants match your current filter.</p>
        </div>

        <!-- Application cards -->
        <div id="cardsList">
            <?php foreach($applications as $app): ?>
            <?php
            $status = strtolower($app['status']);
            $initials = getInitials($app['fullname']);
            $avatarStyle = getAvatarStyle($app['id']);
            $coverFull = htmlspecialchars($app['cover_letter']);
            $coverShort = mb_strlen($app['cover_letter']) > 200
                ? htmlspecialchars(mb_substr($app['cover_letter'], 0, 200)) . '…'
                : $coverFull;
            $hasMore = mb_strlen($app['cover_letter']) > 200;
        ?>
            <div class="app-card" data-id="<?=(int)$app['id'];?>" data-status="<?=htmlspecialchars($status);?>"
                data-name="<?=strtolower(htmlspecialchars($app['fullname']));?>"
                data-email="<?=strtolower(htmlspecialchars($app['email']));?>">

                <!-- Status stripe -->
                <div class="card-stripe <?=htmlspecialchars($status);?>"></div>

                <div class="card-body">

                    <!-- Header row -->
                    <div class="card-header">
                        <div class="applicant-row">
                            <div class="avatar" style="<?=$avatarStyle?>">
                                <?=htmlspecialchars($initials);?>
                            </div>
                            <div>
                                <div class="applicant-name"><?=htmlspecialchars($app['fullname']);?></div>
                                <div class="applicant-id">Application #<?=(int)$app['id'];?></div>
                            </div>
                        </div>

                        <!-- Status badge -->
                        <?php if($status === 'pending'): ?>
                        <span class="badge pending">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            Pending Review
                        </span>
                        <?php elseif($status === 'accepted'): ?>
                        <span class="badge accepted">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            Accepted
                        </span>
                        <?php else: ?>
                        <span class="badge rejected">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="m15 9-6 6" />
                                <path d="m9 9 6 6" />
                            </svg>
                            Rejected
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Contact -->
                    <div class="contact-row">
                        <a class="contact-link" href="mailto:<?=htmlspecialchars($app['email']);?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2" />
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                            </svg>
                            <?=htmlspecialchars($app['email']);?>
                        </a>
                        <?php if(!empty($app['phone'])): ?>
                        <a class="contact-link" href="tel:<?=htmlspecialchars($app['phone']);?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.77 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.68 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                            <?=htmlspecialchars($app['phone']);?>
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- Cover letter -->
                    <div class="cover-label">Cover Letter</div>
                    <div class="cover-box">
                        <p class="cover-text cover-short"><?=$coverShort;?></p>
                        <?php if($hasMore): ?>
                        <p class="cover-text cover-full"><?=$coverFull;?></p>
                        <button class="read-more-btn" onclick="toggleCover(this)">
                            <svg class="ico-down" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                            <svg class="ico-up" style="display:none" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m18 15-6-6-6 6" />
                            </svg>
                            <span>Read more</span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Action buttons -->
                    <?php if($status === 'pending'): ?>
                    <div class="actions">
                        <a class="btn btn-accept"
                            href="application_received.php?job_id=<?=$job_id;?>&application_id=<?=(int)$app['id'];?>&action=accept">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            Confirm Applicant
                        </a>
                        <a class="btn btn-reject"
                            href="application_received.php?job_id=<?=$job_id;?>&application_id=<?=(int)$app['id'];?>&action=reject">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="m15 9-6 6" />
                                <path d="m9 9 6 6" />
                            </svg>
                            Reject Applicant
                        </a>
                    </div>
                    <?php elseif($status === 'accepted'): ?>
                    <div class="actions">
                        <a class="btn btn-chat" href="message.php?application_id=<?=(int)$app['id'];?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                            Open Private Chat
                        </a>
                    </div>
                    <?php endif; ?>

                </div><!-- /card-body -->
            </div><!-- /app-card -->
            <?php endforeach; ?>
        </div><!-- /cardsList -->

        <p class="result-footer" id="resultFooter">
            Showing <?=$stats['total'];?> of <?=$stats['total'];?> application<?=$stats['total'] !== 1 ? 's' : '';?>
        </p>

        <?php else: ?>

        <!-- True empty state (no applications at all) -->
        <div class="empty-state">
            <div class="empty-icon-wrap">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 16 12 14 15 10 15 8 12 2 12" />
                    <path
                        d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />
                </svg>
            </div>
            <div class="empty-title">No applications yet</div>
            <p class="empty-sub">Applications for this job posting will appear here once candidates start applying.</p>
        </div>

        <?php endif; ?>

    </main>

    <script>
    var currentTab = 'all';

    function setTab(tab, btn) {
        currentTab = tab;
        document.querySelectorAll('.tab-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        btn.classList.add('active');
        filterCards();
    }

    function filterCards() {
        var search = document.getElementById('searchInput').value.toLowerCase().trim();
        var cards = document.querySelectorAll('.app-card');
        var visible = 0;

        cards.forEach(function(card) {
            var status = card.getAttribute('data-status');
            var name = card.getAttribute('data-name');
            var email = card.getAttribute('data-email');

            var tabMatch = (currentTab === 'all') || (status === currentTab);
            var searchMatch = (search === '') || name.includes(search) || email.includes(search);

            if (tabMatch && searchMatch) {
                card.style.display = '';
                visible++;
            } else {
                card.style.display = 'none';
            }
        });

        var noRes = document.getElementById('noResults');
        var footer = document.getElementById('resultFooter');
        var total = cards.length;

        if (visible === 0) {
            noRes.style.display = 'block';
            var txt = search ?
                'No applicants match "' + search + '". Try a different search term.' :
                'No applicants in this category yet.';
            document.getElementById('noResultsText').textContent = txt;
        } else {
            noRes.style.display = 'none';
        }

        if (footer) {
            footer.textContent = 'Showing ' + visible + ' of ' + total + ' application' + (total !== 1 ? 's' : '');
        }
    }

    function toggleCover(btn) {
        var box = btn.closest('.cover-box');
        var short = box.querySelector('.cover-short');
        var full = box.querySelector('.cover-full');
        var icoD = btn.querySelector('.ico-down');
        var icoU = btn.querySelector('.ico-up');
        var label = btn.querySelector('span');

        if (full.style.display === 'block') {
            full.style.display = 'none';
            short.style.display = 'block';
            icoD.style.display = '';
            icoU.style.display = 'none';
            label.textContent = 'Read more';
        } else {
            full.style.display = 'block';
            short.style.display = 'none';
            icoD.style.display = 'none';
            icoU.style.display = '';
            label.textContent = 'Show less';
        }
    }
    </script>

</body>

</html>