<?php
include 'db_connection.php';

$venue_query = "SELECT venue_id, venue_name FROM venue";
$venue_result = mysqli_query($conn, $venue_query);

$venues = [];
if ($venue_result && mysqli_num_rows($venue_result) > 0) {
    while ($row = mysqli_fetch_assoc($venue_result)) {
        $venues[] = $row;
    }
}

$booking_query = "SELECT venue_id, MONTHNAME(event_date) as b_month, YEAR(event_date) as b_year, COUNT(booking_id) as total_bookings 
                  FROM booking 
                  GROUP BY venue_id, b_month, b_year";
$booking_result = mysqli_query($conn, $booking_query);

$booking_data = [];
if ($booking_result && mysqli_num_rows($booking_result) > 0) {
    while ($row = mysqli_fetch_assoc($booking_result)) {
        $booking_data[] = $row;
    }
}

// -------------------------------------------------------------------------
// RATING REPORT QUERIES (Merujuk kepada table 'rating' dan column 'rating_star')
// -------------------------------------------------------------------------
$rating_summary_query = "SELECT COUNT(rating_id) as total_reviews, AVG(rating_star) as avg_rating FROM rating";
$rating_summary_result = mysqli_query($conn, $rating_summary_query);
$rating_summary = $rating_summary_result ? mysqli_fetch_assoc($rating_summary_result) : ['total_reviews' => 0, 'avg_rating' => 0];

$total_reviews = $rating_summary['total_reviews'] ?? 0;
$avg_rating = $total_reviews > 0 ? number_format($rating_summary['avg_rating'], 1) : '0.0';

$rating_dist_query = "SELECT rating_star, COUNT(rating_id) as count FROM rating GROUP BY rating_star";
$rating_dist_result = mysqli_query($conn, $rating_dist_query);

$rating_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
if ($rating_dist_result && mysqli_num_rows($rating_dist_result) > 0) {
    while ($row = mysqli_fetch_assoc($rating_dist_result)) {
        $rating_counts[(int)$row['rating_star']] = (int)$row['count'];
    }
}

$max_rating_count = max($rating_counts) > 0 ? max($rating_counts) : 1;
$pct_5 = ($rating_counts[5] / $max_rating_count) * 100;
$pct_4 = ($rating_counts[4] / $max_rating_count) * 100;
$pct_3 = ($rating_counts[3] / $max_rating_count) * 100;
$pct_2 = ($rating_counts[2] / $max_rating_count) * 100;
$pct_1 = ($rating_counts[1] / $max_rating_count) * 100;

// -------------------------------------------------------------------------
// PAYMENT REPORT QUERIES (Merujuk kepada table 'payment')
// -------------------------------------------------------------------------
$payment_count_query = "SELECT 
    SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) as successful_payments,
    SUM(CASE WHEN payment_status = 'Failed' THEN 1 ELSE 0 END) as failed_payments,
    SUM(CASE WHEN payment_status = 'Pending' THEN 1 ELSE 0 END) as pending_status_count,
    SUM(CASE WHEN payment_status = 'Paid' THEN payment_amount ELSE 0 END) as total_received_amount,
    SUM(CASE WHEN payment_status = 'Pending' THEN payment_amount ELSE 0 END) as pending_amount,
    COUNT(payment_id) as total_transactions
FROM payment";
$payment_count_result = mysqli_query($conn, $payment_count_query);
$payment_counts_data = $payment_count_result ? mysqli_fetch_assoc($payment_count_result) : [
    'successful_payments' => 0, 
    'failed_payments' => 0, 
    'pending_status_count' => 0,
    'total_received_amount' => 0,
    'pending_amount' => 0,
    'total_transactions' => 0
];

$total_transactions = $payment_counts_data['total_transactions'] ?? 0;
$successful_payments = $payment_counts_data['successful_payments'] ?? 0;
$failed_payments = $payment_counts_data['failed_payments'] ?? 0;
$pending_status_count = $payment_counts_data['pending_status_count'] ?? 0;
$total_received_amount = $payment_counts_data['total_received_amount'] ?? 0;
$pending_amount = $payment_counts_data['pending_amount'] ?? 0;

$total_payments_count = $total_transactions > 0 ? $total_transactions : 1;

$pct_paid = ($successful_payments / $total_payments_count) * 100;
$pct_pending_status = ($pending_status_count / $total_payments_count) * 100;
$pct_failed = ($failed_payments / $total_payments_count) * 100;

