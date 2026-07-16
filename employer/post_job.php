<?php
require "../config/session.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Gig | GigBoard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap"
        rel="stylesheet">
    <style>
    :root {
        --bg: #09111f;
        --card: #111e33;
        --border: rgba(255, 255, 255, 0.07);
        --border-focus: rgba(245, 158, 11, 0.6);
        --accent: #f59e0b;
        --accent-dim: rgba(245, 158, 11, 0.10);
        --text-1: #f0f4ff;
        --text-2: #8899bb;
        --text-3: #4a5a7a;
        --input-bg: rgba(255, 255, 255, 0.04);
        --input-bg-focus: rgba(255, 255, 255, 0.06);
        --error: #f43f5e;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    html,
    body {
        min-height: 100%;
    }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--bg);
        color: var(--text-1);
        position: relative;
        overflow-x: hidden;
    }

    body::before {
        content: '';
        position: fixed;
        top: -180px;
        right: -180px;
        width: 560px;
        height: 560px;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.07) 0%, transparent 65%);
        pointer-events: none;
        z-index: 0;
    }

    body::after {
        content: '';
        position: fixed;
        bottom: -150px;
        left: -120px;
        width: 480px;
        height: 480px;
        background: radial-gradient(circle, rgba(45, 212, 191, 0.04) 0%, transparent 65%);
        pointer-events: none;
        z-index: 0;
    }

    /* ── Top bar ── */
    .topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 32px;
        border-bottom: 1px solid var(--border);
        position: relative;
        z-index: 2;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 9px;
        text-decoration: none;
    }

    .brand-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: var(--accent-dim);
        border: 1.5px solid rgba(245, 158, 11, .3);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .brand-name {
        font-family: 'Syne', sans-serif;
        font-size: 16px;
        font-weight: 800;
        color: var(--text-1);
    }

    .brand-name em {
        color: var(--accent);
        font-style: normal;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-2);
        text-decoration: none;
        transition: color .2s;
    }

    .back-link:hover {
        color: var(--accent);
    }

    .back-link:focus-visible {
        outline: 2px solid var(--accent);
        outline-offset: 3px;
        border-radius: 4px;
    }

    .back-link svg {
        transition: transform .2s;
    }

    .back-link:hover svg {
        transform: translateX(-2px);
    }

    /* ── Page wrap ── */
    .page-wrap {
        max-width: 600px;
        margin: 0 auto;
        padding: 44px 24px 80px;
        position: relative;
        z-index: 1;
    }

    /* ── Page header ── */
    .page-header {
        margin-bottom: 28px;
        animation: slideUp .45s ease both;
    }

    .eyebrow-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: var(--accent-dim);
        border: 1px solid rgba(245, 158, 11, .2);
        border-radius: 100px;
        padding: 5px 14px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 16px;
    }

    h1.page-title {
        font-family: 'Syne', sans-serif;
        font-size: clamp(26px, 4vw, 34px);
        font-weight: 800;
        color: var(--text-1);
        line-height: 1.1;
        margin-bottom: 8px;
    }

    .page-sub {
        font-size: 14px;
        color: var(--text-2);
        line-height: 1.6;
    }

    /* ── Form card ── */
    .form-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        animation: slideUp .45s ease both .06s;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .3);
    }

    .card-stripe {
        height: 3px;
        background: linear-gradient(90deg, var(--accent) 0%, rgba(245, 158, 11, .3) 100%);
    }

    .card-body {
        padding: 36px 36px 32px;
    }

    /* ── Section divider ── */
    .section-label {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 28px 0 18px;
    }

    .section-label:first-child {
        margin-top: 0;
    }

    .section-label-text {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--text-3);
        white-space: nowrap;
    }

    .section-label-line {
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    /* ── Inputs ── */
    .input-group {
        margin-bottom: 16px;
    }

    .input-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-2);
        margin-bottom: 7px;
        letter-spacing: .01em;
    }

    .input-group input,
    .input-group textarea,
    .input-group select {
        width: 100%;
        padding: 12px 14px;
        background: var(--input-bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        color: var(--text-1);
        outline: none;
        transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
        appearance: none;
        -webkit-appearance: none;
    }

    .input-group input::placeholder,
    .input-group textarea::placeholder {
        color: var(--text-3);
    }

    .input-group select option {
        background: #111e33;
        color: var(--text-1);
    }

    .input-group input:focus,
    .input-group textarea:focus,
    .input-group select:focus {
        border-color: var(--border-focus);
        background: var(--input-bg-focus);
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.08);
    }

    .input-group input:hover:not(:focus),
    .input-group textarea:hover:not(:focus),
    .input-group select:hover:not(:focus) {
        border-color: rgba(255, 255, 255, .12);
    }

    .input-group textarea {
        height: 110px;
        resize: vertical;
        line-height: 1.6;
    }

    /* Select arrow */
    .select-wrap {
        position: relative;
    }

    .select-wrap select {
        padding-right: 36px;
        cursor: pointer;
    }

    .select-wrap::after {
        content: '';
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 5px solid var(--text-3);
        pointer-events: none;
    }

    /* Grid 2 */
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    /* Budget prefix */
    .budget-wrap {
        position: relative;
    }

    .budget-prefix {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-3);
        pointer-events: none;
        z-index: 1;
    }

    .budget-wrap input {
        padding-left: 48px;
    }

    /* Char count */
    .field-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 5px;
    }

    .char-count {
        font-size: 11px;
        color: var(--text-3);
        transition: color .2s;
    }

    /* Required star */
    .req {
        color: var(--error);
    }

    /* ── Segmented control ── */
    .segment-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-2);
        margin-bottom: 9px;
        letter-spacing: .01em;
    }

    .segmented {
        position: relative;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        background: var(--input-bg);
        border: 1px solid var(--border);
        border-radius: 11px;
        padding: 4px;
    }

    .segmented input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 0;
        height: 0;
    }

    .segmented label {
        position: relative;
        z-index: 2;
        text-align: center;
        font-size: 13px;
        font-weight: 500;
        padding: 10px 4px;
        border-radius: 8px;
        color: var(--text-3);
        cursor: pointer;
        transition: color .25s ease;
        display: block;
        margin: 0;
        letter-spacing: 0;
    }

    .segmented-indicator {
        position: absolute;
        top: 4px;
        left: 4px;
        width: calc(33.333% - 2.67px);
        height: calc(100% - 8px);
        background: var(--accent);
        border-radius: 8px;
        transition: transform .3s cubic-bezier(.65, 0, .35, 1);
        z-index: 1;
        box-shadow: 0 2px 12px rgba(245, 158, 11, .25);
    }

    #type-one:checked~label[for="type-one"],
    #type-part:checked~label[for="type-part"],
    #type-full:checked~label[for="type-full"] {
        color: #09111f;
        font-weight: 600;
    }

    #type-part:checked~.segmented-indicator {
        transform: translateX(100%);
    }

    #type-full:checked~.segmented-indicator {
        transform: translateX(200%);
    }

    /* ── Submit button ── */
    .submit-btn {
        width: 100%;
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 15px 24px;
        background: var(--accent);
        color: #09111f;
        border: none;
        border-radius: 12px;
        font-family: 'Syne', sans-serif;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        letter-spacing: .01em;
        transition: all .2s ease;
        box-shadow: 0 4px 20px rgba(245, 158, 11, .2);
    }

    .submit-btn:hover {
        background: #fbbf24;
        box-shadow: 0 8px 28px rgba(245, 158, 11, .35);
        transform: translateY(-1px);
    }

    .submit-btn:active {
        transform: translateY(0) scale(.98);
    }

    .submit-btn:focus-visible {
        outline: 2px solid var(--text-1);
        outline-offset: 2px;
    }

    .submit-btn svg {
        flex-shrink: 0;
        transition: transform .2s;
    }

    .submit-btn:hover svg {
        transform: translateX(3px);
    }

    /* ── Back link ── */
    .form-back {
        text-align: center;
        margin-top: 20px;
        animation: slideUp .5s ease both .12s;
    }

    .form-back a {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-3);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: color .2s;
    }

    .form-back a:hover {
        color: var(--accent);
    }

    .form-back a:focus-visible {
        outline: 2px solid var(--accent);
        outline-offset: 3px;
        border-radius: 4px;
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

    @media (prefers-reduced-motion: reduce) {

        .page-header,
        .form-card,
        .form-back {
            animation: none;
        }

        .submit-btn:hover,
        .back-link:hover svg,
        .submit-btn:hover svg {
            transform: none;
        }
    }

    @media (max-width: 560px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }

        .card-body {
            padding: 28px 22px 24px;
        }

        .topbar {
            padding: 14px 20px;
        }

        .page-wrap {
            padding: 32px 18px 60px;
        }
    }
    </style>
