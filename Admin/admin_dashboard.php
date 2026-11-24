<?php
session_start();
include 'db_connection.php';

$msg_toast = "";
if (isset($_SESSION['toast_message'])) {
    $msg_toast = $_SESSION['toast_message'];
    unset($_SESSION['toast_message']);
}

// Kenal pasti jadual admin & kolum jika ada
$admin_table = "";
$admin_id_col = "";
$admin_name_col = "";
$admin_phone_col = "";
$admin_email_col = "";

$check_admin_tbl = mysqli_query($conn, "SHOW TABLES LIKE 'admin'");
if ($check_admin_tbl && mysqli_num_rows($check_admin_tbl) > 0) {
    $admin_table = "admin";
    $cols_res = mysqli_query($conn, "SHOW COLUMNS FROM admin");
    if ($cols_res) {
        while ($c_row = mysqli_fetch_assoc($cols_res)) {
            $field = $c_row['Field'];
            if (stripos($field, 'id') !== false && $admin_id_col === "") $admin_id_col = $field;
            if (stripos($field, 'name') !== false && $admin_name_col === "") $admin_name_col = $field;
            if (stripos($field, 'phone') !== false || stripos($field, 'phonenum') !== false || stripos($field, 'tel') !== false) {
                $admin_phone_col = $field;
            }
            if (stripos($field, 'email') !== false && $admin_email_col === "") {
                $admin_email_col = $field;
            }
        }
    }
}

