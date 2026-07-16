<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | GigBoard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@500&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="assets/css/auth.css">

</head>

<body>

    <div class="page">

        <!-- LEFT / TOP: photo -->
        <section class="panel panel-photo" aria-hidden="true">
            <div class="photo-caption">
                <span class="photo-mark">GigBoard</span>
                <h2>Find your next gig, or your next hire.</h2>
                <p>Trusted by workers and employers across Kigali.</p>
            </div>
        </section>

        <!-- RIGHT / BOTTOM: the form -->
        <section class="panel panel-form">

            <div class="form-wrap">

                <div class="logo">
                    <span class="logo-mark">GB</span>
                    <div>
                        <h1>GigBoard</h1>
                        <p>Create your account</p>
                    </div>
                </div>

                <form action="auth/register_process.php" method="POST" id="registerForm">

                    <div class="role-toggle" role="radiogroup" aria-label="Account type">
                        <input type="radio" name="role" id="role-worker" value="worker" checked>
                        <label for="role-worker" class="role-pill">Find work</label>

                        <input type="radio" name="role" id="role-employer" value="employer">
                        <label for="role-employer" class="role-pill">Hire talent</label>

                        <span class="role-indicator"></span>
                    </div>

                    <div class="grid-2">
                        <div class="input-group">
                            <label for="fullname">Full name</label>
                            <input type="text" id="fullname" name="fullname" required autocomplete="name">
                        </div>

                        <div class="input-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" required autocomplete="username">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="input-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required autocomplete="email">
                        </div>

                        <div class="input-group">
                            <label for="phone">Phone number</label>
                            <input type="text" id="phone" name="phone" required autocomplete="tel">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="input-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required autocomplete="new-password">
                        </div>

                        <div class="input-group">
                            <label for="confirm_password">Confirm password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <span>Create account</span>
                    </button>

                </form>

                <div class="bottom">
                    Already have an account? <a href="login.php">Log in</a>

                </div>

            </div>

        </section>

    </div>

</body>

</html>