</head>

<body>

    <!-- Top bar -->
    <header class="topbar">
        <a class="brand" href="dashboard.php">
            <div class="brand-icon" aria-hidden="true">
                <svg width="14" height="14" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="7" width="20" height="14" rx="2" />
                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                </svg>
            </div>
            <span class="brand-name">Gig<em>Board</em></span>
        </a>
        <a class="back-link" href="dashboard.php">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                aria-hidden="true">
                <path d="M19 12H5M12 5l-7 7 7 7" />
            </svg>
            Dashboard
        </a>
    </header>

    <div class="page-wrap">

        <!-- Page header -->
        <div class="page-header">
            <div class="eyebrow-pill" aria-hidden="true">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="16" />
                    <line x1="8" y1="12" x2="16" y2="12" />
                </svg>
                New Posting
            </div>
            <h1 class="page-title">Post a Gig</h1>
            <p class="page-sub">Describe the work and talented workers nearby will start applying.</p>
        </div>

        <!-- Form card -->
        <div class="form-card">
            <div class="card-stripe" aria-hidden="true"></div>
            <div class="card-body">

                <form action="post_job_process.php" method="POST" novalidate>

                    <!-- Section: The Basics -->
                    <div class="section-label" aria-hidden="true">
                        <span class="section-label-text">The Basics</span>
                        <div class="section-label-line"></div>
                    </div>

                    <div class="input-group">
                        <label for="title">
                            Gig title <span class="req" aria-hidden="true">*</span>
                        </label>
                        <input type="text" id="title" name="title" placeholder="e.g. Need a website developer" required
                            autocomplete="off" aria-required="true">
                    </div>

                    <div class="input-group">
                        <label for="description">
                            Description <span class="req" aria-hidden="true">*</span>
                        </label>
                        <textarea id="description" name="description"
                            placeholder="Explain what you need, timelines, and any requirements..." required
                            maxlength="1000" aria-required="true" aria-describedby="char-count"></textarea>
                        <div class="field-footer">
                            <span class="char-count" id="char-count" aria-live="polite">0 / 1000</span>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="category">
                            Category <span class="req" aria-hidden="true">*</span>
                        </label>
                        <div class="select-wrap">
                            <select id="category" name="category" required aria-required="true">
                                <option value="">Select category</option>
                                <option value="Technology">Technology</option>
                                <option value="Education">Education</option>
                                <option value="Design">Design</option>
                                <option value="Cleaning">Cleaning</option>
                                <option value="Construction">Construction</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Section: Logistics -->
                    <div class="section-label" aria-hidden="true">
                        <span class="section-label-text">Logistics</span>
                        <div class="section-label-line"></div>
                    </div>

                    <div class="grid-2">
                        <div class="input-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" placeholder="e.g. Kigali"
                                autocomplete="off">
                        </div>
                        <div class="input-group">
                            <label for="budget">Budget</label>
                            <div class="budget-wrap">
                                <span class="budget-prefix" aria-hidden="true">RWF</span>
                                <input type="number" id="budget" name="budget" placeholder="50,000" min="0"
                                    aria-label="Budget in RWF">
                            </div>
                        </div>
                    </div>

                    <!-- Job type segmented control -->
                    <div class="input-group">
                        <span class="segment-label" id="job-type-label">Job type</span>
                        <div class="segmented" role="radiogroup" aria-labelledby="job-type-label">

                            <input type="radio" name="job_type" id="type-one" value="one_time" checked>
                            <label for="type-one">One time</label>

                            <input type="radio" name="job_type" id="type-part" value="part_time">
                            <label for="type-part">Part time</label>

                            <input type="radio" name="job_type" id="type-full" value="full_time">
                            <label for="type-full">Full time</label>

                            <span class="segmented-indicator" aria-hidden="true"></span>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="submit-btn">
                        Post Gig
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12" />
                            <polyline points="12 5 19 12 12 19" />
                        </svg>
                    </button>

                </form>
            </div>
        </div>

        <!-- Back link -->
        <div class="form-back">
            <a href="dashboard.php">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path d="M19 12H5M12 5l-7 7 7 7" />
                </svg>
                Back to dashboard
            </a>
        </div>

    </div>

    <script>
    (function() {
        const ta = document.getElementById('description');
        const cc = document.getElementById('char-count');
        if (!ta || !cc) return;
        ta.addEventListener('input', function() {
            const len = ta.value.length;
            cc.textContent = len + ' / 1000';
            cc.style.color = len > 900 ? '#f43f5e' :
                len > 750 ? '#f59e0b' :
                '#4a5a7a';
        });
    })();
    </script>

</body>

</html>