$payment_success_rate = $total_transactions > 0 ? ($successful_payments / $total_transactions) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Dashboard</title>
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
        content: '✨🌸';
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
        content: '💍';
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

    .content-section {
        background-color: var(--white);
        color: var(--text-dark);
        margin: 0 auto 30px auto;
        padding: 35px;
        border-radius: var(--radius-lg);
        width: 100%;
        max-width: 1300px;
        position: relative;
        min-height: 550px;
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
        margin-bottom: 45px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .section-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary-burgundy);
        font-family: 'Playfair Display', serif;
    }

    .btn-premium {
        background: linear-gradient(135deg, var(--primary-burgundy) 0%, var(--primary-dark) 100%);
        color: var(--white);
        border: none;
        padding: 11px 24px;
        border-radius: 30px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(122, 34, 79, 0.2);
    }

    .btn-premium:hover {
        background: linear-gradient(135deg, var(--primary-dark) 0%, #400d27 100%);
        transform: translateY(-1px);
    }

    .filter-controls {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .custom-select {
        background-color: var(--bg-page);
        color: var(--text-dark);
        padding: 11px 16px;
        font-size: 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s;
        min-width: 110px;
        outline: none;
    }

    .custom-select:focus {
        background-color: var(--white);
        border-color: var(--primary-burgundy);
        box-shadow: 0 0 0 3.5px rgba(122, 34, 79, 0.12);
    }

    .chart-legend {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        color: var(--text-muted);
        margin-bottom: 30px;
        margin-top: -5px;
        font-weight: 500;
    }

    .legend-color {
        width: 30px;
        height: 14px;
        background: linear-gradient(135deg, var(--primary-burgundy) 0%, var(--primary-dark) 100%);
        border-radius: 4px;
    }

    .chart-container {
        position: relative;
        width: 100%;
        height: 300px;
        margin-top: 20px;
    }

    .chart-lines {
        position: absolute;
        width: 100%;
        height: 250px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .chart-row {
        display: flex;
        align-items: center;
        width: 100%;
    }

    .axis-label {
        width: 25px;
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 600;
    }

    .grid-line {
        flex-grow: 1;
        border-bottom: 1px solid var(--border-color);
    }

    .bars-area {
        position: absolute;
        left: 25px;
        bottom: 0px;
        width: calc(100% - 25px);
        height: 290px;
        display: flex;
        gap: 40px;
        padding-left: 20px;
        padding-bottom: 40px;
        overflow-x: auto;
        white-space: nowrap;
    }

    .bars-area::-webkit-scrollbar {
        height: 6px;
    }
    .bars-area::-webkit-scrollbar-track {
        background: var(--bg-page);
        border-radius: 10px;
    }
    .bars-area::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 10px;
    }
    .bars-area::-webkit-scrollbar-thumb:hover {
        background: var(--text-muted);
    }

    .bar-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 120px; 
        min-width: 120px;
        height: 250px;
        justify-content: flex-end;
        position: relative;
    }

    .bar {
        width: 45px;
        background: linear-gradient(135deg, var(--primary-burgundy) 0%, var(--primary-dark) 100%);
        height: 0%;
        transition: height 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 6px 6px 0 0;
        box-shadow: 0 4px 12px rgba(122, 34, 79, 0.2);
    }

    .bar-label {
        position: absolute;
        bottom: -35px;
        font-size: 12px;
        color: var(--text-dark);
        font-weight: 600;
        text-align: center;
        line-height: 1.3;
        width: 100%;
        white-space: normal;
        word-wrap: break-word;
    }

    /* Rating Report Styles */
    .rating-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .rating-card-box {
        background-color: var(--bg-page);
        padding: 20px 25px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
    }

    .rating-card-box .label-text {
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 8px;
    }

    .rating-card-box .main-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .rating-card-box .sub-text {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 5px;
    }

    .rating-distribution-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 6px;
        font-family: 'Playfair Display', serif;
    }

    .rating-distribution-subtitle {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 25px;
    }

    .rating-row-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        gap: 15px;
    }

    .rating-star-label {
        width: 75px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-dark);
    }

    .rating-bar-bg {
        flex-grow: 1;
        background-color: var(--bg-page);
        height: 14px;
        border-radius: 30px;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .rating-bar-fill {
        height: 100%;
        background: linear-gradient(135deg, var(--primary-burgundy) 0%, var(--primary-dark) 100%);
        border-radius: 30px;
    }

    .rating-count-value {
        width: 35px;
        text-align: right;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-dark);
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

    .modal-box {
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

    /* TOAST NOTIFICATION (DIALIH KE BAWAH) */
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
        transform: translateY(15px);
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
            <a href="admin_dashboard.php" class="active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="admin_venues.php"><i class="fa-solid fa-building"></i> Venues</a>
            <a href="admin_packages.php"><i class="fa-solid fa-box-archive"></i> Packages</a>
            <a href="admin_users.php"><i class="fa-solid fa-users"></i> Users</a>
            <a href="admin_bookings.php"><i class="fa-regular fa-calendar-check"></i> Bookings</a>
            <a class="logout" onclick="openModal('modal-logout-confirmation')"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</a>
        </div>
    </div>

    <div class="welcome-banner">
        <h2>Welcome, Admin!</h2>
        <p>Manage users, venues and generate reports.</p>
    </div>

    <!-- Monthly Booking Report Section -->
    <div class="content-section">
        <div class="section-header">
            <h3>Monthly Booking Report</h3>
            
            <div class="filter-controls">
                <select class="custom-select" id="month-select">
                    <option value="Month" selected>Month</option> 
                    <option value="January">January</option>
                    <option value="February">February</option>
                    <option value="March">March</option>
                    <option value="April">April</option>
                    <option value="May">May</option>
                    <option value="June">June</option>
                    <option value="July">July</option>
                    <option value="August">August</option>
                    <option value="September">September</option>
                    <option value="October">October</option>
                    <option value="November">November</option>
                    <option value="December">December</option>
                </select>
                
                <select class="custom-select" id="year-select">
                    <option value="Year" selected>Year</option> 
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
                    <option value="2026">2026</option>
                    <option value="2027">2027</option>
                    <option value="2028">2028</option>
                    <option value="2029">2029</option>
                    <option value="2030">2030</option>
                </select>
                
                <button class="btn-premium" onclick="triggerReportGeneration()">Generate Graph</button>
            </div>
        </div>

        <div class="chart-legend">
            <div class="legend-color"></div>
            <span>Number of booking</span>
        </div>

        <div class="chart-container">
            <div class="chart-lines">
                <div class="chart-row"><span class="axis-label">6</span><div class="grid-line"></div></div>
                <div class="chart-row"><span class="axis-label">5</span><div class="grid-line"></div></div>
                <div class="chart-row"><span class="axis-label">4</span><div class="grid-line"></div></div>
                <div class="chart-row"><span class="axis-label">3</span><div class="grid-line"></div></div>
                <div class="chart-row"><span class="axis-label">2</span><div class="grid-line"></div></div>
                <div class="chart-row"><span class="axis-label">1</span><div class="grid-line"></div></div>
                <div class="chart-row"><span class="axis-label">0</span><div class="grid-line"></div></div>
            </div>

            <div class="bars-area">
                <?php 
                if (!empty($venues)): 
                    foreach ($venues as $venue): 
                ?>
                   <div class="bar-wrapper">
                        <div class="bar" id="bar-id-<?php echo $venue['venue_id']; ?>" style="height: 0%;"></div> 
                        <div class="bar-label"><?php echo htmlspecialchars($venue['venue_name']); ?></div>
                    </div>
                <?php 
                    endforeach; 
                endif; 
                ?>
            </div>
        </div>
    </div>

    <!-- Rating Report Section -->
    <div class="content-section">
        <div class="section-header">
            <div>
                <h3 style="margin-bottom: 5px;">Rating Report</h3>
                <p style="font-size: 14px; color: var(--text-muted); font-weight: 400;">Overview of customer ratings for wedding venues</p>
            </div>
        </div>

        <div class="rating-overview">
            <div class="rating-card-box">
                <div class="label-text">Total Reviews</div>
                <div class="main-value"><?php echo $total_reviews; ?></div>
                <div class="sub-text">All venue reviews</div>
            </div>
            <div class="rating-card-box">
                <div class="label-text">Average Rating</div>
                <div class="main-value">
                    <?php echo $avg_rating; ?> 
                    <i class="fa-solid fa-star" style="color: #ecc94b; font-size: 22px;"></i>
                </div>
                <div class="sub-text">Out of 5.0</div>
            </div>
        </div>

        <div class="rating-distribution-title">Rating Distribution</div>
        <div class="rating-distribution-subtitle">Number of reviews by star rating</div>

        <div class="rating-row-item">
            <div class="rating-star-label">5 Stars</div>
            <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $pct_5; ?>%;"></div></div>
            <div class="rating-count-value"><?php echo $rating_counts[5]; ?></div>
        </div>

        <div class="rating-row-item">
            <div class="rating-star-label">4 Stars</div>
            <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $pct_4; ?>%;"></div></div>
            <div class="rating-count-value"><?php echo $rating_counts[4]; ?></div>
        </div>

        <div class="rating-row-item">
            <div class="rating-star-label">3 Stars</div>
            <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $pct_3; ?>%;"></div></div>
            <div class="rating-count-value"><?php echo $rating_counts[3]; ?></div>
        </div>

        <div class="rating-row-item">
            <div class="rating-star-label">2 Stars</div>
            <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $pct_2; ?>%;"></div></div>
            <div class="rating-count-value"><?php echo $rating_counts[2]; ?></div>
        </div>

        <div class="rating-row-item">
            <div class="rating-star-label">1 Star</div>
            <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $pct_1; ?>%;"></div></div>
            <div class="rating-count-value"><?php echo $rating_counts[1]; ?></div>
        </div>
    </div>

    <!-- Payment Report Section -->
    <div class="content-section">
        <div class="section-header">
            <div>
                <h3 style="margin-bottom: 5px;">Payment Report</h3>
                <p style="font-size: 14px; color: var(--text-muted); font-weight: 400;">Overview of customer deposit payments</p>
            </div>
        </div>

        <div class="rating-overview">
            <div class="rating-card-box">
                <div class="label-text">Total Transactions</div>
                <div class="main-value"><?php echo $total_transactions; ?></div>
            </div>
            <div class="rating-card-box">
                <div class="label-text">Payment Success Rate</div>
                <div class="main-value" style="color: #2f855a;"><?php echo number_format($payment_success_rate, 1); ?>%</div>
            </div>
        </div>

        <div class="rating-distribution-title">Payment Status Overview</div>
        <div class="rating-distribution-subtitle">Summary of payment statuses</div>

        <div class="rating-row-item">
            <div class="rating-star-label">Paid</div>
            <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $pct_paid; ?>%;"></div></div>
            <div class="rating-count-value" style="width: 75px; text-align: right;"><?php echo $successful_payments; ?> payments</div>
        </div>

        <div class="rating-row-item">
            <div class="rating-star-label">Pending</div>
            <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $pct_pending_status; ?>%; background: #d69e2e;"></div></div>
            <div class="rating-count-value" style="width: 75px; text-align: right;"><?php echo $pending_status_count; ?> payments</div>
        </div>

        <div class="rating-row-item">
            <div class="rating-star-label">Unpaid</div>
            <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $pct_failed; ?>%; background: #e53e3e;"></div></div>
            <div class="rating-count-value" style="width: 75px; text-align: right;"><?php echo $failed_payments; ?> payments</div>
        </div>
    </div>
</div>

<div class="toast-notification" id="toast-global"></div>

<div id="modal-logout-confirmation" class="modal-overlay">
    <div class="modal-box">
        <p>Are you sure you want to log out? You will need to login again to access the panel.</p>
        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="closeModal('modal-logout-confirmation')">Cancel</button>
            <button type="button" class="btn-confirm-ok" onclick="confirmLogout()">Log Out</button>
        </div>
    </div>
</div>

<script>
    const databaseBookings = <?php echo json_encode($booking_data); ?>;
    const currentVenues = <?php echo json_encode($venues); ?>;

    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'flex';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    function confirmLogout() {
        window.location.href = 'index.php';
    }

    function triggerReportGeneration() {
        const month = document.getElementById('month-select').value;
        const year = document.getElementById('year-select').value;

        currentVenues.forEach(v => {
            const barElement = document.getElementById(`bar-id-${v.venue_id}`);
            if (barElement) barElement.style.height = "0%";
        });

        if (month === "Month" || year === "Year") {
            triggerToast('Please select both Month and Year!');
            return;
        }

        let matchFound = false;
        
        databaseBookings.forEach(booking => {
            if (booking.b_month === month && booking.b_year === year) {
                const barElement = document.getElementById(`bar-id-${booking.venue_id}`);
                if (barElement) {
                    let total = parseInt(booking.total_bookings);
                    if (total > 6) total = 6;
                    
                    const heightPercent = (total / 6) * 100;
                    barElement.style.height = `${heightPercent}%`;
                    matchFound = true;
                }
            }
        });

        triggerToast(`Report for ${month} ${year} updated successfully!`);
    }

    function triggerToast(message) {
        const toast = document.getElementById('toast-global');
        if (toast) {
            toast.textContent = message;
            toast.classList.add('show-toast');
            setTimeout(() => {
                toast.classList.remove('show-toast'); 
            }, 3000);
        }
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }
</script>

</body>
</html>