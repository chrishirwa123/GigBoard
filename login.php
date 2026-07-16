<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | GigBoard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

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
        --success: #4FA97C;
        --radius-sm: 8px;
        --radius-md: 14px;
        --shadow: 0 20px 60px -20px rgba(0, 0, 0, 0.6);
        --ease: cubic-bezier(0.4, 0, 0.2, 1);
        --font-display: 'Space Grotesk', sans-serif;
        --font-body: 'Inter', sans-serif;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html,
    body {
        font-family: var(--font-body);
        color: var(--cream);
        background: var(--ink);
        overflow-x: hidden;
    }

    body {
        background-image:
            radial-gradient(ellipse 900px 500px at 15% -5%, rgba(201, 162, 39, 0.10), transparent 60%),
            radial-gradient(ellipse 700px 500px at 100% 10%, rgba(79, 169, 124, 0.06), transparent 55%);
    }

    .page {
        min-height: 100vh;
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .panel {
        position: relative;
    }

    /* ---------- Photo panel with enhanced visuals ---------- */
    .panel-photo {
        background-image: linear-gradient(180deg, rgba(10, 18, 32, 0.4) 0%, rgba(10, 18, 32, 0.85) 100%),
            url('https://images.unsplash.com/photo-1552664730-d307ca884978?fm=jpg&q=80&w=1400&auto=format&fit=crop');
        background-size: cover;
        background-position: center;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-end;
        padding: 64px 56px;
        animation: photoZoom 24s ease-in-out infinite alternate;
        position: relative;
        overflow: hidden;
    }

    .panel-photo::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 85% 20%, rgba(201, 162, 39, 0.15), transparent 60%);
        animation: pulse 4s ease-in-out infinite;
        pointer-events: none;
    }

    @keyframes photoZoom {
        from {
            background-size: 100%;
        }
        to {
            background-size: 112%;
        }
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 0.5;
        }
        50% {
            opacity: 1;
        }
    }

    .photo-caption {
        color: var(--cream);
        max-width: 500px;
        animation: rise 0.8s var(--ease) both;
        position: relative;
        z-index: 1;
    }

    .photo-mark {
        font-family: var(--font-display);
        font-size: 12px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--brass-light);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .photo-mark::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--brass);
        box-shadow: 0 0 12px var(--brass);
    }

    .photo-caption h2 {
        font-family: var(--font-display);
        font-size: clamp(32px, 5vw, 48px);
        line-height: 1.1;
        font-weight: 700;
        margin: 0 0 16px;
        letter-spacing: -0.02em;
    }

    .photo-caption h2 .accent {
        color: var(--brass-light);
        text-decoration: underline;
        text-decoration-color: var(--brass);
        text-underline-offset: 6px;
        text-decoration-thickness: 2px;
    }

    .photo-caption p {
        font-size: 15.5px;
        line-height: 1.6;
        margin: 0;
        color: var(--cream-dim);
        max-width: 420px;
    }

    .typewriter-line {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 13px;
        color: var(--brass-light);
        min-height: 20px;
        margin: 24px 0;
        display: flex;
        align-items: center;
    }

    .typewriter-line .cursor {
        display: inline-block;
        width: 2px;
        height: 16px;
        background: var(--brass-light);
        margin-left: 4px;
        animation: blink 1s step-end infinite;
    }

    @keyframes blink {
        50% {
            opacity: 0;
        }
    }

    /* ---------- Form panel ---------- */
    .panel-form {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 40px;
        background: linear-gradient(135deg, var(--ink-2), var(--ink));
    }

    .form-wrap {
        width: 100%;
        max-width: 440px;
        animation: slideIn 0.8s var(--ease) both;
    }

    @keyframes rise {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 32px;
    }

    .logo-mark {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-sm);
        background: linear-gradient(160deg, var(--brass-light), var(--brass));
        color: #1A1304;
        font-family: var(--font-display);
        font-weight: 700;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px -4px rgba(201, 162, 39, 0.6);
    }

    .logo h1 {
        font-family: var(--font-display);
        font-size: 21px;
        margin: 0;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .logo p {
        margin: 2px 0 0;
        font-size: 13px;
        color: var(--cream-dim);
    }

    /* ---------- Inputs with glow ---------- */
    .input-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 16px;
    }

    .input-group label {
        font-size: 12.5px;
        font-weight: 500;
        color: var(--slate);
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .input-group input {
        font-family: var(--font-body);
        font-size: 14.5px;
        padding: 12px 14px;
        border-radius: var(--radius-md);
        border: 1px solid var(--line-soft);
        background: rgba(16, 28, 48, 0.6);
        color: var(--cream);
        outline: none;
        transition: border-color 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
        backdrop-filter: blur(8px);
    }

    .input-group input::placeholder {
        color: var(--slate);
    }

    .input-group input:focus {
        border-color: var(--brass);
        box-shadow: 0 0 0 3px rgba(201, 162, 39, 0.15), 0 0 20px -2px rgba(201, 162, 39, 0.3);
        background: rgba(16, 28, 48, 0.8);
    }

    .label-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .label-row label {
        margin-bottom: 0;
    }

    .label-row a {
        font-size: 12px;
        font-weight: 600;
        color: var(--brass-light);
        text-decoration: none;
        transition: color 0.2s;
    }

    .label-row a:hover {
        color: var(--amber);
        text-decoration: underline;
    }

    .password-field {
        position: relative;
        display: flex;
    }

    .password-field input {
        width: 100%;
        padding-right: 52px;
    }

    .toggle-visibility {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        font-size: 11px;
        font-weight: 600;
        color: var(--slate);
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        transition: color 0.2s;
    }

    .toggle-visibility:hover {
        color: var(--brass-light);
    }

    /* ---------- Submit button with glow ---------- */
    .submit-btn {
        width: 100%;
        margin-top: 8px;
        padding: 14px;
        border: none;
        border-radius: var(--radius-md);
        background: linear-gradient(180deg, var(--brass-light), var(--brass));
        color: #1A1304;
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: transform 0.25s var(--ease), box-shadow 0.25s var(--ease);
        box-shadow: 0 8px 24px -8px rgba(201, 162, 39, 0.55);
        letter-spacing: 0.02em;
        text-transform: uppercase;
        font-size: 14px;
    }

    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 40px -8px rgba(201, 162, 39, 0.8), 0 0 30px -5px rgba(201, 162, 39, 0.4);
    }

    .submit-btn:active {
        transform: translateY(-1px);
    }

    .submit-btn:focus-visible {
        outline: 2px solid var(--brass-light);
        outline-offset: 3px;
    }

    .bottom {
        text-align: center;
        margin-top: 24px;
        font-size: 13.5px;
        color: var(--cream-dim);
    }

    .bottom a {
        color: var(--brass-light);
        font-weight: 600;
        text-decoration: none;
        transition: color 0.2s;
    }

    .bottom a:hover {
        color: var(--amber);
        text-decoration: underline;
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 900px) {
        .page {
            grid-template-columns: 1fr;
        }

        .panel-photo {
            min-height: 280px;
            padding: 40px 32px;
        }

        .photo-caption h2 {
            font-size: 28px;
        }

        .panel-form {
            padding: 40px 24px;
        }

        .form-wrap {
            max-width: 100%;
        }
    }

    @media (max-width: 600px) {
        .panel-photo {
            min-height: 240px;
            padding: 32px 24px;
        }

        .photo-caption h2 {
            font-size: 24px;
        }

        .photo-caption p {
            font-size: 14px;
        }

        .logo h1 {
            font-size: 18px;
        }

        .submit-btn {
            padding: 12px;
            font-size: 13px;
        }
    }

    /* ---------- Accessibility ---------- */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.001ms !important;
        }
    }
    </style>

