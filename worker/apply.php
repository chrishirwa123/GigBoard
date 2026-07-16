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


$job_id = (int) $_GET['id'];

$worker_id = $_SESSION['user_id'];


// Get job information

$stmt = $conn->prepare("
SELECT 
    jobs.*,
    users.fullname AS employer_name
FROM jobs
INNER JOIN users
ON jobs.employer_id = users.id
WHERE jobs.id = ?
");

$stmt->execute([$job_id]);

$job = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$job) {
    die("Job does not exist.");
}


// Check if worker already applied

$check = $conn->prepare("
SELECT id 
FROM applications
WHERE job_id = ?
AND worker_id = ?
");

$check->execute([$job_id, $worker_id]);


if ($check->rowCount() > 0) {

    echo "
    <script>
    alert('You already applied for this job.');
    window.location='my_applications.php';
    </script>
    ";

    exit();

}


// Handle application submission

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $cover_letter = trim($_POST['cover_letter']);


    $insert = $conn->prepare("
    INSERT INTO applications
    (
        job_id,
        worker_id,
        cover_letter,
        status
    )
    VALUES
    (
        ?,
        ?,
        ?,
        'pending'
    )
    ");


    $insert->execute([
        $job_id,
        $worker_id,
        $cover_letter
    ]);


    echo "
    <script>
    alert('Application submitted successfully!');
    window.location='my_applications.php';
    </script>
    ";

    exit();

}



?>


<!DOCTYPE html>

<html>

<head>

<title>Apply For Job</title>


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
    max-width: 800px;
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

h1 {
    color: var(--brass-light);
    margin-bottom: 28px;
    font-size: 32px;
    letter-spacing: -0.01em;
}

.summary {
    background: rgba(201, 162, 39, 0.08);
    padding: 24px;
    border-radius: var(--radius-md);
    margin-bottom: 32px;
    border: 1px solid var(--line);
}

.summary h3 {
    color: var(--cream);
    font-size: 18px;
    margin-bottom: 16px;
    font-weight: 600;
}

.summary p {
    margin: 12px 0;
    color: var(--cream-dim);
    font-size: 14px;
    line-height: 1.6;
}

.summary p strong {
    color: var(--brass-light);
    font-weight: 600;
}

.form-group {
    margin-bottom: 24px;
}

label {
    display: block;
    color: var(--slate);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    margin-bottom: 10px;
}

textarea {
    width: 100%;
    height: 200px;
    padding: 16px;
    border-radius: var(--radius-md);
    border: 1px solid var(--line-soft);
    background: rgba(16, 28, 48, 0.6);
    color: var(--cream);
    resize: vertical;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    transition: all 0.3s var(--ease);
    backdrop-filter: blur(8px);
}

textarea::placeholder {
    color: var(--slate);
}

textarea:focus {
    outline: none;
    border-color: var(--brass);
    box-shadow: 0 0 0 3px rgba(201, 162, 39, 0.15);
    background: rgba(16, 28, 48, 0.8);
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 32px;
}

button {
    flex: 1;
    margin-top: 0;
    padding: 14px 28px;
    background: linear-gradient(180deg, var(--brass-light), var(--brass));
    color: #1A1304;
    border: none;
    border-radius: var(--radius-md);
    font-size: 14px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.25s var(--ease);
    box-shadow: 0 8px 24px -8px rgba(201, 162, 39, 0.55);
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

button:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 40px -8px rgba(201, 162, 39, 0.8), 0 0 30px -5px rgba(201, 162, 39, 0.4);
}

button:active {
    transform: translateY(-1px);
}

.back {
    display: inline-block;
    margin-top: 20px;
    text-decoration: none;
    color: var(--brass-light);
    font-weight: 600;
    font-size: 13px;
    transition: all 0.25s var(--ease);
}

.back:hover {
    color: var(--amber);
}

@media (max-width: 900px) {
    .container {
        margin: 24px auto;
    }
    .card {
        padding: 24px;
    }
    h1 {
        font-size: 24px;
    }
    textarea {
        height: 160px;
    }
    .form-actions {
        flex-direction: column;
    }
}

</style>


</head>


<body>


<header>

<h2>GigBoard</h2>

<a href="browse_jobs.php">
← Browse Jobs
</a>

</header>



<div class="container">


<div class="card">


<h1>Apply for This Opportunity</h1>

<div class="summary">

<h3>✨ <?php echo htmlspecialchars($job['title']); ?></h3>

<p>
<strong>👤 Employer:</strong>
<?php echo htmlspecialchars($job['employer_name']); ?>
</p>

<p>
<strong>📂 Category:</strong>
<?php echo htmlspecialchars($job['category']); ?>
</p>

<p>
<strong>📍 Location:</strong>
<?php echo htmlspecialchars($job['location']); ?>
</p>

<p>
<strong>💰 Budget:</strong>
RWF <?php echo number_format($job['budget']); ?>
</p>

</div>

<form method="POST">

<div class="form-group">
<label for="cover_letter">Cover Letter (Optional)</label>

<textarea 
id="cover_letter"
name="cover_letter"
placeholder="Share why you&apos;re perfect for this role. Highlight relevant skills and experience..."></textarea>
</div>

<div class="form-actions">
<button type="submit">📤 Submit Application</button>
</div>

</form>

<a class="back" href="job_details.php?id=<?php echo $job_id; ?>">
← Back to Job Details
</a>



</div>


</div>



</body>


</html>
