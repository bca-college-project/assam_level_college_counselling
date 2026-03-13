<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission system - Smart College Counselling</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --landing-bg: #ffffff;
            --hero-gradient: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        }
        [data-theme="dark"] {
            --landing-bg: #0f172a;
        }
        body {
            margin: 0;
            padding: 0;
            background: var(--landing-bg);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }
        .navbar {
            padding: 24px 80px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(var(--landing-bg), 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }
        .hero {
            padding: 160px 80px 100px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 80vh;
        }
        .hero-text {
            max-width: 600px;
        }
        .hero-text h1 {
            font-size: 64px;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 24px;
            background: var(--hero-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-text p {
            font-size: 20px;
            color: var(--text-dim);
            line-height: 1.6;
            margin-bottom: 40px;
        }
        .hero-image {
            position: relative;
            width: 500px;
            height: 500px;
            background: var(--hero-gradient);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation: morph 8s ease-in-out infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 120px;
        }
        @keyframes morph {
            0% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
            50% { border-radius: 50% 50% 33% 67% / 55% 27% 73% 45%; }
            100% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
        }
        .cta-group {
            display: flex;
            gap: 20px;
        }
        .btn-large {
            padding: 18px 36px;
            font-size: 18px;
            font-weight: 700;
            border-radius: 14px;
            text-decoration: none;
            transition: transform 0.2s;
        }
        .btn-large:hover {
            transform: translateY(-4px);
        }
        @media (max-width: 968px) {
            .hero { flex-direction: column; text-align: center; padding: 120px 20px; }
            .hero-image { display: none; }
            .cta-group { justify-content: center; }
            .hero-text h1 { font-size: 40px; }
        }
    </style>
    <script src="js/theme.js"></script>
</head>
<body>
    <nav class="navbar">
        <div style="font-size: 24px; font-weight: 900; color: var(--text-main);">AS<span style="color: var(--accent);">.</span></div>
        <div style="display: flex; align-items: center; gap: 32px;">
            <button class="theme-toggle" onclick="toggleTheme()" style="background: none; border: 1px solid var(--border); color: var(--text-main);"><i class="fas fa-moon"></i></button>
            <a href="login.php" style="text-decoration: none; font-weight: 700; color: var(--text-main);">Sign In</a>
            <a href="register.php" class="btn" style="padding: 10px 24px; border-radius: 10px; text-decoration: none; color: white;">Get Started</a>
        </div>
    </nav>

    <section class="hero">
        <div class="hero-text">
            <h1>Your Future, <br> Simplified.</h1>
            <p>The smarter way to manage college admissions. Transparent merit lists, instant document verification, and a seamless path to your dream institution.</p>
            <div class="cta-group">
                <a href="register.php" class="btn-large" style="background: var(--hero-gradient); color: white; box-shadow: 0 20px 40px rgba(37, 99, 235, 0.2);">Start Your Journey</a>
                <a href="login.php" class="btn-large" style="background: var(--bg-card); color: var(--text-main); border: 2px solid var(--border);">Admin Portal</a>
            </div>
        </div>
        <div class="hero-image">
            <i class="fas fa-graduation-cap"></i>
        </div>
    </section>

    <div style="padding: 80px; text-align: center; background: var(--bg-app); border-top: 1px solid var(--border);">
        <p style="color: var(--text-dim); font-size: 14px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 48px;">Trusted by leading institutions</p>
        <div style="display: flex; justify-content: center; gap: 60px; flex-wrap: wrap; opacity: 0.5; filter: grayscale(1);">
            <i class="fas fa-university fa-3x"></i>
            <i class="fas fa-school fa-3x"></i>
            <i class="fas fa-building-columns fa-3x"></i>
            <i class="fas fa-landmark fa-3x"></i>
        </div>
    </div>

    <footer style="padding: 60px 80px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; font-size: 14px; color: var(--text-dim);">
        <div>© 2026 Admission system. All rights reserved.</div>
        <div style="display: flex; gap: 24px;">
            <a href="#" style="color: inherit; text-decoration: none;">Privacy</a>
            <a href="#" style="color: inherit; text-decoration: none;">Terms</a>
            <a href="#" style="color: inherit; text-decoration: none;">Contact</a>
        </div>
    </footer>
</body>
</html>