// PROSES EDIT PROFILE ADMIN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_type']) && $_POST['action_type'] == 'update_admin_profile') {
    $new_admin_name = trim($_POST['edit_admin_name']);
    $new_admin_phone = trim($_POST['edit_admin_phone']);

    if ($new_admin_name === '') {
        $_SESSION['toast_message'] = "Please enter a name.";
    } else {
        $_SESSION['admin_profile_name'] = $new_admin_name;
        $_SESSION['admin_profile_phone'] = $new_admin_phone;

        if ($admin_table !== "" && $admin_name_col !== "") {
            $esc_name = mysqli_real_escape_string($conn, $new_admin_name);
            $esc_phone = mysqli_real_escape_string($conn, $new_admin_phone);
            $set_parts = ["`$admin_name_col` = '$esc_name'"];
            if ($admin_phone_col !== "") {
                $set_parts[] = "`$admin_phone_col` = '$esc_phone'";
            }
            $where_clause = "";
            if ($admin_id_col !== "" && isset($_SESSION['admin_id'])) {
                $esc_id = mysqli_real_escape_string($conn, $_SESSION['admin_id']);
                $where_clause = " WHERE `$admin_id_col` = '$esc_id'";
            }
            mysqli_query($conn, "UPDATE `$admin_table` SET " . implode(", ", $set_parts) . $where_clause);
        }

        $_SESSION['toast_message'] = "Profile updated successfully!";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$admin_name = isset($_SESSION['admin_profile_name']) && $_SESSION['admin_profile_name'] !== '' ? $_SESSION['admin_profile_name'] : "Admin";
$admin_phone = isset($_SESSION['admin_profile_phone']) ? $_SESSION['admin_profile_phone'] : "";
$admin_email = isset($_SESSION['admin_profile_email']) ? $_SESSION['admin_profile_email'] : "";

if ($admin_table !== "" && $admin_name_col !== "") {
    $select_cols = "`$admin_name_col`"
        . ($admin_phone_col !== "" ? ", `$admin_phone_col`" : "")
        . ($admin_email_col !== "" ? ", `$admin_email_col`" : "");
    $where_clause = "";
    if ($admin_id_col !== "" && isset($_SESSION['admin_id'])) {
        $esc_id = mysqli_real_escape_string($conn, $_SESSION['admin_id']);
        $where_clause = " WHERE `$admin_id_col` = '$esc_id'";
    }
    $adm_q = mysqli_query($conn, "SELECT $select_cols FROM `$admin_table` $where_clause LIMIT 1");
    if ($adm_q && mysqli_num_rows($adm_q) > 0) {
        $adm_row = mysqli_fetch_assoc($adm_q);
        if (!isset($_SESSION['admin_profile_name']) && !empty($adm_row[$admin_name_col])) {
            $admin_name = $adm_row[$admin_name_col];
        }
        if (!isset($_SESSION['admin_profile_phone']) && $admin_phone_col !== "" && isset($adm_row[$admin_phone_col])) {
            $admin_phone = $adm_row[$admin_phone_col];
        }
        if ($admin_email_col !== "" && isset($adm_row[$admin_email_col])) {
            $admin_email = $adm_row[$admin_email_col];
        }
    }
}

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
// PAYMENT REPORT QUERIES (Merujuk kepada table 'booking' dan 'payment')
// -------------------------------------------------------------------------
$payment_count_query = "SELECT 
    SUM(CASE 
        WHEN LOWER(TRIM(COALESCE(b.bookingstatus, ''))) IN ('confirmed', 'completed')
         AND LOWER(TRIM(COALESCE(p.payment_status, ''))) = 'paid'
        THEN 1 ELSE 0 
    END) as successful_payments,
    SUM(CASE 
        WHEN LOWER(TRIM(COALESCE(b.bookingstatus, ''))) IN ('confirmed', 'completed')
         AND LOWER(TRIM(COALESCE(p.payment_status, ''))) <> 'paid'
        THEN 1 ELSE 0 
    END) as unpaid_payments,
    SUM(CASE 
        WHEN LOWER(TRIM(COALESCE(b.bookingstatus, ''))) = 'pending'
        THEN 1 ELSE 0 
    END) as pending_status_count,
    SUM(CASE 
        WHEN LOWER(TRIM(COALESCE(p.payment_status, ''))) = 'failed'
         AND LOWER(TRIM(COALESCE(b.bookingstatus, ''))) NOT IN ('cancelled', 'canceled')
        THEN 1 ELSE 0 
    END) as failed_payments,
    SUM(CASE 
        WHEN LOWER(TRIM(COALESCE(b.bookingstatus, ''))) IN ('confirmed', 'completed')
         AND LOWER(TRIM(COALESCE(p.payment_status, ''))) = 'paid'
        THEN COALESCE(p.payment_amount, 0) ELSE 0 
    END) as total_received_amount,
    SUM(CASE 
        WHEN LOWER(TRIM(COALESCE(b.bookingstatus, ''))) = 'pending'
        THEN COALESCE(p.payment_amount, 0) ELSE 0 
    END) as pending_amount,
    COUNT(b.booking_id) as total_transactions
FROM booking b
LEFT JOIN payment p ON b.booking_id = p.booking_id";
$payment_count_result = mysqli_query($conn, $payment_count_query);
$payment_counts_data = $payment_count_result ? mysqli_fetch_assoc($payment_count_result) : [
    'successful_payments' => 0, 
    'unpaid_payments' => 0,
    'failed_payments' => 0, 
    'pending_status_count' => 0,
    'total_received_amount' => 0,
    'pending_amount' => 0,
    'total_transactions' => 0
];

$total_transactions = $payment_counts_data['total_transactions'] ?? 0;
$successful_payments = $payment_counts_data['successful_payments'] ?? 0;
$unpaid_payments = $payment_counts_data['unpaid_payments'] ?? 0;
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

    .payment-two-by-two {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 20px !important;
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

    .modal-box-edit-profile {
        background-color: var(--white);
        width: 90%;
        max-width: 480px;
        border-radius: var(--radius-lg);
        padding: 34px;
        color: var(--text-dark);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        border: 1px solid var(--border-color);
        animation: modalSlideUp 0.3s ease;
        text-align: left;
    }

    .modal-box-edit-profile h3 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        font-weight: 700;
        color: #6b143e;
        margin-bottom: 20px;
    }

    .account-settings-box {
        background-color: #fcebf1;
        border-left: 4px solid #6b143e;
        border-radius: 12px;
        padding: 15px 18px;
        margin-bottom: 20px;
    }

    .account-settings-title {
        font-family: 'Playfair Display', serif;
        font-size: 17px;
        font-weight: 700;
        color: #6b143e;
        margin-bottom: 4px;
    }

    .account-settings-email {
        font-size: 13.5px;
        color: #64748b;
        font-weight: 500;
    }

    .edit-profile-group {
        margin-bottom: 16px;
    }

    .edit-profile-group label {
        display: block;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 7px;
    }

    .edit-profile-group input {
        width: 100%;
        padding: 12px 15px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14px;
        background-color: var(--bg-page);
        color: var(--text-dark);
        transition: all 0.2s ease;
    }

    .edit-profile-group input:focus {
        outline: none;
        background-color: var(--white);
        border-color: var(--primary-burgundy);
        box-shadow: 0 0 0 3.5px rgba(122, 34, 79, 0.12);
    }

    .edit-profile-actions {
        display: flex;
        gap: 14px;
        margin-top: 24px;
    }

    .btn-save-profile {
        flex: 1;
        background: #6b143e;
        color: var(--white);
        border: none;
        padding: 12px 20px;
        font-size: 14.5px;
        font-weight: 600;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-save-profile:hover {
        background: var(--primary-dark);
    }

    .btn-profile-cancel {
        flex: 1;
        background-color: #e6ebf0;
        color: #1e293b;
        border: none;
        padding: 12px 20px;
        font-size: 14.5px;
        font-weight: 600;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-profile-cancel:hover {
        background-color: #d8dee6;
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
            <a onclick="openEditAdminProfileModal()" title="Settings / Edit Profile"><i class="fa-solid fa-gear"></i></a>
            <a class="logout" onclick="openModal('modal-logout-confirmation')"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</a>
        </div>
    </div>

    <div class="welcome-banner">
        <h2 id="welcome-admin-heading">Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h2>
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

        <div class="rating-overview payment-two-by-two">
            <div class="rating-card-box">
                <div class="label-text">Total Bookings</div>
                <div class="main-value"><?php echo $total_transactions; ?></div>
            </div>
            <div class="rating-card-box">
                <div class="label-text">Paid</div>
                <div class="main-value"><?php echo $successful_payments; ?></div>
            </div>
            <div class="rating-card-box">
                <div class="label-text">Pending</div>
                <div class="main-value"><?php echo $pending_status_count; ?></div>
            </div>
            <div class="rating-card-box">
                <div class="label-text">Unpaid</div>
                <div class="main-value"><?php echo $unpaid_payments; ?></div>
            </div>
        </div>
    </div>
</div>

<div class="toast-notification" id="toast-global"></div>

<!-- MODAL: EDIT ADMIN PROFILE -->
<div id="modal-edit-admin-profile" class="modal-overlay">
    <div class="modal-box-edit-profile">
        <h3>Edit Profile</h3>
        <div class="account-settings-box">
            <div class="account-settings-title">Account Settings</div>
            <div class="account-settings-email">Email: <?php echo htmlspecialchars($admin_email !== '' ? $admin_email : 'admin@thehallz.com'); ?></div>
        </div>
        <form id="form-edit-admin-profile" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="action_type" value="update_admin_profile">
            <div class="edit-profile-group">
                <label>Full Name</label>
                <input type="text" id="edit-admin-name" name="edit_admin_name" value="<?php echo htmlspecialchars($admin_name); ?>" required>
            </div>
            <div class="edit-profile-group">
                <label>Phone Number</label>
                <input type="tel" id="edit-admin-phone" name="edit_admin_phone" value="<?php echo htmlspecialchars($admin_phone); ?>">
            </div>
            <div class="edit-profile-actions">
                <button type="submit" class="btn-save-profile">Save Changes</button>
                <button type="button" class="btn-profile-cancel" onclick="closeModal('modal-edit-admin-profile')">Cancel</button>
            </div>
        </form>
    </div>
</div>

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
    const adminNameVal = <?php echo json_encode($admin_name); ?>;
    const adminPhoneVal = <?php echo json_encode($admin_phone); ?>;

    function openEditAdminProfileModal() {
        document.getElementById('edit-admin-name').value = adminNameVal;
        document.getElementById('edit-admin-phone').value = adminPhoneVal;
        document.getElementById('modal-edit-admin-profile').style.display = 'flex';
    }

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

    window.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($msg_toast)): ?>
            triggerToast('<?php echo $msg_toast; ?>');
        <?php endif; ?>
    });
</script>

</body>
</html>