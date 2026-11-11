<?php include 'db_connection.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - All Bookings</title>
    <!-- Google Fonts & Font Awesome Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --primary-burgundy: #7a224f;
        --primary-dark: #581637;
        --accent-pink: #fce4ec;
        --badge-pink: #f8bbd0;
        --bg-page: #f9f6f8;
        --text-dark: #2d3748;
        --text-muted: #718096;
        --border-color: #f0e6eb;
        --white: #ffffff;
        --radius-lg: 20px;
        --radius-md: 14px;
        --radius-sm: 8px;
        --shadow-soft: 0 10px 30px rgba(122, 34, 79, 0.05);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    body {
        background-color: var(--bg-page);
        color: var(--text-dark);
        min-height: 100vh;
        -webkit-font-smoothing: antialiased;
        overflow-x: hidden;
        position: relative;
    }

    body::after {
        content: '';
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 120px;
        background: linear-gradient(180deg, rgba(253, 237, 244, 0) 0%, rgba(248, 187, 208, 0.3) 100%);
        z-index: -1;
        pointer-events: none;
    }

    .container {
        width: 100%;
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 20px 60px 20px;
    }

    .header {
        background-color: var(--white);
        padding: 16px 35px;
        margin: 0 -20px 25px -20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
    }

    .header::after {
        content: '🌸🍃';
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.15;
        font-size: 28px;
        pointer-events: none;
    }

    .logo-area {
        font-size: 28px;
        font-weight: 400;
        color: var(--primary-burgundy);
        display: flex;
        align-items: center;
        gap: 12px;
        font-family: 'Great Vibes', cursive;
    }

    .logo-area::before {
        content: '🌸';
        width: 38px;
        height: 38px;
        background: var(--accent-pink);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-burgundy);
        font-size: 18px;
    }

    .nav-links {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .nav-links div.nav-links {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .nav-links a {
        text-decoration: none;
        color: var(--text-muted);
        font-size: 14.5px;
        font-weight: 600;
        cursor: pointer;
        padding: 9px 18px;
        border-radius: 30px;
        transition: all 0.25s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: none !important;
    }

    .nav-links a:hover {
        color: var(--primary-burgundy);
        background: var(--accent-pink);
    }

    .nav-links a.active {
        color: var(--white) !important;
        background: var(--primary-burgundy);
        box-shadow: 0 4px 12px rgba(122, 34, 79, 0.25);
        border-bottom: none !important;
        padding: 9px 18px !important;
    }

    .nav-links a.logout {
        color: var(--primary-burgundy);
    }

    .nav-links a.logout:hover {
        background-color: #fff0f3;
        color: #d32f2f;
    }

    .welcome-banner {
        background: linear-gradient(105deg, var(--primary-dark) 0%, var(--primary-burgundy) 55%, #9c2d67 100%);
        color: white;
        margin: 0 auto 30px auto;
        padding: 40px 30px;
        border-radius: var(--radius-lg);
        width: 100%;
        max-width: 1300px;
        box-shadow: 0 12px 30px rgba(88, 22, 55, 0.2);
        text-align: center;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .welcome-banner::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        background: url('images/weddingVenue.jpg') center/cover no-repeat;
        opacity: 0.15;
        mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,0) 100%);
        -webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,0) 100%);
    }

    .welcome-banner h2 {
        font-family: 'Playfair Display', serif;
        font-size: 34px;
        font-weight: 600;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
        position: relative;
        z-index: 2;
    }

    .welcome-banner p {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 400;
        position: relative;
        z-index: 2;
    }

    /* Content Section Layout */
    .content-section {
        background-color: var(--white);
        color: var(--text-dark);
        margin: 0 auto;
        padding: 35px;
        border-radius: var(--radius-lg);
        width: 100%;
        max-width: 1300px;
        position: relative;
        min-height: 320px;
        box-shadow: var(--shadow-soft);
        border: 1px solid var(--border-color);
        animation: fadeIn 0.3s ease;
    }

    .content-section.active-section {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .section-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary-burgundy);
        font-family: 'Playfair Display', serif;
    }

    .btn-delete {
        background-color: #fff5f5;
        color: #e53e3e;
        border: 1px solid #fed7d7;
        padding: 7px 16px;
        border-radius: 20px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .btn-delete:hover {
        background-color: #e53e3e;
        color: white;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 14.5px;
    }

    th, td {
        padding: 16px 18px;
        border-bottom: 1px solid var(--border-color);
    }

    th {
        font-weight: 700;
        color: var(--primary-burgundy);
        background-color: var(--bg-page);
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    td {
        color: var(--text-dark);
    }

    tr:hover td {
        background-color: #fcf8fa;
    }

    td:last-child {
        white-space: nowrap;
    }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(45, 20, 35, 0.55);
        backdrop-filter: blur(5px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        display: none;
    }

    .modal-box-delete {
        background-color: var(--white);
        width: 90%;
        border-radius: var(--radius-lg);
        padding: 35px;
        color: var(--text-dark);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        border: 1px solid var(--border-color);
        animation: modalSlideUp 0.3s ease;
        max-width: 420px; 
        text-align: center;
    }

    @keyframes modalSlideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .form-actions {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 25px;
    }

    .btn-cancel {
        background-color: var(--white);
        color: var(--text-muted);
        border: 1px solid var(--border-color);
        padding: 11px 24px;
        font-size: 14px;
        font-weight: 600;
        border-radius: 30px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-cancel:hover {
        background-color: var(--bg-page);
        color: var(--text-dark);
    }

    .modal-box-delete p {
        font-size: 15px;
        color: var(--text-dark);
        margin-bottom: 25px;
        line-height: 1.5;
        font-weight: 500;
    }

    .btn-confirm-ok {
        background-color: #e53e3e;
        color: var(--white);
        border: none;
        padding: 11px 24px;
        font-size: 14px;
        font-weight: 600;
        border-radius: 30px;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .btn-confirm-ok:hover {
        background-color: #c53030;
    }

    .toast-notification {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background-color: var(--white);
        color: var(--text-dark);
        padding: 16px 24px;
        border-radius: var(--radius-md);
        font-size: 14px;
        font-weight: 600;
        z-index: 1010;
        box-shadow: 0 12px 35px rgba(122, 34, 79, 0.15);
        border-left: 5px solid var(--primary-burgundy);
        visibility: hidden;
        opacity: 0;
        transform: translateY(-15px);
        transition: all 0.3s ease;
    }

    .toast-notification.show-toast {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="logo-area">TheHallz - Admin</div>
        <div class="nav-links">
            <div class="nav-links">
            <a href="admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="admin_venues.php"><i class="fa-solid fa-building"></i> Venues</a>
            <a href="admin_packages.php"><i class="fa-solid fa-box-archive"></i> Packages</a>
            <a href="admin_users.php"><i class="fa-solid fa-users"></i> Users</a>
            <a href="admin_bookings.php" class="active"><i class="fa-regular fa-calendar-check"></i> Bookings</a>
            <a class="logout" onclick="openModal('modal-logout-confirmation', this)"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</a>
        </div>
        </div>
    </div>

    <div class="welcome-banner">
        <h2>Welcome, Admin!</h2>
        <p>Manage users, venues and generate reports.</p>
    </div>

    <div class="content-section" style="display: block;">
        <div class="section-header">
            <h3>Booking Requests</h3>
        </div>
        <table id="table-bookings">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Venue</th>
                    <th>Event Date</th>
                    <th>Package</th>
                    <th>Guests</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT c.client_name, v.venue_name, b.event_date, p.package_name, b.num_of_guests AS guests, b.bookingstatus AS status 
                        FROM booking b
                        JOIN client c ON b.client_id = c.client_id
                        JOIN venue v ON b.venue_id = v.venue_id
                        JOIN package p ON b.package_id = p.package_id";
                
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status_color = '#d69e2e'; // default fallback (orange)
                        $st = strtolower(trim($row['status']));
                        if ($st === 'pending') {
                            $status_color = '#ed8936'; // orange
                        } elseif ($st === 'completed') {
                            $status_color = '#3182ce'; // biru
                        } elseif ($st === 'confirmed') {
                            $status_color = '#38a169'; // hijau
                        } elseif ($st === 'rejected' || $st === 'cancelled') {
                            $status_color = '#e53e3e'; // merah
                        }

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['client_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['venue_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['event_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['package_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['guests']) . "</td>";
                        echo "<td style='color: " . $status_color . "; font-weight: 600;'>" . htmlspecialchars($row['status']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center;'>No bookings found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="toast-notification" id="toast-global"></div>

<div id="modal-logout-confirmation" class="modal-overlay">
    <div class="modal-box-delete">
        <p>Are you sure you want to log out? You will need to login again to access the panel.</p>
        <div class="form-actions" style="justify-content: center; margin-top: 0;">
            <button type="button" class="btn-cancel" style="padding: 10px 24px;" onclick="closeModal('modal-logout-confirmation')">Cancel</button>
            <button type="button" class="btn-confirm-ok" id="btn-confirm-logout-action">Log Out</button>
        </div>
    </div>
</div>

<script>
    function openModal(modalId, buttonElement) {
        document.getElementById(modalId).style.display = 'flex';
        
        if (modalId === 'modal-logout-confirmation') {
            const confirmLogoutBtn = document.getElementById('btn-confirm-logout-action');
            confirmLogoutBtn.setAttribute('onclick', "confirmLogout()");
        }
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function confirmLogout() {
        window.location.href = 'index.php'; 
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }
</script>

</body>
</html>