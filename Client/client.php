<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['client_id'])) {
    header("Location: login.php");
    exit();
}

$client_id = $_SESSION['client_id'];

$client_name = "User";
$client_phone = "";
$client_email = "";

$client_query = "SELECT client_name, client_phonenum, client_email FROM client WHERE client_id = '$client_id'";
$client_result = mysqli_query($conn, $client_query);
if ($client_result && mysqli_num_rows($client_result) > 0) {
    $client_row = mysqli_fetch_assoc($client_result);
    $client_name = $client_row['client_name'];
    $client_phone = $client_row['client_phonenum'];
    $client_email = $client_row['client_email'];
}

$booking_submitted = false;
$msg_toast = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action_type']) && $_POST['action_type'] == 'submit_booking') {
    $venue_id = mysqli_real_escape_string($conn, $_POST['book_venue_id']);
    $pkg_query = "SELECT package_id FROM package WHERE venue_id = '$venue_id' LIMIT 1";
    $pkg_res = mysqli_query($conn, $pkg_query);
    $package_id = ($pkg_res && mysqli_num_rows($pkg_res) > 0) ? mysqli_fetch_assoc($pkg_res)['package_id'] : "NULL";

    $event_date = mysqli_real_escape_string($conn, $_POST['book_date']);
    $num_of_guests = mysqli_real_escape_string($conn, $_POST['book_guests']);
    $booking_date = date('Y-m-d');
    $bookingstatus = "Pending";

    $capacity_query = "SELECT venue_capacity, venue_name FROM venue WHERE venue_id = '$venue_id'";
    $capacity_res = mysqli_query($conn, $capacity_query);
    $venue_data = mysqli_fetch_assoc($capacity_res);
    $max_capacity = isset($venue_data['venue_capacity']) ? (int)$venue_data['venue_capacity'] : 0;

    if ($num_of_guests > $max_capacity) {
        $booking_submitted = false;
        $msg_toast = "Error: Total guests exceeded the venue's maximum capacity of " . number_format($max_capacity) . " pax!";
    } 
    else {
        $check_duplicate_query = "SELECT * FROM booking 
                                  WHERE (venue_id = '$venue_id' AND event_date = '$event_date' AND bookingstatus != 'Rejected') 
                                  OR (client_id = '$client_id' AND event_date = '$event_date' AND bookingstatus != 'Rejected')";
        $check_duplicate_res = mysqli_query($conn, $check_duplicate_query);

        if ($check_duplicate_res && mysqli_num_rows($check_duplicate_res) > 0) {
            $booking_submitted = false;
            $msg_toast = "Error: The venue is already booked for this date, or you already have a booking on this date!";
        } else {
            $insert_query = "INSERT INTO booking (client_id, venue_id, package_id, booking_date, event_date, num_of_guests, bookingstatus) 
                             VALUES ('$client_id', '$venue_id', $package_id, '$booking_date', '$event_date', '$num_of_guests', '$bookingstatus')";
            if (mysqli_query($conn, $insert_query)) {
                $booking_submitted = true;
                $msg_toast = "Booking submitted! Waiting for owner approval.";
            } else {
                $msg_toast = "Error: " . mysqli_error($conn);
            }
        }
    }
}

$venues_list = [];
$venue_sql = "SELECT v.*, p.package_name, p.package_price, p.package_id 
              FROM venue v 
              LEFT JOIN package p ON v.venue_id = p.venue_id 
              WHERE v.venue_status = 'approved'
              GROUP BY v.venue_id";
$venue_result = mysqli_query($conn, $venue_sql);
if ($venue_result) {
    while ($row = mysqli_fetch_assoc($venue_result)) {
        $venues_list[] = $row;
    }
}

$packages_details = [];
$pkg_sql = "SELECT * FROM package";
$pkg_result = mysqli_query($conn, $pkg_sql);
if ($pkg_result) {
    while ($row = mysqli_fetch_assoc($pkg_result)) {
        $packages_details[$row['package_id']] = $row;
    }
}

