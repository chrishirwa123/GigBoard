<?php

require "config/session.php";

// ── Destroy the session completely ────────────────────────────────
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out | GigBoard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <meta http-equiv="refresh" content="4;url=login.php">

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
        --shadow-md: 0 4px 20px rgba(109, 40, 217, .14);
        --radius: 16px;
    }

    html,
    body {
        height: 100%;
    }

    body {
        font-family: 'Nunito', sans-serif;
        color: var(--text-primary);
        background: var(--bg);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .card {
        background: var(--card-bg);
        width: 100%;
        max-width: 420px;
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border);
        padding: 3rem 2.4rem 2.4rem;
        text-align: center;
        animation: fadeUp 0.4s ease both;
    }

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

    .icon-badge {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: var(--purple-pale);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.6rem;
    }

    .icon-badge svg {
        width: 32px;
        height: 32px;
        stroke: var(--purple-mid);
    }

    h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 0.7rem;
        color: var(--text-primary);
    }

    p.desc {
        font-size: 0.92rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.9rem 1.5rem;
        border-radius: 999px;
        border: none;
        background: linear-gradient(135deg, var(--purple-mid), #9333ea);
        color: #fff;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.95rem;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(109, 40, 217, .3);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(109, 40, 217, .4);
    }

    .btn svg {
        width: 16px;
        height: 16px;
        stroke: currentColor;
    }

    .redirect-note {
        margin-top: 1.1rem;
        font-size: 0.78rem;
        color: var(--text-muted);
    }
    </style>

</head>

<body>

    <div class="card">

        <div class="icon-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
        </div>

        <h1>You've been logged out</h1>
        <p class="desc">Your session has ended safely. Come back anytime to keep finding gigs or hiring talent on
            GigBoard.</p>

        <a href="login.php" class="btn">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                <polyline points="10 17 15 12 10 7" />
                <line x1="15" y1="12" x2="3" y2="12" />
            </svg>
            Back to Login
        </a>

        <p class="redirect-note">Redirecting you automatically in a few seconds…</p>

    </div>

</body>

</html>