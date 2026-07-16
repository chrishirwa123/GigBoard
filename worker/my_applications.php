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


$worker_id = $_SESSION['user_id'];


// Get worker applications

$sql = "

SELECT

applications.*,

jobs.title,
jobs.location,
jobs.budget,

users.fullname AS employer_name


FROM applications


INNER JOIN jobs

ON applications.job_id = jobs.id


INNER JOIN users

ON jobs.employer_id = users.id


WHERE applications.worker_id = ?


ORDER BY applications.created_at DESC


";


$stmt = $conn->prepare($sql);

$stmt->execute([$worker_id]);


$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications – GigBoard</title>
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
    .page-header {
        background: linear-gradient(135deg, var(--purple-deep) 0%, var(--purple-mid) 60%, #9333ea 100%);
        padding: 2.5rem 1.5rem 4.5rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
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
        z-index: 2;
    }

    .back-link:hover {
        color: #fff;
    }

    .page-header h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.7rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: -.3px;
        position: relative;
        z-index: 1;
    }

    .page-header p {
        font-size: .92rem;
        color: rgba(255, 255, 255, .75);
        margin-top: .4rem;
        font-weight: 500;
        position: relative;
        z-index: 1;
    }

    /* ── Content ─────────────────────────────────────────────────────── */
    .content-wrap {
        max-width: 780px;
        margin: -2.75rem auto 3rem;
        padding: 0 1rem;
    }

    .applications-list {
        display: flex;
        flex-direction: column;
        gap: 1.15rem;
    }

    /* ── Application Card ───────────────────────────────────────────── */
    .application {
        background: var(--card-bg);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        padding: 1.5rem;
        animation: fadeUp .4s ease both;
        transition: box-shadow .2s, transform .2s;
    }

    .application:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .application-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding-bottom: .9rem;
        margin-bottom: 1.1rem;
        border-bottom: 2px solid var(--purple-pale);
    }

    .application-header h2 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    /* Badges — matches profile.php badge system */
    .status-badge {
        font-family: 'Outfit', sans-serif;
        font-size: .7rem;
        font-weight: 700;
        padding: .3rem .75rem;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: .4px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .status-accepted {
        background: #dcfce7;
        color: #166534;
    }

    .status-pending {
        background: #fef9c3;
        color: #854d0e;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Meta grid */
    .application-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
        margin-bottom: 1.1rem;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
        gap: .3rem;
    }

    .meta-label {
        display: flex;
        align-items: center;
        gap: .3rem;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: var(--purple-mid);
    }

    .meta-label svg {
        width: 13px;
        height: 13px;
        flex-shrink: 0;
    }

    .meta-value {
        font-size: .9rem;
        color: var(--text-primary);
        font-weight: 500;
    }

    /* Message box */
    .message-box {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .9rem 1rem;
        background: var(--purple-pale);
        border-left: 3px solid var(--purple-mid);
        border-radius: 10px;
        font-size: .85rem;
        color: var(--purple-deep);
        font-weight: 500;
        line-height: 1.5;
    }

    .message-box svg {
        width: 18px;
        height: 18px;
        color: var(--purple-mid);
        flex-shrink: 0;
    }

    /* Chat button — matches edit-btn in profile.php */
    .chat-btn {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        margin-top: 1.1rem;
        background: linear-gradient(135deg, var(--purple-mid), #9333ea);
        color: #fff;
        text-decoration: none;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: .85rem;
        padding: .7rem 1.5rem;
        border-radius: 999px;
        box-shadow: 0 4px 16px rgba(109, 40, 217, .35);
        transition: transform .2s, box-shadow .2s;
    }

    .chat-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(109, 40, 217, .45);
    }

    .chat-btn svg {
        width: 15px;
        height: 15px;
    }

    /* ── Empty State ─────────────────────────────────────────────────── */
    .empty {
        background: var(--card-bg);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        padding: 3.5rem 1.5rem;
        text-align: center;
    }

    .empty-icon {
        width: 56px;
        height: 56px;
        margin: 0 auto 1.1rem;
        color: var(--purple-light);
    }

    .empty h2 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: .5rem;
    }

    .empty p {
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
        .page-header h1 {
            font-size: 1.4rem;
        }

        .application-header {
            flex-direction: column;
            gap: .5rem;
        }

        .application-meta {
            grid-template-columns: 1fr 1fr;
        }
    }
    </style>
</head>

<body>

    <!-- ═══ HEADER ════════════════════════════════════════════════════════════════ -->
    <header class="page-header">
        <a href="dashboard.php" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor"
                stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M19 12H5M12 5l-7 7 7 7" />
            </svg>
            Dashboard
        </a>
        <h1>My Applications</h1>
        <p>Track the status of every job you've applied to</p>
    </header>

    <!-- ═══ CONTENT ══════════════════════════════════════════════════════════════ -->
    <main class="content-wrap">

        <?php if (count($applications) > 0) : ?>
        <div class="applications-list">
            <?php foreach ($applications as $application) :

                $status = strtolower($application['status']);

                if ($status == "accepted") {
                    $text = "Congratulations! The employer accepted your application.";
                    $statusClass = "status-accepted";
                    $iconPath = '<circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/>';
                } elseif ($status == "rejected") {
                    $text = "Unfortunately, your application was rejected.";
                    $statusClass = "status-rejected";
                    $iconPath = '<circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/>';
                } else {
                    $text = "Your application is waiting for employer response.";
                    $statusClass = "status-pending";
                    $iconPath = '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>';
                }

            ?>
            <div class="application">

                <div class="application-header">
                    <h2><?= htmlspecialchars($application['title']); ?></h2>
                    <div class="status-badge <?= $statusClass; ?>">
                        <?= ucfirst($status); ?>
                    </div>
                </div>

                <div class="application-meta">
                    <div class="meta-item">
                        <div class="meta-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M12 2a5 5 0 1 0 0 10A5 5 0 0 0 12 2zM20 21a8 8 0 1 0-16 0" />
                            </svg>
                            Employer
                        </div>
                        <div class="meta-value"><?= htmlspecialchars($application['employer_name']); ?></div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            Location
                        </div>
                        <div class="meta-value"><?= htmlspecialchars($application['location']); ?></div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 6v2m0 8v2M9 9h4.5a1.5 1.5 0 0 1 0 3H10a1.5 1.5 0 0 0 0 3h5" />
                            </svg>
                            Budget
                        </div>
                        <div class="meta-value">RWF <?= number_format($application['budget']); ?></div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" />
                                <path d="M16 2v4M8 2v4M3 10h18" />
                            </svg>
                            Applied
                        </div>
                        <div class="meta-value"><?= date("d M Y", strtotime($application['created_at'])); ?></div>
                    </div>
                </div>

                <div class="message-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <?= $iconPath; ?>
                    </svg>
                    <?= $text; ?>
                </div>

                <?php if ($status == "accepted") : ?>
                <a class="chat-btn" href="messages.php?application_id=<?= $application['id']; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    </svg>
                    Open Chat
                </a>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>
        </div>

        <?php else : ?>
        <div class="empty">
            <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M22 12h-6l-2 3h-4l-2-3H2" />
                <path
                    d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />
            </svg>
            <h2>No applications yet</h2>
            <p>Browse available jobs and start building your career on GigBoard.</p>
        </div>
        <?php endif; ?>

    </main>
</body>

</html>