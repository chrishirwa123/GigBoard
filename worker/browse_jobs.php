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

$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Search jobs
$sql = "
SELECT
    jobs.*,
    users.fullname

FROM jobs

INNER JOIN users
ON jobs.employer_id = users.id

WHERE jobs.status = 'open'

AND
(
    jobs.title LIKE ?
    OR jobs.category LIKE ?
    OR jobs.location LIKE ?
)

ORDER BY jobs.created_at DESC
";

$stmt = $conn->prepare($sql);

$stmt->execute([
    "%$search%",
    "%$search%",
    "%$search%"
]);

$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Browse Jobs | GigBoard</title>

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

    html,
    body {
        font-family: 'Nunito', sans-serif;
        color: var(--text-primary);
        background: var(--bg);
    }

    /* ── Header ─────────────────────────────────────────────────────── */
    header {
        background: var(--card-bg);
        padding: 20px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    header h2 {
        font-family: 'Outfit', sans-serif;
        display: flex;
        align-items: center;
        gap: .6rem;
        color: var(--text-primary);
        font-size: 21px;
        font-weight: 800;
    }

    header h2 svg {
        width: 22px;
        height: 22px;
        stroke: var(--purple-mid);
    }

    header a {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        text-decoration: none;
        background: linear-gradient(135deg, var(--purple-mid), #9333ea);
        color: #fff;
        padding: 11px 20px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 13px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 14px rgba(109, 40, 217, .3);
    }

    header a svg {
        width: 15px;
        height: 15px;
        stroke: currentColor;
    }

    header a:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(109, 40, 217, .4);
    }

    /* ── Container ──────────────────────────────────────────────────── */
    .container {
        width: 90%;
        max-width: 1200px;
        margin: 40px auto;
    }

    .search-section {
        margin-bottom: 36px;
    }

    .search-section h2 {
        font-family: 'Outfit', sans-serif;
        color: var(--text-primary);
        margin-bottom: 20px;
        font-size: 26px;
        font-weight: 800;
    }

    .search-box {
        display: flex;
        gap: 10px;
    }

    .search-box .input-wrap {
        flex: 1;
        position: relative;
    }

    .search-box .input-wrap svg {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        stroke: var(--text-muted);
        pointer-events: none;
    }

    .search-box input {
        width: 100%;
        padding: 14px 18px 14px 44px;
        border: 1px solid var(--border);
        border-radius: 12px;
        font-size: 15px;
        font-family: 'Nunito', sans-serif;
        background: var(--card-bg);
        color: var(--text-primary);
        transition: all 0.2s ease;
    }

    .search-box input::placeholder {
        color: var(--text-muted);
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--purple-mid);
        box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.12);
    }

    .search-box button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 30px;
        border: none;
        background: linear-gradient(135deg, var(--purple-mid), #9333ea);
        color: #fff;
        cursor: pointer;
        border-radius: 12px;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 16px rgba(109, 40, 217, .3);
    }

    .search-box button svg {
        width: 16px;
        height: 16px;
        stroke: currentColor;
    }

    .search-box button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(109, 40, 217, .4);
    }

    /* ── Jobs Grid ──────────────────────────────────────────────────── */
    .jobs-grid {
        display: grid;
        gap: 20px;
    }

    .job {
        background: var(--card-bg);
        padding: 28px;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
        animation: slideIn 0.5s ease both;
    }

    .job:hover {
        transform: translateY(-6px);
        border-color: var(--purple-light);
        box-shadow: var(--shadow-md);
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .job h2 {
        font-family: 'Outfit', sans-serif;
        color: var(--text-primary);
        margin-bottom: 16px;
        font-size: 19px;
        font-weight: 700;
    }

    .job-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 22px;
        margin: 16px 0;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .job-meta-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 14px;
    }

    .job-meta-item svg {
        width: 16px;
        height: 16px;
        stroke: var(--purple-mid);
        margin-top: 2px;
        flex-shrink: 0;
    }

    .job-meta-item div {
        display: flex;
        flex-direction: column;
    }

    .job-meta-item .meta-label {
        font-size: .66rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--purple-mid);
    }

    .job-meta-item .meta-value {
        color: var(--text-primary);
        font-weight: 600;
    }

    .job p.job-desc {
        margin: 12px 0 18px;
        color: var(--text-muted);
        line-height: 1.65;
        font-size: 14px;
    }

    .job-budget {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: var(--purple-pale);
        color: var(--purple-mid);
        font-weight: 800;
        font-size: 15px;
        padding: .4rem .9rem;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
    }

    .job-budget svg {
        width: 16px;
        height: 16px;
        stroke: var(--purple-mid);
    }

    .view-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 18px;
        text-decoration: none;
        background: linear-gradient(135deg, var(--purple-mid), #9333ea);
        color: #fff;
        padding: 12px 24px;
        border-radius: 999px;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 13px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 16px rgba(109, 40, 217, .3);
    }

    .view-btn svg {
        width: 15px;
        height: 15px;
        stroke: currentColor;
    }

    .view-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(109, 40, 217, .4);
    }

    /* ── Empty State ────────────────────────────────────────────────── */
    .empty {
        background: var(--card-bg);
        padding: 60px 40px;
        text-align: center;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
    }

    .empty .empty-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--purple-pale);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
    }

    .empty .empty-icon svg {
        width: 26px;
        height: 26px;
        stroke: var(--purple-mid);
    }

    .empty h2 {
        font-family: 'Outfit', sans-serif;
        color: var(--text-primary);
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .empty p {
        color: var(--text-muted);
    }

    /* ── Responsive ─────────────────────────────────────────────────── */
    @media (max-width: 900px) {
        header {
            padding: 16px 22px;
        }

        .search-box {
            flex-direction: column;
        }

        .search-box .input-wrap,
        .search-box button {
            width: 100%;
            justify-content: center;
        }

        .job-meta {
            flex-direction: column;
            gap: 12px;
        }
    }
    </style>

</head>

<body>

    <header>

        <h2>
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="7" width="18" height="13" rx="2" />
                <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                <path d="M3 12h18" />
            </svg>
            GigBoard - Browse Jobs
        </h2>

        <a href="dashboard.php">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 5l-7 7 7 7" />
            </svg>
            Dashboard
        </a>

    </header>

    <div class="container">

        <div class="search-section">
            <h2>Find Your Next Gig</h2>
            <form method="GET" class="search-box">
                <div class="input-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input type="text" name="search" placeholder="Search by title, category, or location..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    Search
                </button>
            </form>
        </div>

        <?php if(count($jobs) > 0): ?>

        <div class="jobs-grid">
            <?php foreach($jobs as $job): ?>

            <div class="job">

                <h2><?php echo htmlspecialchars($job['title']); ?></h2>

                <div class="job-meta">
                    <div class="job-meta-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4" />
                            <path d="M4 21c0-4 4-7 8-7s8 3 8 7" />
                        </svg>
                        <div>
                            <span class="meta-label">Employer</span>
                            <span class="meta-value"><?php echo htmlspecialchars($job['fullname']); ?></span>
                        </div>
                    </div>

                    <div class="job-meta-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <div>
                            <span class="meta-label">Location</span>
                            <span class="meta-value"><?php echo htmlspecialchars($job['location']); ?></span>
                        </div>
                    </div>

                    <div class="job-meta-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                        </svg>
                        <div>
                            <span class="meta-label">Category</span>
                            <span class="meta-value"><?php echo htmlspecialchars($job['category']); ?></span>
                        </div>
                    </div>
                </div>

                <p class="job-budget">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <rect x="2" y="6" width="20" height="12" rx="2" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    RWF <?php echo number_format($job['budget'],0); ?>
                </p>

                <p class="job-desc">
                    <?php echo nl2br(htmlspecialchars(substr($job['description'],0,200))); ?>...
                </p>

                <a class="view-btn" href="job_details.php?id=<?php echo $job['id']; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M5 12h14M13 5l7 7-7 7" />
                    </svg>
                    View Details
                </a>

            </div>

            <?php endforeach; ?>
        </div>

        <?php else: ?>

        <div class="empty">

            <div class="empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
            </div>

            <h2>No jobs found.</h2>

            <p>Try adjusting your search or check back later for new opportunities.</p>

        </div>

        <?php endif; ?>

    </div>

</body>

</html>