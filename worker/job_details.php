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

// Check job ID
if (!isset($_GET['id'])) {
    die("Job not found.");
}

$job_id = (int)$_GET['id'];

// Get job information
$stmt = $conn->prepare("
SELECT
    jobs.*,
    users.fullname,
    users.email,
    users.phone
FROM jobs
INNER JOIN users
ON jobs.employer_id = users.id
WHERE jobs.id = ?
");

$stmt->execute([$job_id]);

$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die("Job not found.");
}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo htmlspecialchars($job['title']); ?></title>

<style>

:root {
    --ink: #0A1220;
    --ink-2: #0D1728;
    --panel: #101C30;
    --brass: #C9A227;
    --brass-light: #E8C468;
    --amber: #F2B84B;
    --cream: #F4EFE2;
    --cream-dim: #B9C1CE;
    --slate: #8996A9;
    --line: rgba(201, 162, 39, 0.16);
    --line-soft: rgba(244, 239, 226, 0.08);
    --radius-md: 14px;
    --shadow: 0 20px 60px -20px rgba(0, 0, 0, 0.6);
    --ease: cubic-bezier(0.4, 0, 0.2, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    font-family: 'Inter', 'Arial', sans-serif;
    color: var(--cream);
    background: var(--ink);
}

body {
    background-image:
        radial-gradient(ellipse 900px 500px at 15% -5%, rgba(201, 162, 39, 0.10), transparent 60%),
        radial-gradient(ellipse 700px 500px at 100% 10%, rgba(79, 169, 124, 0.06), transparent 55%);
}

header {
    background: linear-gradient(180deg, var(--ink-2), var(--panel));
    padding: 24px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--line);
    backdrop-filter: blur(10px);
    position: sticky;
    top: 0;
    z-index: 100;
}

header h2 {
    color: var(--brass-light);
    font-size: 24px;
    font-weight: 700;
}

header a {
    color: var(--cream);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s var(--ease);
}

header a:hover {
    color: var(--brass-light);
}

.container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 0 20px;
}

.card {
    background: linear-gradient(135deg, rgba(16, 28, 48, 0.8), rgba(13, 23, 40, 0.6));
    padding: 40px;
    border-radius: var(--radius-md);
    border: 1px solid var(--line);
    backdrop-filter: blur(10px);
    animation: slideUp 0.6s var(--ease) both;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card h1 {
    margin-bottom: 10px;
    color: var(--brass-light);
    font-size: 36px;
    letter-spacing: -0.01em;
}

.job-category {
    display: inline-block;
    margin-bottom: 24px;
    padding: 8px 16px;
    background: rgba(201, 162, 39, 0.15);
    border: 1px solid var(--line);
    border-radius: 20px;
    color: var(--brass-light);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
    margin: 30px 0;
    padding: 28px;
    background: rgba(201, 162, 39, 0.08);
    border-radius: var(--radius-md);
    border: 1px solid var(--line);
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    color: var(--slate);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    margin-bottom: 8px;
}

.info-value {
    color: var(--cream);
    font-size: 16px;
    font-weight: 600;
}

.info-value.emphasis {
    color: var(--brass-light);
    font-size: 20px;
}

.description {
    margin-top: 40px;
    padding-top: 40px;
    border-top: 1px solid var(--line);
}

.description h2 {
    margin-bottom: 20px;
    color: var(--cream);
    font-size: 22px;
}

.description p {
    line-height: 1.8;
    color: var(--cream-dim);
    font-size: 15px;
    margin-bottom: 12px;
}

.buttons {
    margin-top: 40px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    text-decoration: none;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 14px;
    transition: all 0.25s var(--ease);
    text-transform: uppercase;
    letter-spacing: 0.02em;
    border: none;
    cursor: pointer;
}

.apply {
    background: linear-gradient(180deg, var(--brass-light), var(--brass));
    color: #1A1304;
    box-shadow: 0 8px 24px -8px rgba(201, 162, 39, 0.55);
}

.apply:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 40px -8px rgba(201, 162, 39, 0.8), 0 0 30px -5px rgba(201, 162, 39, 0.4);
}

.back {
    background: rgba(201, 162, 39, 0.12);
    color: var(--brass-light);
    border: 1px solid var(--line);
}

.back:hover {
    background: rgba(201, 162, 39, 0.2);
    border-color: var(--brass-light);
}

@media (max-width: 900px) {
    .container {
        margin: 24px auto;
    }
    .card {
        padding: 24px;
    }
    .card h1 {
        font-size: 28px;
    }
    .info-grid {
        grid-template-columns: 1fr;
    }
    .buttons {
        flex-direction: column;
    }
    .btn {
        width: 100%;
        justify-content: center;
    }
}

</style>

</head>

<body>

<header>

<h2>GigBoard</h2>

<a href="browse_jobs.php">← Browse Jobs</a>

</header>

<div class="container">

<div class="card">

<span class="job-category"><?php echo htmlspecialchars($job['category']); ?></span>

<h1><?php echo htmlspecialchars($job['title']); ?></h1>

<div class="info-grid">

<div class="info-item">
<div class="info-label">👤 Employer</div>
<div class="info-value"><?php echo htmlspecialchars($job['fullname']); ?></div>
</div>

<div class="info-item">
<div class="info-label">📍 Location</div>
<div class="info-value"><?php echo htmlspecialchars($job['location']); ?></div>
</div>

<div class="info-item">
<div class="info-label">💰 Budget</div>
<div class="info-value emphasis">RWF <?php echo number_format($job['budget']); ?></div>
</div>

<div class="info-item">
<div class="info-label">⏱️ Job Type</div>
<div class="info-value"><?php echo ucfirst(str_replace("_"," ",$job['job_type'])); ?></div>
</div>

<div class="info-item">
<div class="info-label">📅 Posted</div>
<div class="info-value"><?php echo date("d M Y",strtotime($job['created_at'])); ?></div>
</div>

<div class="info-item">
<div class="info-label">✓ Status</div>
<div class="info-value"><?php echo ucfirst($job['status']); ?></div>
</div>

</div>

<div class="description">

<h2>Job Description</h2>

<p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>

</div>

<div class="buttons">

<a class="btn apply" href="apply.php?id=<?php echo $job['id']; ?>">
✨ Apply Now
</a>

<a class="btn back" href="browse_jobs.php">
← Browse Jobs
</a>

</div>

</div>

</div>

</body>

</html>
