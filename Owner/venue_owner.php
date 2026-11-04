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

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #f4f5f7;
            color: #2d3748;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            width: 100%;
            padding-bottom: 60px;
        }

        .header {
            background-color: #ffffff;
            padding: 24px 5%;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .logo-area {
            font-size: 24px;
            font-weight: 700;
            color: #4a042e;
            letter-spacing: -0.5px;
        }

        .nav-links {
            display: flex;
            justify-content: center;
            gap: 25px;
        }

        .nav-links a {
            text-decoration: none;
            color: #718096;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            padding: 8px 4px;
            transition: color 0.2s ease, border-color 0.2s ease;
            border-bottom: 2px solid transparent;
        }

        .nav-links a:hover {
            color: #710349;
        }

        .nav-links a.active {
            color: #710349;
            border-bottom: 2px solid #710349;
        }

        .nav-links a.logout {
            color: #e53e3e;
        }

        .nav-links a.logout:hover {
            color: #c53030;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #710349 0%, #4a042e 100%);
            color: white;
            margin: 40px auto 25px auto;
            padding: 40px;
            border-radius: 16px;
            width: 90%;
            max-width: 1200px;
            box-shadow: 0 4px 20px #e7cfde;
            text-align: center;
        }

        .welcome-banner h2 {
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .welcome-banner p {
            font-size: 15px;
            color: #f7fafc;
            opacity: 0.85;
        }

        .stats-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin: 0 auto 30px auto;
            width: 90%;
            max-width: 1200px;
        }

        .stat-card {
            background-color: white;
            color: #1a202c;
            padding: 24px;
            border-radius: 14px;
            text-align: left;
            flex: 1;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 6px -1px #f0f0f2, 0 2px 4px -1px #f5f5f6;
            border: 1px solid #edf2f7;
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
            background-color: #710349;
        }

        .content-section {
            background-color: #ffffff;
            color: #2d3748;
            margin: 0 auto;
            padding: 35px;
            border-radius: 16px;
            width: 90%;
            max-width: 1200px;
            position: relative;
            min-height: 320px;
            display: none;
            box-shadow: 0 4px 6px -1px #f0f0f2;
            border: 1px solid #edf2f7;
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
            border-bottom: 1px solid #edf2f7;
        }

        .section-header h3 {
            font-size: 20px;
            font-weight: 600;
            color: #1a202c;
        }

        .btn-add {
            background-color: #710349;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s ease;
        }

        .btn-add:hover {
            background-color: #520235;
        }

        .btn-edit {
            background-color: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            margin-right: 5px;
            transition: all 0.2s ease;
        }

        .btn-edit:hover {
            background-color: #edf2f7;
            color: #1a202c;
        }

        .btn-delete {
            background-color: #fff5f5;
            color: #e53e3e;
            border: 1px solid #fed7d7;
            padding: 8px 16px;
            border-radius: 6px;
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
            font-size: 15px;
        }

        th,
        td {
            padding: 16px 18px;
            border-bottom: 1px solid #edf2f7;
        }

        .inclusions-cell {
            white-space: pre-line;
            line-height: 1.6;
        }

        th {
            font-weight: 600;
            color: #718096;
            background-color: #f7fafc;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            color: #4a5568;
        }

        tr:hover td {
            background-color: #fcfcfd;
        }

        td:last-child {
            white-space: nowrap;
        }

        .select-status {
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid #cbd5e0;
            background-color: #fff;
            cursor: pointer;
            outline: none;
            transition: all 0.2s;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(51, 47, 50, 0.4);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }

        .modal-box-add,
        .modal-box-delete {
            background-color: #ffffff;
            width: 90%;
            border-radius: 16px;
            padding: 35px;
            color: #1a202c;
            border: 1px solid #e2e8f0;
            animation: fadeIn 0.4s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-box-add {
            max-width: 580px;
        }

        .modal-box-delete {
            max-width: 440px;
            text-align: center;
        }

        .modal-box-logout {
            background-color: #ffffff;
            width: 90%;
            max-width: 420px;
            border-radius: 16px;
            padding: 35px;
            text-align: center;
            box-shadow: 0 10px 25 rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.4s ease-out;
        }

        .modal-box-logout p {
            font-size: 15px;
            color: #4a5568;
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .btn-logout-confirm {
            background-color: #e53e3e;
            color: #ffffff;
            border: none;
            padding: 11px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-logout-confirm:hover {
            background-color: #c53030;
        }

        .modal-box-add h3 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 25px;
            font-weight: 600;
            color: #1a202c;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #4a5568;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #cbd5e0;
            border-radius: 8px;
            font-size: 14px;
            background-color: #fff;
            color: #2d3748;
            transition: all 0.2s ease;
        }

        .form-group input[type="file"] {
            padding: 8px 12px;
            cursor: pointer;
        }

        .form-group input[type="file"]::file-selector-button {
            background-color: #710349;
            color: white;
            border: none;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 13px;
            margin-right: 10px;
            transition: background 0.2s ease;
            cursor: pointer;
        }

        .form-group input[type="file"]::file-selector-button:hover {
            background-color: #520235;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #710349;
            box-shadow: 0 0 0 3px #ebd5e4;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
        }

        .btn-submit {
            background-color: #710349;
            color: white;
            border: none;
            padding: 11px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-submit:hover {
            background-color: #520235;
        }

        .btn-cancel {
            background-color: #ffffff;
            color: #4a5568;
            border: 1px solid #cbd5e0;
            padding: 11px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background-color: #f7fafc;
            color: #1a202c;
        }

        .btn-confirm-ok {
            background-color: #e53e3e;
            color: #ffffff;
            border: none;
            padding: 11px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-confirm-ok:hover {
            background-color: #c53030;
        }

        .toast-notification {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background-color: #2f855a;
            color: white;
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            z-index: 1010;
            box-shadow: 0 10px 15px -3px #e2e8f0;
            visibility: hidden;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .toast-notification.show-toast {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
        }

        .modal-box-view-image {
            background-color: #ffffff;
            width: 90%;
            max-width: 650px;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.4s ease-out;
            position: relative;
        }

        .modal-box-view-image img {
            max-width: 100%;
            max-height: 60vh;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .image-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: #710349;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 50%;
            font-weight: bold;
        }

        .prev-btn {
            left: 10px;
        }

        .next-btn {
            right: 10px;
        }

        .btn-close-image {
            background-color: #710349;
            color: #ffffff;
            border: none;
            padding: 11px 28px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-close-image:hover {
            background-color: #520235;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <div class="logo-area">🌸 Wedding Hall Management - Owner Panel</div>
            <div class="nav-links">
                <a onclick="switchTab('venues')" id="tab-venues" class="active">Venues</a>
                <a onclick="switchTab('packages')" id="tab-packages">Packages</a>
                <a onclick="switchTab('bookings')" id="tab-bookings">Bookings</a>
                <a class="logout" onclick="openModal('modal-logout-confirmation')">Log out</a>
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
                <button class="btn-add" onclick="openModal('modal-add-venue')">+ Add New Venue</button>
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
                <button class="btn-add" onclick="openModal('modal-add-package')">+ Add New Package</button>
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
            <h3 id="gallery-title" style="margin-bottom: 15px; font-weight: 600; color: #1a202c;">Image View</h3>
            <button class="image-nav-btn prev-btn" onclick="changeImage(-1)">&#10094;</button>
            <img id="ssm-img-placeholder" src="" alt="Image">
            <button class="image-nav-btn next-btn" onclick="changeImage(1)">&#10095;</button>
            <p id="image-counter" style="margin-bottom: 10px; color: #718096;"></p>
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
            window.location.href = 'login.php';
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