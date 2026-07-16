<?php
session_start();

// ── Redirect if not logged in ─────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ── Database Connection ───────────────────────────────────────────────────────
$conn = new mysqli("localhost", "root", "", "gigboard");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = (int)$_SESSION['user_id'];
$message = "";
$success = false;

// ── Handle Form Submission ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($_POST['full_name'] ?? '');
    $gender     = trim($_POST['gender'] ?? '');
    $dob        = trim($_POST['dob'] ?? '');
    $location   = trim($_POST['location'] ?? '');
    $experience = trim($_POST['experience'] ?? '');

    // Server-side age validation (Must be 18+)
    $age = date_diff(date_create($dob), date_create('today'))->y;
    if ($age < 18) {
        $message = "You must be 18 years or older to proceed.";
    } else {
        // Fetch current filenames from DB just in case
        $stmt = $conn->prepare("SELECT profile_pic, cv_path, cert_path FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $profile_pic_name = $res['profile_pic'] ?? '';
        $cv_name          = $res['cv_path'] ?? '';
        $cert_name        = $res['cert_path'] ?? '';

        // File upload helper
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        // 1. Profile Pic
        if (!empty($_FILES['profile_pic']['name'])) {
            $profile_pic_name = time() . '_avatar_' . basename($_FILES['profile_pic']['name']);
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_dir . $profile_pic_name);
        }
        // 2. CV
        if (!empty($_FILES['cv']['name'])) {
            $cv_name = time() . '_cv_' . basename($_FILES['cv']['name']);
            move_uploaded_file($_FILES['cv']['tmp_name'], $target_dir . $cv_name);
        }
        // 3. Certificate
        if (!empty($_FILES['certificate']['name'])) {
            $cert_name = time() . '_cert_' . basename($_FILES['certificate']['name']);
            move_uploaded_file($_FILES['certificate']['tmp_name'], $target_dir . $cert_name);
        }

        // Update database with custom attributes
        $stmt = $conn->prepare("
            UPDATE users 
            SET full_name = ?, gender = ?, dob = ?, location = ?, experience = ?, profile_pic = ?, cv_path = ?, cert_path = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssssssi", $full_name, $gender, $dob, $location, $experience, $profile_pic_name, $cv_name, $cert_name, $user_id);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $message = "Something went wrong updating your profile. Try again.";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if ($success): ?>
    <meta http-equiv="refresh" content="4;url=login.php">
    <?php endif; ?>
    <title>Complete Your Profile – GigBoard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;700;800&family=Nunito:wght@400;600;700&display=swap"
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
        --bg: #f5f3ff;
        --card-bg: #ffffff;
        --text-primary: #1e1b4b;
        --text-muted: #6b7280;
        --border: #e5e7eb;
        --radius: 20px;
    }

    body {
        font-family: 'Nunito', sans-serif;
        background: var(--bg);
        color: var(--text-primary);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .wizard-container {
        background: var(--card-bg);
        width: 100%;
        max-width: 520px;
        border-radius: var(--radius);
        box-shadow: 0 10px 30px rgba(109, 40, 217, 0.12);
        overflow: hidden;
        border: 1px solid rgba(109, 40, 217, 0.05);
    }

    .wizard-header {
        background: linear-gradient(135deg, var(--purple-deep) 0%, var(--purple-mid) 100%);
        padding: 2rem;
        text-align: center;
        color: #fff;
        position: relative;
    }

    .wizard-header h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.4rem;
        font-weight: 800;
    }

    .progress-bar-container {
        width: 100%;
        height: 5px;
        background: rgba(255, 255, 255, 0.2);
        position: absolute;
        bottom: 0;
        left: 0;
    }

    .progress-fill {
        height: 100%;
        width: 14.2%;
        background: var(--purple-light);
        transition: width 0.3s ease;
    }

    .wizard-body {
        padding: 2.2rem;
    }

    .stage {
        display: none;
    }

    .stage.active {
        display: block;
        animation: slideIn 0.35s ease both;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(15px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .stage-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--purple-deep);
    }

    .stage-desc {
        font-size: 0.9rem;
        color: var(--text-muted);
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    /* Form Inputs */
    .input-group {
        margin-bottom: 1rem;
    }

    .text-input,
    textarea {
        width: 100%;
        padding: 0.9rem 1.1rem;
        border: 2px solid var(--border);
        border-radius: 12px;
        font-size: 0.95rem;
        font-family: inherit;
        color: var(--text-primary);
        transition: border-color 0.2s;
    }

    .text-input:focus,
    textarea:focus {
        outline: none;
        border-color: var(--purple-mid);
    }

    textarea {
        height: 120px;
        resize: none;
    }

    /* Custom Gender Selectors */
    .gender-options {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .gender-box {
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        font-weight: 600;
        font-family: 'Outfit', sans-serif;
        transition: all 0.2s;
    }

    .gender-box:hover {
        border-color: var(--purple-light);
        background: var(--purple-pale);
    }

    .gender-options input {
        display: none;
    }

    .gender-options input:checked+.gender-box {
        border-color: var(--purple-mid);
        background: var(--purple-pale);
        color: var(--purple-mid);
    }

    /* File upload area */
    .file-dropzone {
        border: 2px dashed var(--purple-light);
        background: var(--purple-pale);
        border-radius: 12px;
        padding: 2rem 1rem;
        text-align: center;
        cursor: pointer;
        position: relative;
    }

    .file-dropzone input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .file-dropzone p {
        font-size: 0.85rem;
        color: var(--purple-mid);
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .file-preview {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 0.4rem;
        font-weight: bold;
    }

    /* Buttons */
    .btn-group {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        justify-content: space-between;
    }

    .btn {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 0.85rem 1.8rem;
        border-radius: 999px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn-next,
    .btn-submit {
        background: var(--purple-mid);
        color: #fff;
        box-shadow: 0 4px 12px rgba(109, 40, 217, 0.2);
        margin-left: auto;
    }

    .btn-next:hover,
    .btn-submit:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .btn-prev {
        background: transparent;
        color: var(--text-muted);
        border: 2px solid var(--border);
    }

    .btn-prev:hover {
        background: #f3f4f6;
        color: var(--text-primary);
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        padding: 0.8rem;
        border-radius: 10px;
        font-size: 0.85rem;
        margin-bottom: 1.2rem;
        font-weight: bold;
        text-align: center;
    }

    /* Success view */
    .success-view {
        text-align: center;
        padding: 1rem 0 0.5rem;
    }

    .success-icon {
        width: 78px;
        height: 78px;
        margin: 0 auto 1.4rem;
        border-radius: 50%;
        background: var(--purple-pale);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: popIn 0.4s ease both;
    }

    @keyframes popIn {
        from {
            opacity: 0;
            transform: scale(0.6);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .success-view .stage-title {
        text-align: center;
        font-size: 1.35rem;
    }

    .success-view .stage-desc {
        text-align: center;
    }

    .success-redirect-note {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 1rem;
    }

    .success-view .btn-submit {
        margin: 1.5rem auto 0;
        justify-content: center;
    }
    </style>
</head>

<body>

    <div class="wizard-container">
        <header class="wizard-header">
            <h1><?= $success ? 'Profile Complete' : 'Setup Your Profile' ?></h1>
            <div class="progress-bar-container">
                <div class="progress-fill" id="progressBar" style="<?= $success ? 'width:100%;' : '' ?>"></div>
            </div>
        </header>

        <div class="wizard-body">

            <?php if ($success): ?>

            <!-- SUCCESS STAGE -->
            <div class="success-view">
                <div class="success-icon">
                    <svg width="36" height="36" fill="none" stroke="#6d28d9" stroke-width="3" stroke-linecap="round"
                        stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M20 6L9 17l-5-5" />
                    </svg>
                </div>
                <h2 class="stage-title">All done, <?= htmlspecialchars($full_name) ?>!</h2>
                <p class="stage-desc">Your profile details, CV, and documents have been saved successfully. You can
                    now log in to start applying for gigs.</p>
                <a href="login.php" class="btn btn-submit">Continue to Login</a>
                <p class="success-redirect-note">Redirecting you to the login page automatically…</p>
            </div>

            <?php else: ?>

            <?php if(!empty($message)): ?>
            <div class="alert-error"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form id="onboardingForm" method="POST" enctype="multipart/form-data">

                <!-- STAGE 1: Profile Image -->
                <div class="stage active" data-step="1">
                    <h2 class="stage-title">Add an avatar</h2>
                    <p class="stage-desc">Let employers put a face to your excellent application data.</p>
                    <div class="file-dropzone">
                        <input type="file" name="profile_pic" accept="image/*"
                            onchange="showPreview(this, 'avatarPreview')">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" style="color:var(--purple-mid); margin:0 auto;">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        <p>Click to upload profile image</p>
                        <div class="file-preview" id="avatarPreview"></div>
                    </div>
                </div>

                <!-- STAGE 2: Full Name -->
                <div class="stage" data-step="2">
                    <h2 class="stage-title">What is your name?</h2>
                    <p class="stage-desc">Please enter your structural legal name for client contracts.</p>
                    <div class="input-group">
                        <input type="text" name="full_name" class="text-input" placeholder="e.g. Jane Doe" required>
                    </div>
                </div>

                <!-- STAGE 3: Gender -->
                <div class="stage" data-step="3">
                    <h2 class="stage-title">Select your gender</h2>
                    <p class="stage-desc">Help us specialize your account interactions correctly.</p>
                    <div class="gender-options">
                        <label>
                            <input type="radio" name="gender" value="Male" required>
                            <div class="gender-box">Male</div>
                        </label>
                        <label>
                            <input type="radio" name="gender" value="Female">
                            <div class="gender-box">Female</div>
                        </label>
                    </div>
                </div>

                <!-- STAGE 4: Date of Birth -->
                <div class="stage" data-step="4">
                    <h2 class="stage-title">When were you born?</h2>
                    <p class="stage-desc">You must be at least 18 years old to access and process jobs on GigBoard.</p>
                    <div class="input-group">
                        <input type="date" name="dob" id="dobInput" class="text-input" required>
                    </div>
                    <div class="alert-error" id="ageError" style="display:none; margin-top:10px;"></div>
                </div>

                <!-- STAGE 5: Location -->
                <div class="stage" data-step="5">
                    <h2 class="stage-title">Where are you located?</h2>
                    <p class="stage-desc">Enter your city or address to display localized local contracts.</p>
                    <div class="input-group">
                        <input type="text" name="location" class="text-input" placeholder="e.g. New York, USA" required>
                    </div>
                </div>

                <!-- STAGE 6: Experience Description -->
                <div class="stage" data-step="6">
                    <h2 class="stage-title">Describe your experience</h2>
                    <p class="stage-desc">Write down a quick overview explaining your professional highlights and
                        history.</p>
                    <div class="input-group">
                        <textarea name="experience" placeholder="Describe your background here..." required></textarea>
                    </div>
                </div>

                <!-- STAGE 7: CV & Credentials Upload -->
                <div class="stage" data-step="7">
                    <h2 class="stage-title">Upload your documents</h2>
                    <p class="stage-desc">Attach your technical CV document and professional certificates to win
                        assignments.</p>

                    <div class="input-group" style="margin-bottom: 1.5rem;">
                        <div class="file-dropzone">
                            <input type="file" name="cv" accept=".pdf,.doc,.docx" required
                                onchange="showPreview(this, 'cvPreview')">
                            <p>Upload CV Document (PDF/Docx)</p>
                            <div class="file-preview" id="cvPreview"></div>
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="file-dropzone">
                            <input type="file" name="certificate" accept=".pdf,image/*"
                                onchange="showPreview(this, 'certPreview')">
                            <p>Upload Training Certificate (Optional)</p>
                            <div class="file-preview" id="certPreview"></div>
                        </div>
                    </div>
                </div>

                <!-- BUTTONS NAVIGATION CONTROLS -->
                <div class="btn-group">
                    <button type="button" class="btn btn-prev" id="prevBtn" style="display:none;">Back</button>
                    <button type="button" class="btn btn-next" id="nextBtn">Next</button>
                    <button type="submit" class="btn btn-submit" id="submitBtn" style="display:none;">Finish
                        Setup</button>
                </div>
            </form>

            <?php endif; ?>

        </div>
    </div>

    <?php if (!$success): ?>
    <script>
    let currentStep = 1;
    const totalSteps = 7;

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.getElementById('progressBar');

    function showPreview(input, divId) {
        if (input.files && input.files[0]) {
            document.getElementById(divId).innerText = "Selected: " + input.files[0].name;
        }
    }

    nextBtn.addEventListener('click', () => {
        const currentStageEl = document.querySelector(`.stage[data-step="${currentStep}"]`);
        const inputs = currentStageEl.querySelectorAll('input[required], textarea[required]');

        // Basic frontend verification check before moving ahead
        let valid = true;
        inputs.forEach(input => {
            if (input.type === 'radio') {
                const checked = currentStageEl.querySelector('input[type="radio"]:checked');
                if (!checked) valid = false;
            } else if (!input.value) {
                valid = false;
            }
        });

        if (!valid) {
            alert("Please complete the required information before moving forward.");
            return;
        }

        // Dedicated Age Validation check at step 4
        if (currentStep === 4) {
            const dobVal = document.getElementById('dobInput').value;
            const birthDate = new Date(dobVal);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            if (age < 18) {
                const err = document.getElementById('ageError');
                err.style.display = "block";
                err.innerText = "Access denied: You must be at least 18 years old.";
                return;
            } else {
                document.getElementById('ageError').style.display = "none";
            }
        }

        if (currentStep < totalSteps) {
            currentStageEl.classList.remove('active');
            currentStep++;
            document.querySelector(`.stage[data-step="${currentStep}"]`).classList.add('active');
        }
        updateControlUI();
    });

    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            document.querySelector(`.stage[data-step="${currentStep}"]`).classList.remove('active');
            currentStep--;
            document.querySelector(`.stage[data-step="${currentStep}"]`).classList.add('active');
        }
        updateControlUI();
    });

    function updateControlUI() {
        progressBar.style.width = `${(currentStep / totalSteps) * 100}%`;

        if (currentStep === 1) {
            prevBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'block';
        }

        if (currentStep === totalSteps) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'block';
        } else {
            nextBtn.style.display = 'block';
            submitBtn.style.display = 'none';
        }
    }
    </script>
    <?php endif; ?>
</body>

</html>