<?php
session_start();

include('db_connection.php');

if (!isset($_SESSION['owner_id'])) {
    $_SESSION['owner_id'] = 1;
}
$owner_id = $_SESSION['owner_id'];

$owner_name = "Venue Owner";
$owner_query = "SELECT owner_name FROM venue_owner WHERE owner_id = $owner_id LIMIT 1";
$owner_result = mysqli_query($conn, $owner_query);
if ($owner_result && mysqli_num_rows($owner_result) > 0) {
    $owner_row = mysqli_fetch_assoc($owner_result);
    $owner_name = $owner_row['owner_name'];
}

$toast_message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_venue') {
    $v_name = mysqli_real_escape_string($conn, $_POST['v_name']);
    $v_address = mysqli_real_escape_string($conn, $_POST['v_address']);
    $v_capacity = intval($_POST['v_capacity']);

    $venue_image_filename = 'weddingVenue.jpg';
    if (isset($_FILES['v_images']) && $_FILES['v_images']['error'][0] == 0) {
        $venue_image_filename = time() . '_' . basename($_FILES['v_images']['name'][0]);
        $target_dir = "images/";
        move_uploaded_file($_FILES['v_images']['tmp_name'][0], $target_dir . $venue_image_filename);
    }

    $insert_venue = "INSERT INTO venue (venue_name, venue_location, venue_capacity, venue_price, owner_id, venue_image) 
                     VALUES ('$v_name', '$v_address', $v_capacity, 0.00, $owner_id, '$venue_image_filename')";
    if (mysqli_query($conn, $insert_venue)) {
        $toast_message = "Venue Added Successfully!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_venue') {
    $edit_v_id = intval($_POST['edit_venue_id']);
    $edit_v_owner = mysqli_real_escape_string($conn, $_POST['edit_v_owner']); 
    $edit_v_name = mysqli_real_escape_string($conn, $_POST['edit_v_name']);
    $edit_v_address = mysqli_real_escape_string($conn, $_POST['edit_v_address']);
    $edit_v_capacity = intval($_POST['edit_v_capacity']);

    $current_venue_res = mysqli_query($conn, "SELECT venue_image FROM venue WHERE venue_id = $edit_v_id");
    $current_venue = mysqli_fetch_assoc($current_venue_res);
    $venue_image_filename = $current_venue['venue_image'];

    if (isset($_FILES['edit_v_images']) && $_FILES['edit_v_images']['error'][0] == 0) {
        $venue_image_filename = time() . '_' . basename($_FILES['edit_v_images']['name'][0]);
        $target_dir = "images/";
        move_uploaded_file($_FILES['edit_v_images']['tmp_name'][0], $target_dir . $venue_image_filename);
    }

    $update_owner = "UPDATE venue_owner SET owner_name = '$edit_v_owner' WHERE owner_id = $owner_id";
    mysqli_query($conn, $update_owner);

    $update_venue = "UPDATE venue SET venue_name = '$edit_v_name', venue_location = '$edit_v_address', venue_capacity = $edit_v_capacity, venue_image = '$venue_image_filename' WHERE venue_id = $edit_v_id AND owner_id = $owner_id";
    if (mysqli_query($conn, $update_venue)) {
        $toast_message = "Venue Updated Successfully!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_package') {
    $p_venue_name = mysqli_real_escape_string($conn, $_POST['p_venue']);
    $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
    $p_price = floatval($_POST['p_price']);
    $p_inclusions = mysqli_real_escape_string($conn, $_POST['p_inclusions']);

    $find_venue = "SELECT venue_id FROM venue WHERE venue_name = '$p_venue_name' AND owner_id = $owner_id LIMIT 1";
    $venue_res = mysqli_query($conn, $find_venue);
    if (mysqli_num_rows($venue_res) > 0) {
        $v_row = mysqli_fetch_assoc($venue_res);
        $v_id = $v_row['venue_id'];

        $insert_package = "INSERT INTO package (package_name, package_price, package_inclusions, venue_id) 
                           VALUES ('$p_name', $p_price, '$p_inclusions', $v_id)";
        if (mysqli_query($conn, $insert_package)) {
            $toast_message = "Package Added Successfully!";
        }
    } else {
        echo "<script>alert('Error: Venue name not found under your account. Please create the venue first.');</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_package') {
    $edit_p_id = intval($_POST['edit_package_id']);
    $edit_p_name = mysqli_real_escape_string($conn, $_POST['edit_p_name']);
    $edit_p_price = floatval($_POST['edit_p_price']);
    $edit_p_inclusions = mysqli_real_escape_string($conn, $_POST['edit_p_inclusions']);

    $update_package = "UPDATE package SET package_name = '$edit_p_name', package_price = $edit_p_price, package_inclusions = '$edit_p_inclusions' 
                       WHERE package_id = $edit_p_id";
    if (mysqli_query($conn, $update_package)) {
        $toast_message = "Package Updated Successfully!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_booking_status') {
    $b_id = intval($_POST['booking_id']);
    $b_status = mysqli_real_escape_string($conn, $_POST['status_value']);

    $update_status = "UPDATE booking SET bookingstatus = '$b_status' WHERE booking_id = $b_id";
    mysqli_query($conn, $update_status);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venue Owner Panel</title>
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

        .stats-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin: 0 auto 30px auto;
            width: 100%;
            max-width: 1300px;
        }

        .stat-card {
            background-color: var(--white);
            color: var(--text-dark);
            padding: 24px;
            border-radius: var(--radius-md);
            text-align: left;
            flex: 1;
            font-weight: 600;
            font-size: 15px;
            box-shadow: var(--shadow-soft);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: var(--primary-burgundy);
        }

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
            display: none;
            box-shadow: var(--shadow-soft);
            border: 1px solid var(--border-color);
        }

        .content-section.active-section {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .btn-add {
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

        .btn-add:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #400d27 100%);
            transform: translateY(-1px);
        }

        .btn-edit {
            background-color: var(--bg-page);
            color: var(--primary-burgundy);
            border: 1px solid var(--border-color);
            padding: 7px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            margin-right: 5px;
            transition: all 0.2s ease;
        }

        .btn-edit:hover {
            background-color: var(--accent-pink);
            color: var(--primary-dark);
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

        th,
        td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border-color);
        }

        .inclusions-cell {
            white-space: pre-line;
            line-height: 1.6;
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

        .select-status {
            padding: 8px 12px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 13.5px;
            border: 1.5px solid #e2e8f0;
            background-color: var(--white);
            cursor: pointer;
            outline: none;
            transition: all 0.2s;
        }

        .select-status:focus {
            border-color: var(--primary-burgundy);
            box-shadow: 0 0 0 3.5px rgba(122, 34, 79, 0.12);
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

        .modal-box-add,
        .modal-box-delete,
        .modal-box-logout {
            background-color: var(--white);
            width: 90%;
            border-radius: var(--radius-lg);
            padding: 35px;
            color: var(--text-dark);
            border: 1px solid var(--border-color);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-box-add {
            max-width: 580px;
        }

        .modal-box-delete, .modal-box-logout {
            max-width: 420px;
            text-align: center;
        }

        .modal-box-logout p, .modal-box-delete p {
            font-size: 15px;
            color: var(--text-dark);
            line-height: 1.5;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .modal-box-add h3 {
            text-align: left;
            font-size: 24px;
            margin-bottom: 22px;
            font-weight: 600;
            color: var(--primary-burgundy);
            font-family: 'Playfair Display', serif;
        }

        .form-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-dark);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            background-color: var(--bg-page);
            color: var(--text-dark);
            transition: all 0.2s ease;
        }

        .form-group input[type="file"] {
            padding: 8px 12px;
            cursor: pointer;
            background-color: var(--white);
        }

        .form-group input[type="file"]::file-selector-button {
            background-color: var(--primary-burgundy);
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            margin-right: 10px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .form-group input[type="file"]::file-selector-button:hover {
            background-color: var(--primary-dark);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            background-color: var(--white);
            border-color: var(--primary-burgundy);
            box-shadow: 0 0 0 3.5px rgba(122, 34, 79, 0.12);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
        }

        .btn-submit {
            background: var(--primary-burgundy);
            color: white;
            border: none;
            padding: 11px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 30px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
        }

        .btn-cancel, .btn-logout-confirm, .btn-confirm-ok {
            padding: 11px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-cancel {
            background-color: var(--white);
            color: var(--text-muted);
            border: 1px solid var(--border-color);
        }

        .btn-cancel:hover {
            background-color: var(--bg-page);
            color: var(--text-dark);
        }

        .btn-confirm-ok, .btn-logout-confirm {
            background-color: #e53e3e;
            color: var(--white);
            border: none;
        }

        .btn-confirm-ok:hover, .btn-logout-confirm:hover {
            background-color: #c53030;
        }

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

        .modal-box-view-image {
            background-color: var(--white);
            width: 90%;
            max-width: 650px;
            border-radius: var(--radius-lg);
            padding: 30px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.3s ease-out;
            position: relative;
            border: 1px solid var(--border-color);
        }

        .modal-box-view-image img {
            max-width: 100%;
            max-height: 55vh;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            object-fit: contain;
            margin-bottom: 15px;
            background: #f7fafc;
        }

        .image-nav-btn {
            position: absolute;
            top: 48%;
            transform: translateY(-50%);
            background: var(--primary-burgundy);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            cursor: pointer;
            border-radius: 50%;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .image-nav-btn:hover {
            background: var(--primary-dark);
        }

        .prev-btn { left: 15px; }
        .next-btn { right: 15px; }

        .btn-close-image {
            background-color: var(--primary-burgundy);
            color: var(--white);
            border: none;
            padding: 11px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 30px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-close-image:hover {
            background-color: var(--primary-dark);
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <div class="logo-area">
                <div class="logo-icon">🌸</div>
                <div>
                    <span class="logo-text-title">TheHallz - Owner Panel</span>
                </div>
            </div>
            <div class="nav-links">
                <a onclick="switchTab('venues')" id="tab-venues" class="active"><i class="fa-solid fa-building"></i> Venues</a>
                <a onclick="switchTab('packages')" id="tab-packages"><i class="fa-solid fa-box-archive"></i> Packages</a>
                <a onclick="switchTab('bookings')" id="tab-bookings"><i class="fa-regular fa-calendar-check"></i> Bookings</a>
                <a class="logout" onclick="openModal('modal-logout-confirmation')"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</a>
            </div>
        </div>

        <div class="welcome-banner">
            <h2>Welcome, <?php echo htmlspecialchars($owner_name); ?>! 🏢</h2>
            <p>Manage your venues, packages, and view booking requests seamlessly.</p>
        </div>

        <div class="stats-container">
            <div class="stat-card" id="count-pending">0 Pending Bookings</div>
            <div class="stat-card" id="count-confirmed">0 Confirmed Bookings</div>
            <div class="stat-card" id="count-completed">0 Completed Bookings</div>
            <div class="stat-card" id="count-cancelled">0 Cancelled Bookings</div>
        </div>

        <div id="section-venues" class="content-section active-section">
            <div class="section-header">
                <h3>Venues</h3>
                <button class="btn-add" onclick="openModal('modal-add-venue')"><i class="fa-solid fa-plus"></i> Add New Venue</button>
            </div>
            <table id="table-venues">
                <thead>
                    <tr>
                        <th>Owner Name</th>
                        <th>Venue Name</th>
                        <th>Full Address</th>
                        <th>Capacity</th>
                        <th>Venue Images</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $venue_query = "SELECT v.*, vo.owner_name FROM venue v 
                                    JOIN venue_owner vo ON v.owner_id = vo.owner_id 
                                    WHERE v.owner_id = $owner_id";
                    $venue_result = mysqli_query($conn, $venue_query);
                    if (mysqli_num_rows($venue_result) > 0) {
                        while ($v_row = mysqli_fetch_assoc($venue_result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($v_row['owner_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($v_row['venue_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($v_row['venue_location']) . "</td>";
                            echo "<td>" . number_format($v_row['venue_capacity']) . "</td>";
                            
                            $img_path = "images/" . $v_row['venue_image'];
                            echo "<td><button class='btn-edit' onclick=\"openGalleryModal(['$img_path'], '" . htmlspecialchars($v_row['venue_name']) . "')\">View</button></td>";
                            
                            $v_status = isset($v_row['venue_status']) ? $v_row['venue_status'] : 'Pending'; 
                            $status_color = '#d69e2e';
                            if (strcasecmp($v_status, 'Approved') == 0) {
                                $status_color = '#38a169';
                            } elseif (strcasecmp($v_status, 'Rejected') == 0) {
                                $status_color = '#e53e3e';
                            }
                            echo "<td style='color: $status_color; font-weight: 700;'>" . htmlspecialchars($v_status) . "</td>";

                            echo "<td>
                                <button class='btn-edit' data-venue-id='" . $v_row['venue_id'] . "' onclick=\"openModal('modal-edit-venue', 'venue', this)\">Edit</button>
                              </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center;'>No venues registered yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="section-packages" class="content-section">
            <div class="section-header">
                <h3>Packages</h3>
                <button class="btn-add" onclick="openModal('modal-add-package')"><i class="fa-solid fa-plus"></i> Add New Package</button>
            </div>
            <table id="table-packages">
                <thead>
                    <tr>
                        <th>Venue Name</th>
                        <th>Package Name</th>
                        <th>Price (RM)</th>
                        <th>Package Inclusions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $package_query = "SELECT p.*, v.venue_name FROM package p 
                                      JOIN venue v ON p.venue_id = v.venue_id 
                                      WHERE v.owner_id = $owner_id";
                    $package_result = mysqli_query($conn, $package_query);
                    if (mysqli_num_rows($package_result) > 0) {
                        while ($p_row = mysqli_fetch_assoc($package_result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($p_row['venue_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($p_row['package_name']) . "</td>";
                            echo "<td>" . number_format($p_row['package_price'], 2) . "</td>";
                            echo "<td class='inclusions-cell'>" . htmlspecialchars($p_row['package_inclusions']) . "</td>";
                            echo "<td>
                                <button class='btn-edit' data-package-id='" . $p_row['package_id'] . "' onclick=\"openModal('modal-edit-package', 'package', this)\">Edit</button>
                              </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center;'>No packages created yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="section-bookings" class="content-section">
            <div class="section-header">
                <h3>Booking Requests</h3>
            </div>
            <table id="table-bookings">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Phone Number</th>
                        <th>Email Address</th>
                        <th>Venue Name</th>
                        <th>Event Date</th>
                        <th>Package</th>
                        <th>Guests</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $booking_query = "SELECT b.*, c.client_name, c.client_phonenum, c.client_email, v.venue_name, p.package_name 
                                      FROM booking b 
                                      JOIN client c ON b.client_id = c.client_id 
                                      JOIN venue v ON b.venue_id = v.venue_id 
                                      JOIN package p ON b.package_id = p.package_id 
                                      WHERE v.owner_id = $owner_id";
                    $booking_result = mysqli_query($conn, $booking_query);
                    if (mysqli_num_rows($booking_result) > 0) {
                        while ($b_row = mysqli_fetch_assoc($booking_result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($b_row['client_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($b_row['client_phonenum']) . "</td>";
                            echo "<td>" . htmlspecialchars($b_row['client_email']) . "</td>";
                            echo "<td>" . htmlspecialchars($b_row['venue_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($b_row['event_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($b_row['package_name']) . "</td>";
                            echo "<td>" . number_format($b_row['num_of_guests']) . "</td>";
                            echo "<td>
                                <select class='select-status' data-booking-id='" . $b_row['booking_id'] . "' onchange='updateDatabaseStatus(this); updateStatusStyle(this); updateStats();'>";
                            $statuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
                            foreach ($statuses as $st) {
                                $selected = ($b_row['bookingstatus'] == $st) ? "selected" : "";
                                echo "<option value='$st' $selected>$st</option>";
                            }
                            echo " </select> </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align:center;'>No booking requests found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="toast-notification" id="toast-global"></div>

    <div id="modal-view-image" class="modal-overlay">
        <div class="modal-box-view-image">
            <h3 id="gallery-title" style="margin-bottom: 15px; font-weight: 600; color: var(--primary-burgundy); font-family: 'Playfair Display', serif;">Image View</h3>
            <button class="image-nav-btn prev-btn" onclick="changeImage(-1)">&#10094;</button>
            <img id="ssm-img-placeholder" src="" alt="Image">
            <button class="image-nav-btn next-btn" onclick="changeImage(1)">&#10095;</button>
            <p id="image-counter" style="margin-bottom: 15px; color: var(--text-muted); font-size: 13.5px;"></p>
            <div class="form-actions" style="justify-content: center; margin-top: 0;">
                <button type="button" class="btn-close-image" onclick="closeModal('modal-view-image')">Close</button>
            </div>
        </div>
    </div>

    <div id="modal-add-venue" class="modal-overlay">
        <div class="modal-box-add">
            <h3>Add New Venue</h3>
            <form id="form-add-venue" method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="action" value="add_venue">
                <div class="form-group">
                    <label>Owner Name</label>
                    <input type="text" id="v-owner-name" name="v_owner_name" placeholder="e.g., Ahmad bin Zulkifli" required>
                </div>
                <div class="form-group">
                    <label>Venue Name</label>
                    <input type="text" id="v-name" name="v_name" required>
                </div>
                <div class="form-group">
                    <label>Full Address</label>
                    <input type="text" id="v-address" name="v_address">
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="text" id="v-capacity" name="v_capacity" required>
                </div>
                <div class="form-group">
                    <label>Venue Images (Upload at least one)</label>
                    <input type="file" id="v-images" name="v_images[]" accept="image/*" multiple required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('modal-add-venue')">Cancel</button>
                    <button type="submit" class="btn-submit">Save Venue</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-edit-venue" class="modal-overlay">
        <div class="modal-box-add">
            <h3>Edit Venue</h3>
            <form id="form-edit-venue" method="POST" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="action" value="edit_venue">
                <input type="hidden" name="edit_venue_id" id="edit-venue-id" value="">
                <div class="form-group">
                    <label>Owner Name</label>
                    <input type="text" id="edit-v-owner" name="edit_v_owner" required>
                </div>
                <div class="form-group">
                    <label>Venue Name</label>
                    <input type="text" id="edit-v-name" name="edit_v_name" required>
                </div>
                <div class="form-group">
                    <label>Full Address</label>
                    <input type="text" id="edit-v-address" name="edit_v_address" required>
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="text" id="edit-v-capacity" name="edit_v_capacity" required>
                </div>
                <div class="form-group">
                    <label>Venue Images</label>
                    <input type="file" id="edit-v-images" name="edit_v_images[]" accept="image/*" multiple>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('modal-edit-venue')">Cancel</button>
                    <button type="submit" class="btn-submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-add-package" class="modal-overlay">
        <div class="modal-box-add">
            <h3>Add New Package</h3>
            <form id="form-add-package" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="action" value="add_package">
                <div class="form-group">
                    <label>Enter Your Venue Name</label>
                    <input type="text" id="p-venue" name="p_venue" placeholder="e.g., Dewan Serbaguna Telok Mas" required>
                </div>
                <div class="form-group">
                    <label>Package Name</label>
                    <input type="text" id="p-name" name="p_name" placeholder="e.g., Basic, Silver, Gold" required>
                </div>
                <div class="form-group">
                    <label>Price (RM)</label>
                    <input type="text" id="p-price" name="p_price" required>
                </div>
                <div class="form-group">
                    <label>Package Inclusions</label>
                    <textarea id="p-inclusions" name="p_inclusions" style="height: 80px; resize: none;" placeholder="e.g., - nasi&#10;- ayam&#10;- air"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('modal-add-package')">Cancel</button>
                    <button type="submit" class="btn-submit">Save Package</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-edit-package" class="modal-overlay">
        <div class="modal-box-add">
            <h3>Edit Package</h3>
            <form id="form-edit-package" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="action" value="edit_package">
                <input type="hidden" name="edit_package_id" id="edit-package-id" value="">
                <div class="form-group">
                    <label>Select Venue</label>
                    <select id="edit-p-venue" name="edit_p_venue" required>
                        <?php
                        $v_options_res = mysqli_query($conn, "SELECT venue_name FROM venue WHERE owner_id = $owner_id");
                        while ($opt = mysqli_fetch_assoc($v_options_res)) {
                            echo "<option value='" . htmlspecialchars($opt['venue_name']) . "'>" . htmlspecialchars($opt['venue_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Package Name</label>
                    <input type="text" id="edit-p-name" name="edit_p_name" required>
                </div>
                <div class="form-group">
                    <label>Price (RM)</label>
                    <input type="text" id="edit-p-price" name="edit_p_price" required>
                </div>
                <div class="form-group">
                    <label>Package Inclusions</label>
                    <textarea id="edit-p-inclusions" name="edit_p_inclusions" style="height: 80px; resize: none;"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('modal-edit-package')">Cancel</button>
                    <button type="submit" class="btn-submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-delete-confirmation" class="modal-overlay">
        <div class="modal-box-delete">
            <p id="delete-prompt-text">Are you sure you want to delete this?</p>
            <div class="form-actions" style="justify-content: center; margin-top: 0;">
                <button type="button" class="btn-cancel" style="padding: 10px 24px;" onclick="closeModal('modal-delete-confirmation')">Cancel</button>
                <button type="button" class="btn-confirm-ok" id="btn-confirm-delete-action">Delete</button>
            </div>
        </div>
    </div>

    <div id="modal-logout-confirmation" class="modal-overlay">
        <div class="modal-box-logout">
            <p>Are you sure you want to log out? You will need to login again to access the panel.</p>
            <div class="form-actions" style="justify-content: center; margin-top: 0; gap: 15px;">
                <button type="button" class="btn-cancel" style="padding: 10px 24px;" onclick="closeModal('modal-logout-confirmation')">Cancel</button>
                <button type="button" class="btn-logout-confirm" onclick="executeLogout()">Log Out</button>
            </div>
        </div>
    </div>

    <script>
        let rowToDelete = null;
        let rowToEdit = null;
        let currentGallery = [];
        let currentIndex = 0;

        window.onload = function() {
            const dropdowns = document.querySelectorAll('.select-status');
            dropdowns.forEach(select => updateStatusStyle(select));
            updateStats();

            <?php if (!empty($toast_message)): ?>
                triggerToast('<?php echo $toast_message; ?>');
            <?php endif; ?>
        };

        function switchTab(tabName) {
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => section.classList.remove('active-section'));
            const links = document.querySelectorAll('.nav-links a');
            links.forEach(link => link.classList.remove('active'));
            document.getElementById('section-' + tabName).classList.add('active-section');
            document.getElementById('tab-' + tabName).classList.add('active');
        }

        function openModal(modalId, type = '', buttonElement = null) {
            document.getElementById(modalId).style.display = 'flex';
            if (modalId === 'modal-delete-confirmation') {
                rowToDelete = buttonElement.closest('tr');
                const promptText = document.getElementById('delete-prompt-text');
                const confirmBtn = document.getElementById('btn-confirm-delete-action');
                if (type === 'venue') {
                    promptText.textContent = "Are you sure you want to delete this venue? This action cannot be undone.";
                    confirmBtn.setAttribute('onclick', "confirmDelete('Venue Deleted Successfully!')");
                }
            }

            if (modalId === 'modal-edit-venue' && type === 'venue') {
                rowToEdit = buttonElement.closest('tr');
                const cells = rowToEdit.getElementsByTagName('td');

                const venueId = buttonElement.getAttribute('data-venue-id');
                document.getElementById('edit-venue-id').value = venueId;

                document.getElementById('edit-v-owner').value = cells[0].textContent; 
                document.getElementById('edit-v-name').value = cells[1].textContent;
                document.getElementById('edit-v-address').value = cells[2].textContent;
                document.getElementById('edit-v-capacity').value = cells[3].textContent.replace(/,/g, '');
            }

            if (modalId === 'modal-edit-package' && type === 'package') {
                rowToEdit = buttonElement.closest('tr');
                const cells = rowToEdit.getElementsByTagName('td');

                const packageId = buttonElement.getAttribute('data-package-id');
                document.getElementById('edit-package-id').value = packageId;

                document.getElementById('edit-p-venue').value = cells[0].textContent;
                document.getElementById('edit-p-name').value = cells[1].textContent;
                document.getElementById('edit-p-price').value = cells[2].textContent.replace(/,/g, '');
                document.getElementById('edit-p-inclusions').value = cells[3].textContent === '-' ? '' : cells[3].textContent;
            }
        }

        function openGalleryModal(images, venueName) {
            currentGallery = images;
            currentIndex = 0;
            document.getElementById('gallery-title').textContent = venueName;
            updateGalleryImage();
            document.getElementById('modal-view-image').style.display = 'flex';
            document.querySelector('.prev-btn').style.display = 'block';
            document.querySelector('.next-btn').style.display = 'block';
        }

        function updateGalleryImage() {
            document.getElementById('ssm-img-placeholder').src = currentGallery[currentIndex];
            document.getElementById('image-counter').textContent = `(Picture ${currentIndex + 1} of ${currentGallery.length})`;
        }

        function changeImage(direction) {
            currentIndex += direction;
            if (currentIndex < 0) currentIndex = currentGallery.length - 1;
            if (currentIndex >= currentGallery.length) currentIndex = 0;
            updateGalleryImage();
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function executeLogout() {
            window.location.href = 'index.php';
        }

        function updateDatabaseStatus(selectElement) {
            const bookingId = selectElement.getAttribute('data-booking-id');
            const newStatus = selectElement.value;

            const formData = new FormData();
            formData.append('action', 'update_booking_status');
            formData.append('booking_id', bookingId);
            formData.append('status_value', newStatus);
            fetch('<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    triggerToast('Booking Status Updated Successfully!');
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function updateStatusStyle(selectElement) {
            const status = selectElement.value;
            if (status === 'Confirmed') {
                selectElement.style.color = '#2f855a';
            } else if (status === 'Pending') {
                selectElement.style.color = '#dd6b20';
            } else if (status === 'Completed') {
                selectElement.style.color = '#3182ce';
            } else if (status === 'Cancelled') {
                selectElement.style.color = '#e53e3e';
            }
        }

        function updateStats() {
            let pendingCount = 0;
            let confirmedCount = 0;
            let completedCount = 0;
            let cancelledCount = 0;

            const dropdowns = document.querySelectorAll('#table-bookings tbody .select-status');
            dropdowns.forEach(select => {
                const currentStatus = select.value;
                if (currentStatus === 'Pending') pendingCount++;
                if (currentStatus === 'Confirmed') confirmedCount++;
                if (currentStatus === 'Completed') completedCount++;
                if (currentStatus === 'Cancelled') cancelledCount++;
            });
            document.getElementById('count-pending').textContent = pendingCount + " Pending Bookings";
            document.getElementById('count-confirmed').textContent = confirmedCount + " Confirmed Bookings";
            document.getElementById('count-completed').textContent = completedCount + " Completed Bookings";
            document.getElementById('count-cancelled').textContent = cancelledCount + " Cancelled Bookings";
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