</head>

<body>

    <div class="page">

        <!-- LEFT / TOP: photo -->
        <section class="panel panel-photo" aria-hidden="true">
            <div class="photo-caption">
                <span class="photo-mark">GigBoard</span>
                <h2>Welcome <span class="accent">back.</span></h2>
                <p>Pick up where you left off — new gigs and applications await your next move.</p>
                <div class="typewriter-line" id="typewriter">
                    <span>Find your next gig in seconds</span>
                    <span class="cursor"></span>
                </div>
            </div>
        </section>

        <!-- RIGHT / BOTTOM: the form -->
        <section class="panel panel-form">

            <div class="form-wrap">

                <div class="logo">
                    <span class="logo-mark">GB</span>
                    <div>
                        <h1>GigBoard</h1>
                        <p>Log in to your account</p>
                    </div>
                </div>

               <form action="/gigboard/auth/login_process.php" method="POST" id="loginForm">

                    <div class="input-group">
                        <label for="login">Email or username</label>
                        <input type="text" id="login" name="login" required autocomplete="username">
                    </div>

                    <div class="input-group">
                        <div class="label-row">
                            <label for="password">Password</label>
                            <a href="forgot_password.php">Forgot password?</a>
                        </div>
                        <div class="password-field">
                            <input type="password" id="password" name="password" required
                                autocomplete="current-password">
                            <button type="button" class="toggle-visibility" id="toggleVisibility"
                                aria-label="Show password">show</button>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        Log in
                    </button>

                </form>

                <div class="bottom">
                    Don't have an account?<a href="/gigboard/register.php">Register</a>
                </div>

            </div>

        </section>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password visibility toggle
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('toggleVisibility');

        toggleBtn.addEventListener('click', function() {
            const showing = passwordInput.type === 'text';
            passwordInput.type = showing ? 'password' : 'text';
            toggleBtn.textContent = showing ? 'show' : 'hide';
        });

        // Typewriter animation
        function typewriterAnimation() {
            const texts = [
                'Find your next gig in seconds',
                'Real pay, no fees, ever',
                'Post & apply instantly'
            ];
            let textIndex = 0;
            let charIndex = 0;
            const typewriterEl = document.getElementById('typewriter');
            const textSpan = typewriterEl.querySelector('span:first-child');
            const cursor = typewriterEl.querySelector('.cursor');

            function type() {
                if (charIndex < texts[textIndex].length) {
                    textSpan.textContent += texts[textIndex].charAt(charIndex);
                    charIndex++;
                    setTimeout(type, 50);
                } else {
                    setTimeout(erase, 2500);
                }
            }

            function erase() {
                if (charIndex > 0) {
                    textSpan.textContent = texts[textIndex].substring(0, charIndex - 1);
                    charIndex--;
                    setTimeout(erase, 30);
                } else {
                    textIndex = (textIndex + 1) % texts.length;
                    setTimeout(type, 500);
                }
            }

            type();
        }

        // Stagger animations for form elements
        const formElements = document.querySelectorAll('.input-group, .submit-btn, .bottom');
        formElements.forEach((el, index) => {
            el.style.animation = `slideIn 0.6s ${0.1 + index * 0.1}s ${getComputedStyle(document.documentElement).getPropertyValue('--ease')} both`;
        });

        // Add a subtle glow effect on input focus
        const inputs = document.querySelectorAll('.input-group input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.background = 'rgba(16, 28, 48, 0.9)';
            });
            input.addEventListener('blur', function() {
                this.style.background = 'rgba(16, 28, 48, 0.6)';
            });
        });

        // Form submission feedback
        const loginForm = document.getElementById('loginForm');
        loginForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.submit-btn');
            submitBtn.style.transform = 'scale(0.98)';
            setTimeout(() => {
                submitBtn.style.transform = '';
            }, 100);
        });

        // Start typewriter after a short delay
        setTimeout(typewriterAnimation, 300);
    });
    </script>

</body>

</html>