$my_bookings = [];
$booking_sql = "SELECT b.*, v.venue_name, p.package_name 
                FROM booking b
                JOIN venue v ON b.venue_id = v.venue_id
                LEFT JOIN package p ON b.package_id = p.package_id
                WHERE b.client_id = '$client_id'
                ORDER BY b.booking_id DESC";
$booking_result = mysqli_query($conn, $booking_sql);
if ($booking_result) {
    while ($row = mysqli_fetch_assoc($booking_result)) {
        $my_bookings[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - TheHallz</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-burgundy: #7a224f;
            --primary-dark: #581637;
            --accent-pink: #fce4ec;
            --badge-pink: #f8bbd0;
            --badge-text: #880e4f;
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

        /* HEADER DESIGN */
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

        .logo-icon {
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

        .logo-text-title {
            font-family: 'Great Vibes', cursive;
            font-size: 29px;
            line-height: 1;
        }

        .logo-text-sub {
            display: block;
            font-size: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 600;
            margin-top: -2px;
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
        }

        .nav-links a:hover {
            color: var(--primary-burgundy);
            background: var(--accent-pink);
        }

        .nav-links a.active {
            color: var(--white);
            background: var(--primary-burgundy);
            box-shadow: 0 4px 12px rgba(122, 34, 79, 0.25);
        }

        .nav-links a.logout {
            color: var(--primary-burgundy);
        }

        .nav-links a.logout:hover {
            background-color: #fff0f3;
            color: #d32f2f;
        }

        /* BANNER ALUAN */
        .welcome-banner {
            background: linear-gradient(105deg, var(--primary-dark) 0%, var(--primary-burgundy) 55%, #9c2d67 100%);
            border-radius: var(--radius-lg);
            padding: 40px 30px;
            margin-bottom: 30px;
            box-shadow: 0 12px 30px rgba(88, 22, 55, 0.2);
            color: var(--white);
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
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

        .welcome-text {
            position: relative;
            z-index: 2;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .welcome-text h2 {
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            font-weight: 600;
            font-style: normal;
            color: var(--white);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .welcome-text p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
            font-style: normal;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .welcome-text p::before {
            content: '♥';
            font-size: 13px;
            color: var(--badge-pink);
        }

        .content-section {
            width: 100%;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .home-layout {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 25px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .home-layout {
                grid-template-columns: 1fr;
            }
        }

        /* BOX SECTIONS */
        .search-filter-box,
        .map-box {
            background-color: var(--white);
            padding: 28px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-soft);
        }

        .map-box {
            height: 580px;
            padding: 12px;
            position: sticky;
            top: 20px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            background: #e5eff5;
        }

        .map-box iframe {
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 14px;
        }

        .section-title {
            font-size: 19px;
            font-weight: 700;
            color: var(--primary-burgundy);
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-title::before {
            content: '\f0b0';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 10px;
            font-size: 16px;
        }

        .section-title::after {
            content: '🌸';
            font-size: 16px;
            opacity: 0.5;
        }

        .venue-list-wrapper {
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* KAD DEWAN */
        .venue-row-card {
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 22px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
            position: relative;
        }

        .venue-row-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(122, 34, 79, 0.08);
            border-color: var(--badge-pink);
        }

        .venue-card-content {
            display: flex;
            gap: 18px;
            align-items: flex-start;
        }

        .venue-thumb-img {
            width: 130px;
            height: 100px;
            border-radius: 10px;
            object-fit: cover;
            background: #eee;
            flex-shrink: 0;
        }

        .v-details {
            flex-grow: 1;
        }

        .v-details h4 {
            font-size: 18px;
            color: var(--text-dark);
            margin-bottom: 6px;
            font-weight: 700;
        }

        .v-details p {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .v-price-tag {
            display: inline-block;
            background: var(--accent-pink);
            color: var(--primary-burgundy);
            font-weight: 700;
            font-size: 13.5px;
            padding: 6px 14px;
            border-radius: 20px;
            margin-top: 8px;
            margin-bottom: 10px;
        }

        .card-actions-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 5px;
            padding-top: 12px;
            border-top: 1px dashed var(--border-color);
        }

        .links-group {
            display: flex;
            gap: 15px;
        }

        /* FORM ELEMENTS (DISESUAIKAN KOTAK CARIAN PANJANG) */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--primary-burgundy);
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-burgundy);
            font-size: 14px;
            opacity: 0.7;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px 12px 38px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-page);
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            color: var(--text-dark);
            transition: all 0.25s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            background-color: var(--white);
            border-color: var(--primary-burgundy);
            box-shadow: 0 0 0 3.5px rgba(122, 34, 79, 0.12);
        }

        /* BUTTONS */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-burgundy) 0%, var(--primary-dark) 100%);
            color: var(--white);
            border: none;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(122, 34, 79, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #400d27 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(122, 34, 79, 0.3);
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
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background-color: var(--bg-page);
            color: var(--text-dark);
        }

        .btn-close-purple {
            background: var(--primary-burgundy);
            color: var(--white);
            border: none;
            padding: 10px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 30px;
            cursor: pointer;
            display: block;
            margin: 20px auto 0 auto;
            transition: background 0.2s;
        }

        .btn-close-purple:hover {
            background: var(--primary-dark);
        }

        .view-pic-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--primary-burgundy);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.2s;
        }

        .view-pic-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* MODALS DESIGN */
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

        .my-booking-details-box {
            background-color: var(--bg-page);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 20px;
            margin-top: 15px;
        }

        .booking-item-row {
            margin-bottom: 12px;
            font-size: 14.5px;
            color: var(--text-dark);
        }

        @keyframes modalSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-box-booking-new {
            background-color: var(--white);
            width: 90%;
            max-width: 480px;
            border-radius: var(--radius-lg);
            padding: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            animation: modalSlideUp 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .modal-main-title {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 600;
            color: var(--primary-burgundy);
            margin-bottom: 18px;
            text-align: left;
        }

        .modal-venue-info {
            background: var(--accent-pink);
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            text-align: left;
            border-left: 4px solid var(--primary-burgundy);
        }

        .modal-venue-info h4 {
            font-family: 'Playfair Display', serif;
            font-size: 17px;
            color: var(--primary-dark);
            font-weight: 700;
            margin-bottom: 3px;
        }

        .modal-venue-info p {
            font-size: 13px;
            color: var(--text-muted);
        }

        .form-group-new {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group-new label {
            display: block;
            font-size: 13px;
            color: var(--text-dark);
            margin-bottom: 6px;
            font-weight: 600;
        }

        .form-group-new input,
        .form-group-new select,
        .form-group-new textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            background-color: var(--bg-page);
            color: var(--text-dark);
            transition: border-color 0.2s;
        }

        .form-group-new input:focus,
        .form-group-new select:focus,
        .form-group-new textarea:focus {
            outline: none;
            background-color: var(--white);
            border-color: var(--primary-burgundy);
        }

        .form-actions-new {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-action {
            flex: 1;
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 30px;
            cursor: pointer;
            text-align: center;
            border: none;
            transition: background 0.2s;
        }

        .btn-book-now {
            background: var(--primary-burgundy);
            color: var(--white);
        }

        .btn-book-now:hover {
            background: var(--primary-dark);
        }

        .btn-cancel-new {
            background-color: #e2e8f0;
            color: var(--text-dark);
        }

        .btn-cancel-new:hover {
            background-color: #cbd5e0;
        }

        /* LOGOUT MODAL */
        .logout-box-container {
            background-color: var(--white);
            width: 90%;
            max-width: 420px;
            border-radius: var(--radius-lg);
            padding: 35px 30px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
            animation: modalSlideUp 0.25s ease-out;
        }

        .logout-title {
            font-size: 15px;
            color: var(--text-dark);
            margin-bottom: 25px;
            line-height: 1.5;
            font-weight: 500;
        }

        .logout-buttons-group {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-logout-cancel {
            background-color: var(--white);
            color: var(--text-muted);
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 30px;
            border: 1px solid var(--border-color);
            cursor: pointer;
        }

        .btn-logout-confirm {
            background-color: #e53e3e;
            color: var(--white);
            border: none;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 30px;
            cursor: pointer;
        }

        /* TOAST NOTIFICATION */
        .toast-notification {
            position: fixed;
            top: 30px;
            right: 30px;
            background-color: var(--white);
            color: var(--text-dark);
            padding: 16px 24px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 600;
            z-index: 1010;
            box-shadow: 0 12px 35px rgba(122, 34, 79, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
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

        .toast-success-icon {
            color: #10b981;
        }

        .hidden-table-holder {
            display: none;
        }

        /* GALLERY MODAL */
        .gallery-modal-box {
            position: relative;
            max-width: 650px;
            width: 90%;
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: modalSlideUp 0.3s ease-out;
        }

        .gallery-img-container {
            width: 100%;
            height: 380px;
            background-color: #f7fafc;
            border-radius: var(--radius-md);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .gallery-img-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(122, 34, 79, 0.75);
            color: var(--white);
            border: none;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
            z-index: 10;
        }

        .nav-arrow:hover {
            background-color: var(--primary-burgundy);
        }

        .arrow-left { left: 15px; }
        .arrow-right { right: 15px; }

        .gallery-title {
            font-size: 17px;
            font-weight: 600;
            color: var(--text-dark);
            margin-top: 15px;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <div class="logo-area">
                <div class="logo-icon">🌸</div>
                <div>
                    <span class="logo-text-title">TheHallz</span>
                </div>
            </div>
            <div class="nav-links">
                <a id="nav-home" class="active" onclick="switchTab('home')"><i class="fa-solid fa-house"></i> Home</a>
                <a id="nav-bookings" onclick="openMyBookingsModal()"><i class="fa-regular fa-calendar-check"></i> My Bookings</a>
                <a class="logout" onclick="openLogoutModal()"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</a>
            </div>
        </div>

        <!-- WELCOME BANNER -->
        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Welcome, <?php echo htmlspecialchars($client_name); ?>!</h2>
                <p>Find your perfect wedding venues!</p>
            </div>
        </div>

        <!-- MAIN CONTENT SECTION -->
        <div class="content-section">
            <div class="home-layout">

                <!-- KANAN: BORANG TAPISAN (BUDGET DIBUANG & KOTAK LOCATION DIPANJANGKAN) -->
                <div class="search-filter-box">
                    <div class="section-title">Filter Options</div>

                    <form id="filter-form" onsubmit="event.preventDefault(); searchVenueMap();">
                        <div class="form-group">
                            <label>Location / Area</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-location-dot"></i>
                                <input type="text" id="filter-area" placeholder="e.g., Telok Mas, Alor Gajah, Melaka">
                            </div>
                        </div>

                        <button type="button" class="btn-primary" style="width: 100%; margin-top: 5px;" onclick="searchVenueMap()">
                            <i class="fa-solid fa-magnifying-glass"></i> Search & Update Map
                        </button>
                    </form>

                    <div class="venue-list-wrapper" id="venue-list-container">
                        <?php if (count($venues_list) > 0): ?>
                            <?php foreach ($venues_list as $venue): ?>
                                <div class="venue-row-card" data-area="<?php echo htmlspecialchars(strtolower($venue['venue_location'])); ?>" data-capacity="<?php echo $venue['venue_capacity']; ?>">
                                    <div class="venue-card-content">
                                        <?php 
                                            $img_src = !empty($venue['venue_image']) ? 'images/' . $venue['venue_image'] : 'images/weddingVenue.jpg';
                                        ?>
                                        <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Venue Image" class="venue-thumb-img" onerror="this.src='https://via.placeholder.com/130x100?text=Venue';">
                                        
                                        <div class="v-details">
                                            <h4><?php echo htmlspecialchars($venue['venue_name']); ?></h4>
                                            <p><i class="fa-solid fa-location-dot" style="color:var(--primary-burgundy);"></i> Full Address: <?php echo htmlspecialchars($venue['venue_location']); ?></p>
                                            <p><i class="fa-solid fa-users" style="color:var(--primary-burgundy);"></i> Capacity: <?php echo htmlspecialchars(number_format($venue['venue_capacity'])); ?> pax</p>

                                            <div class="v-price-tag">
                                                <?php
                                                if (!empty($venue['package_name'])) {
                                                    echo htmlspecialchars($venue['package_name']) . " (RM " . number_format($venue['package_price'], 2) . ")";
                                                } else {
                                                    echo "Price: RM " . number_format($venue['venue_price'], 2);
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-actions-row">
                                        <div class="links-group">
                                            <a class="view-pic-link" onclick="openGallery('<?php echo htmlspecialchars(addslashes($venue['venue_name'])); ?>', '<?php echo htmlspecialchars(addslashes($venue['venue_image'])); ?>')">
                                                <i class="fa-solid fa-arrow-right"></i> view picture
                                            </a>
                                            <?php if (!empty($venue['package_id'])): ?>
                                                <a class="view-pic-link" onclick="previewPackageDetails('<?php echo $venue['package_id']; ?>')">
                                                    <i class="fa-solid fa-arrow-right"></i> view package
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn-primary" onclick="openBookingModal('<?php echo htmlspecialchars(addslashes($venue['venue_name'])); ?>', '<?php echo $venue['venue_id']; ?>', '<?php echo htmlspecialchars(addslashes($venue['venue_location'])); ?>', '<?php echo $venue['venue_capacity']; ?>')">
                                            Book Venue
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; color: var(--text-muted); padding: 30px;">No venues available.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KIRI: PETA GOOGLE -->
                <div class="map-box">
                    <iframe
                        id="map-frame"
                        src="https://maps.google.com/maps?q=Malaysia&t=&z=6&ie=UTF8&iwloc=&output=embed"
                        allowfullscreen=""
                        loading="lazy">
                    </iframe>
                </div>

            </div>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div class="toast-notification" id="toast-global">
        <div class="toast-success-icon">
            <i class="fa-solid fa-circle-check fa-lg"></i>
        </div>
        <span>Booking confirmed!</span>
    </div>

    <!-- MODAL: BOOKING FORM -->
    <div id="modal-customer-booking" class="modal-overlay">
        <div class="modal-box-booking-new">
            <h3 class="modal-main-title">Book Venue</h3>

            <div class="modal-venue-info">
                <h4 id="display-v-name">Dewan Serbaguna Telok Mas</h4>
                <p id="display-v-area">Telok Mas, Melaka</p>
                <p style="color: var(--primary-burgundy); font-weight: 700; font-size: 13px; margin-top:4px;" id="display-v-capacity"></p>
            </div>

            <form id="form-submit-booking" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" onsubmit="return validateGuestCapacity();">
                <input type="hidden" name="action_type" value="submit_booking">
                <input type="hidden" id="book-v-name">
                <input type="hidden" id="book-venue-id" name="book_venue_id">
                <input type="hidden" id="book-max-capacity">

                <div class="form-group-new">
                    <label>Full Name</label>
                    <input type="text" id="book-fullname" value="<?php echo htmlspecialchars($client_name); ?>" readonly style="background-color: #edf2f7; cursor: not-allowed;" required>
                </div>

                <div class="form-group-new">
                    <label>Phone Number</label>
                    <input type="tel" id="book-phone" value="<?php echo htmlspecialchars($client_phone); ?>" readonly style="background-color: #edf2f7; cursor: not-allowed;" required>
                </div>

                <div class="form-group-new">
                    <label>Email Address</label>
                    <input type="email" id="book-email" value="<?php echo htmlspecialchars($client_email); ?>" readonly style="background-color: #edf2f7; cursor: not-allowed;" required>
                </div>

                <div class="form-group-new">
                    <label>Event Date</label>
                    <input type="date" id="book-date" name="book_date" required>
                </div>

                <div class="form-group-new">
                    <label>Number of Guest (est.)</label>
                    <input type="number" id="book-guests" name="book_guests" placeholder="Estimated guests" required>
                </div>

                <div class="form-actions-new">
                    <button type="submit" class="btn-action btn-book-now">Booking Now</button>
                    <button type="button" class="btn-action btn-cancel-new" onclick="closeModal('modal-customer-booking')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: MY BOOKINGS -->
    <div id="modal-my-bookings" class="modal-overlay">
        <div class="modal-box-booking modal-fade-in" style="max-width: 460px; background-color: var(--white); border-radius: var(--radius-lg); padding: 32px; box-shadow: 0 20px 30px rgba(0, 0, 0, 0.15);">
            <h3 id="modal-booking-title" style="text-align: center; font-size: 22px; font-family: 'Playfair Display', serif; margin-bottom: 20px; color: var(--primary-burgundy);">My Booking</h3>

            <div class="my-booking-details-box">
                <div id="dynamic-booking-details"></div>
            </div>

            <button type="button" class="btn-close-purple" onclick="closeModal('modal-my-bookings')">Close</button>
        </div>
    </div>

    <!-- MODAL: LOGOUT CONFIRMATION -->
    <div id="modal-logout-confirm" class="modal-overlay">
        <div class="logout-box-container">
            <div class="logout-title">
                Are you sure you want to log out? You will need to login again to access the panel.
            </div>
            <div class="logout-buttons-group">
                <button class="btn-logout-cancel" onclick="closeModal('modal-logout-confirm')">Cancel</button>
                <button class="btn-logout-confirm" onclick="executeLogout()">Log Out</button>
            </div>
        </div>
    </div>

    <!-- MODAL: GALLERY -->
    <div id="modal-image-gallery" class="modal-overlay">
        <div class="gallery-modal-box">
            <div class="gallery-img-container">
                <button class="nav-arrow arrow-left" onclick="changeImage(-1)">❮</button>
                <img id="gallery-current-img" src="" alt="Hall Image">
                <button class="nav-arrow arrow-right" onclick="changeImage(1)">❯</button>
            </div>
            <div id="gallery-display-title" class="gallery-title">Nama Dewan</div>
            <button type="button" class="btn-close-purple" onclick="closeModal('modal-image-gallery')">Close</button>
        </div>
    </div>

    <!-- TABLE SEMBUNYI UNTUK JS DATA MY BOOKINGS -->
    <div class="hidden-table-holder">
        <table id="table-user-bookings">
            <tbody>
                <?php foreach ($my_bookings as $b_row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($b_row['venue_name']); ?></td>
                        <td><?php echo htmlspecialchars($b_row['package_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($b_row['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($b_row['num_of_guests']); ?></td>
                        <td><?php echo htmlspecialchars($b_row['bookingstatus']); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($b_row['booking_date']))); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- JAVASCRIPT ASAL -->
    <script>
        const dbPackages = <?php echo json_encode($packages_details); ?>;

        function switchTab(tabType) {
            const homeNav = document.getElementById('nav-home');
            const bookingsNav = document.getElementById('nav-bookings');
            if (tabType === 'home') {
                homeNav.classList.add('active');
                bookingsNav.classList.remove('active');
            } else if (tabType === 'bookings') {
                bookingsNav.classList.add('active');
                homeNav.classList.remove('active');
            }
        }

        function searchVenueMap() {
            const searchInput = document.getElementById('filter-area').value.trim().toLowerCase();
            const mapFrame = document.getElementById('map-frame');

            if (searchInput === '') {
                alert('Please enter an area or venue name first!');
                return;
            }

            const formattedQuery = encodeURIComponent(searchInput);
            mapFrame.src = `https://maps.google.com/maps?q=${formattedQuery}%2C%20Malaysia&t=&z=14&ie=UTF8&iwloc=&output=embed`;

            const venueCards = document.querySelectorAll('.venue-row-card');
            let foundCount = 0;

            venueCards.forEach(card => {
                const venueArea = card.getAttribute('data-area').toLowerCase();
                const venueName = card.querySelector('h4').textContent.toLowerCase();

                if (venueArea.includes(searchInput) || venueName.includes(searchInput)) {
                    card.style.display = 'flex';
                    foundCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            if (foundCount === 0) {
                triggerToast(`Map updated, but no venues found matching: ${document.getElementById('filter-area').value}`);
            } else {
                triggerToast(`Map updated & filtered: ${foundCount} venue(s) found!`);
            }
        }

        function openBookingModal(venueName, venueId, venueLocation, venueCapacity) {
            document.getElementById('modal-customer-booking').style.display = 'flex';
            document.getElementById('display-v-name').textContent = venueName;
            document.getElementById('book-v-name').value = venueName;
            document.getElementById('book-venue-id').value = venueId;
            document.getElementById('display-v-area').textContent = venueLocation;

            document.getElementById('book-max-capacity').value = venueCapacity;
            document.getElementById('display-v-capacity').textContent = `Maximum Capacity: ${parseInt(venueCapacity).toLocaleString()} pax`;

            document.getElementById('book-guests').setAttribute('max', venueCapacity);
        }

        function validateGuestCapacity() {
            const guestsInput = document.getElementById('book-guests');
            const maxCapacityInput = document.getElementById('book-max-capacity');

            const guests = parseInt(guestsInput.value);
            const maxCapacity = parseInt(maxCapacityInput.value);

            if (guests > maxCapacity) {
                alert(`Booking rejected! The maximum capacity allowed for this venue is ${maxCapacity.toLocaleString()} pax.`);
                guestsInput.focus();
                return false;
            }
            return true;
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            if (modalId === 'modal-my-bookings') {
                switchTab('home');
            }
        }

        function submitCustomerBooking() {
            const date = document.getElementById('book-date').value;
            const guests = document.getElementById('book-guests').value;

            if (date === '' || guests === '') {
                alert('Please fill in all required information!');
                return;
            }

            if (validateGuestCapacity()) {
                document.getElementById('form-submit-booking').submit();
            }
        }

        function openMyBookingsModal() {
            switchTab('bookings');
            document.getElementById('modal-booking-title').textContent = "My Booking";
            const rows = document.querySelectorAll('#table-user-bookings tbody tr');
            const targetDetailsContainer = document.getElementById('dynamic-booking-details');
            if (rows.length === 0) {
                targetDetailsContainer.innerHTML = `
                <div class="booking-item-row" style="text-align: center; color: #a0aec0; margin-top: 10px;">
                    <em>No upcoming bookings found.</em>
                </div>
            `;
            } else {
                const latestRow = rows[rows.length - 1];
                const cells = latestRow.querySelectorAll('td');

                const statusText = cells[4].textContent.trim();
                let statusColor = '#ed8936';
                const st = statusText.toLowerCase();
                if (st === 'pending') {
                    statusColor = '#ed8936';
                } else if (st === 'completed') {
                    statusColor = '#3182ce';
                } else if (st === 'confirmed') {
                    statusColor = '#38a169';
                } else if (st === 'rejected' || st === 'cancelled') {
                    statusColor = '#e53e3e';
                }

                targetDetailsContainer.innerHTML = `
                <div class="booking-item-row"><strong>Event Date :</strong> ${cells[2].textContent}</div>
                <div class="booking-item-row"><strong>Package :</strong> ${cells[1].textContent}</div>
                <div class="booking-item-row"><strong>Guest :</strong> ${cells[3].textContent.replace(' pax', '')}</div>
                <div class="booking-item-row"><strong>Status :</strong> <span style="color: ${statusColor}; font-weight: bold;">${statusText}</span></div>
                <div class="booking-item-row"><strong>Booked On :</strong> ${cells[5].textContent}</div>
            `;
            }

            document.getElementById('modal-my-bookings').style.display = 'flex';
        }

        function previewPackageDetails(packageId) {
            switchTab('bookings');
            document.getElementById('modal-booking-title').textContent = "Package";
            const targetDetailsContainer = document.getElementById('dynamic-booking-details');

            const pkg = dbPackages[packageId];

            if (pkg) {
                let inclusionsHtml = '';
                if (pkg.package_inclusions) {
                    const inclusionsArray = pkg.package_inclusions.split(/[,\n]+/);
                    inclusionsArray.forEach(item => {
                        if (item.trim() !== "") {
                            inclusionsHtml += `<div class="booking-item-row" style="padding-left: 15px; margin-bottom: 2px;">- ${item.trim()}</div>`;
                        }
                    });
                }

                targetDetailsContainer.innerHTML = `
                <div class="booking-item-row"><strong>Package Name:</strong> ${pkg.package_name}</div>
                <div class="booking-item-row"><strong>Price:</strong> RM ${parseFloat(pkg.package_price).toLocaleString(undefined, {minimumFractionDigits: 2})}</div>
                <div class="booking-item-row" style="margin-top: 10px;"><strong>Package Inclusions:</strong></div>
                ${inclusionsHtml}
            `;
            } else {
                targetDetailsContainer.innerHTML = `<div class="booking-item-row">No package details found.</div>`;
            }

            document.getElementById('modal-my-bookings').style.display = 'flex';
        }

        function openLogoutModal() {
            document.getElementById('modal-logout-confirm').style.display = 'flex';
        }

        function executeLogout() {
            window.location.href = 'index.php';
        }

        function triggerToast(message) {
            const toast = document.getElementById('toast-global');
            if (toast) {
                toast.querySelector('span').textContent = message;
                toast.classList.add('show-toast');
                setTimeout(() => {
                    toast.classList.remove('show-toast');
                }, 3000);
            }
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
                if (event.target.id === 'modal-my-bookings') {
                    switchTab('home');
                }
            }
        }

        const venueImages = {
            "Dewan Serbaguna Telok Mas": [
                "images/weddingVenue.jpg",
                "images/pelamin.jpg",
            ],
            "Dahlia Wedding Hall": [
                "images/weddingVenue1.jpg",
                "images/pelamin1.jpg",
            ]
        };
        let currentVenue = "";
        let currentImgIndex = 0;
        let dynamicImgList = [];
        function openGallery(venueName, databaseImage) {
            currentVenue = venueName;
            currentImgIndex = 0;
            dynamicImgList = [];

            const pathFolder = "images/";
            if (databaseImage && databaseImage.trim() !== "") {
                dynamicImgList.push(pathFolder + databaseImage);
            } else if (venueImages[venueName]) {
                dynamicImgList = venueImages[venueName];
            }

            document.getElementById('modal-image-gallery').style.display = 'flex';
            updateGalleryContent();
        }

        function updateGalleryContent() {
            const imgElement = document.getElementById('gallery-current-img');
            const titleElement = document.getElementById('gallery-display-title');

            if (dynamicImgList && dynamicImgList.length > 0) {
                imgElement.src = dynamicImgList[currentImgIndex];
                titleElement.textContent = `${currentVenue} (Picture ${currentImgIndex + 1} of ${dynamicImgList.length})`;
            } else {
                imgElement.src = "https://via.placeholder.com/600x380?text=No+Image+Available";
                titleElement.textContent = currentVenue;
            }
        }

        function changeImage(direction) {
            if (!dynamicImgList || dynamicImgList.length <= 1) return;
            currentImgIndex += direction;
            if (currentImgIndex >= dynamicImgList.length) {
                currentImgIndex = 0;
            } else if (currentImgIndex < 0) {
                currentImgIndex = dynamicImgList.length - 1;
            }

            updateGalleryContent();
        }

        window.addEventListener('DOMContentLoaded', () => {
            const dateInput = document.getElementById('book-date');
            if (dateInput) {
                const targetDate = new Date();
                targetDate.setMonth(targetDate.getMonth() + 3);

                const yyyy = targetDate.getFullYear();
                const mm = String(targetDate.getMonth() + 1).padStart(2, '0');
                const dd = String(targetDate.getDate()).padStart(2, '0');

                const minDate = `${yyyy}-${mm}-${dd}`;
                dateInput.setAttribute('min', minDate);
            }

            <?php if ($booking_submitted || !empty($msg_toast)): ?>
                triggerToast('<?php echo $msg_toast; ?>');
            <?php endif; ?>
        });
    </script>
</body>
</html>