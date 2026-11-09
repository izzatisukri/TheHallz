<?php
// index.php - Landing / Intro Dashboard
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TheHallz - Wedding Venue Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            width: 100vw;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            /* Real-life system background with dark purple gradient overlay & wedding venue stock image */
            background: linear-gradient(rgba(43, 0, 25, 0.85), rgba(82, 1, 49, 0.9)), 
                        url('https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1920&q=80') no-repeat center center fixed;
            background-size: cover;
            color: #ffffff;
            overflow-x: hidden;
        }

        /* Navigation Bar */
        header {
            width: 100%;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(43, 0, 25, 0.4);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo span {
            color: #d946ef;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-btn {
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-btn.login {
            background: transparent;
            color: #ffffff;
            border: 1.5px solid rgba(255, 255, 255, 0.4);
        }

        .nav-btn.login:hover {
            border-color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-btn.register {
            background-color: #710349;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(113, 3, 73, 0.4);
        }

        .nav-btn.register:hover {
            background-color: #8a0559;
            transform: translateY(-1px);
        }

        /* Hero Section */
        .hero-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
        }

        .hero-content {
            max-width: 750px;
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .hero-badge {
            display: inline-block;
            padding: 6px 14px;
            background: rgba(113, 3, 73, 0.6);
            border: 1px solid rgba(217, 70, 239, 0.3);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 20px;
            color: #f5d0fe;
        }

        .hero-title {
            font-size: 42px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .hero-title span {
            background: linear-gradient(135deg, #f472b6, #db2777);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-description {
            font-size: 16px;
            color: #cbd5e1;
            line-height: 1.6;
            margin-bottom: 35px;
        }

        .hero-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn-primary {
            background-color: #710349;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px 32px;
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 6px 20px rgba(113, 3, 73, 0.5);
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #8a0559;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(113, 3, 73, 0.7);
        }

        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1.5px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 14px 32px;
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
            backdrop-filter: blur(4px);
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
        }

        /* Footer Info Bar */
        footer {
            width: 100%;
            padding: 15px 50px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
            background: rgba(27, 0, 16, 0.6);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            gap: 5px;
            align-items: center;
        }

        .footer-contact {
            color: #cbd5e1;
        }

        .footer-contact a {
            color: #f472b6;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-contact a:hover {
            text-decoration: underline;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Mobile Adjustments */
        @media (max-width: 768px) {
            header { padding: 15px 20px; }
            .hero-title { font-size: 30px; }
            .hero-actions { flex-direction: column; }
            .nav-links { gap: 10px; }
            .nav-btn { padding: 6px 12px; font-size: 13px; }
            footer { padding: 15px 20px; }
        }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <header>
        <a href="index.php" class="logo">The<span>Hallz</span></a>
        <div class="nav-links">
            <a href="login.php" class="nav-btn login">Login</a>
            <a href="register.php" class="nav-btn register">Register</a>
        </div>
    </header>

    <!-- Main Hero / Intro Section -->
    <main class="hero-container">
        <div class="hero-content">
            <div class="hero-badge">Wedding Venue Management System</div>
            <h1 class="hero-title">Plan Your Dream Wedding With <span>TheHallz</span></h1>
            <p class="hero-description">
                Discover and manage exclusive wedding venues seamlessly. Connect with venue owners, coordinate bookings, and organize your special day all in one professional platform.
            </p>
            <div class="hero-actions">
                <a href="login.php" class="btn-primary">Get Started / Login</a>
                <a href="register.php" class="btn-secondary">Create Account</a>
            </div>
        </div>
    </main>

    <!-- Footer with Contact Us -->
    <footer>
        <div class="footer-contact">
            Have any problem? Contact us at <a href="mailto:admin@gmail.com">admin@gmail.com</a>
        </div>
        <div>
            &copy; 2026 TheHallz System. All rights reserved.
        </div>
    </footer>

</body>
</html>