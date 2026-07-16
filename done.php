<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registration Successful | GigBoard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@500&display=swap"
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
        --ink-navy: #0d1b2a;
        --ink-navy-deep: #081420;
        --brass-gold: #c9a227;
        --brass-gold-light: #e0bf4f;
        --bg: #f7f5f0;
        --card-bg: #ffffff;
        --text-primary: #10151c;
        --text-muted: #5b6472;
        --border: #e6e2d8;
    }

    html,
    body {
        height: 100%;
    }

    body {
        font-family: 'Inter', sans-serif;
        color: var(--text-primary);
        background: var(--bg);
    }

    .page {
        min-height: 100vh;
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    /* LEFT / TOP: photo panel */
    .panel-photo {
        background: linear-gradient(160deg, var(--ink-navy-deep) 0%, var(--ink-navy) 100%);
        position: relative;
        display: flex;
        align-items: flex-end;
        padding: 3rem;
        overflow: hidden;
    }

    .panel-photo::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            radial-gradient(circle at 20% 20%, rgba(201, 162, 39, 0.18), transparent 40%),
            radial-gradient(circle at 80% 70%, rgba(201, 162, 39, 0.12), transparent 45%);
    }

    .photo-caption {
        position: relative;
        z-index: 1;
        color: #fff;
    }

    .photo-mark {
        display: inline-block;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.8rem;
        letter-spacing: 0.08em;
        color: var(--brass-gold-light);
        border: 1px solid rgba(201, 162, 39, 0.4);
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        margin-bottom: 1.4rem;
    }

    .photo-caption h2 {
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 600;
        font-size: 2rem;
        line-height: 1.25;
        margin-bottom: 0.8rem;
        max-width: 380px;
    }

    .photo-caption p {
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.65);
        max-width: 340px;
    }

    /* RIGHT / BOTTOM: content */
    .panel-form {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .form-wrap {
        width: 100%;
        max-width: 380px;
        text-align: center;
    }

    .logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.8rem;
        margin-bottom: 2.4rem;
    }

    .logo-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: var(--ink-navy);
        color: var(--brass-gold-light);
        font-family: 'IBM Plex Mono', monospace;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .logo h1 {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 1.2rem;
        font-weight: 600;
        text-align: left;
    }

    .logo p {
        font-size: 0.8rem;
        color: var(--text-muted);
        text-align: left;
    }

    .check-circle {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: var(--brass-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.6rem;
        box-shadow: 0 10px 24px rgba(201, 162, 39, 0.3);
    }

    .form-wrap h2 {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.7rem;
    }

    .form-wrap>p.desc {
        font-size: 0.92rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    .submit-btn {
        width: 100%;
        padding: 0.95rem 1.5rem;
        border-radius: 10px;
        border: none;
        background: var(--ink-navy);
        color: #fff;
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.2s, transform 0.2s;
    }

    .submit-btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .bottom {
        margin-top: 1.6rem;
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .bottom a {
        color: var(--ink-navy);
        font-weight: 600;
        text-decoration: none;
    }

    .bottom a:hover {
        text-decoration: underline;
    }

    /* Responsive: stack panels like register.php on small screens */
    @media (max-width: 860px) {
        .page {
            grid-template-columns: 1fr;
            grid-template-rows: auto auto;
        }

        .panel-photo {
            padding: 2.2rem;
            min-height: 220px;
        }

        .photo-caption h2 {
            font-size: 1.5rem;
        }
    }
    </style>

</head>

<body>

    <div class="page">

        <!-- LEFT / TOP: photo -->
        <section class="panel-photo" aria-hidden="true">
            <div class="photo-caption">
                <span class="photo-mark">GigBoard</span>
                <h2>You're all set to find your next gig, or your next hire.</h2>
                <p>Trusted by workers and employers across Kigali.</p>
            </div>
        </section>

        <!-- RIGHT / BOTTOM: success message -->
        <section class="panel-form">

            <div class="form-wrap">

                <div class="logo">
                    <span class="logo-mark">GB</span>
                    <div>
                        <h1>GigBoard</h1>
                        <p>Account created</p>
                    </div>
                </div>

                <div class="check-circle">
                    <svg width="30" height="30" fill="none" stroke="#0d1b2a" stroke-width="3" stroke-linecap="round"
                        stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M20 6L9 17l-5-5" />
                    </svg>
                </div>

                <h2>Registration successful!</h2>
                <p class="desc">Your account has been created. Log in to start finding gigs or hiring talent on
                    GigBoard.</p>

                <a href="login.php" class="submit-btn">Continue to login</a>

                <div class="bottom">
                    Redirecting automatically in a few seconds…
                </div>

            </div>

        </section>

    </div>

    <meta http-equiv="refresh" content="5;url=login.php">

</body>

</html>