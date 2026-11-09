<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_admin'])) {
    $target_venue_id = mysqli_real_escape_string($conn, $_POST['venue_id']);
    $action_type = mysqli_real_escape_string($conn, $_POST['action_admin']);
    
    $update_query = "UPDATE venue SET venue_status = '$action_type' WHERE venue_id = '$target_venue_id'";
    mysqli_query($conn, $update_query);
    
    $toast_backend_message = "Venue status successfully updated to " .
    ucfirst($action_type) . "!";
}

$query = "SELECT v.*, vo.owner_name, a.admin_id 
          FROM venue v
          LEFT JOIN venue_owner vo ON v.owner_id = vo.owner_id
          LEFT JOIN admin a ON v.admin_id = a.admin_id";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - All Venues</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        to { opacity: 1;
        transform: translateY(0); }
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .section-title-container {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .title-inline-container {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .package-select {
        padding: 6px 12px;
        font-size: 13.5px;
        font-weight: 600;
        background-color: var(--white);
        border: 1.5px solid #e2e8f0;
        border-radius: var(--radius-sm);
        cursor: pointer;
        outline: none;
        transition: all 0.2s ease;
    }

    .package-select:focus {
        border-color: var(--primary-burgundy);
        box-shadow: 0 0 0 3px rgba(122, 34, 79, 0.12);
    }

    .section-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: var(--primary-burgundy);
        font-family: 'Playfair Display', serif;
    }

    .sub-nav {
        display: flex;
        gap: 20px;
    }

    .sub-nav span {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-muted);
        cursor: pointer;
        padding-bottom: 4px;
        border-bottom: 2px solid transparent;
        transition: all 0.2s ease;
    }

    .sub-nav span:hover {
        color: var(--primary-burgundy);
    }

    .sub-nav span.active-sub {
        color: var(--primary-burgundy);
        font-weight: 600;
        border-bottom: 2px solid var(--primary-burgundy);
    }

    .chart-container {
        display: none;
        width: 100%;
        overflow-x: auto;
        margin: 20px auto;
        padding-bottom: 15px;
    }

    .chart-inner {
        min-width: 600px;
        height: 350px;
        position: relative;
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

    .btn-view {
        background-color: var(--bg-page);
        color: var(--text-dark);
        border: 1.5px solid var(--border-color);
        padding: 6px 14px;
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-weight: 600;
        font-size: 12.5px;
        transition: all 0.2s ease;
    }
    .btn-view:hover {
        background-color: var(--accent-pink);
        color: var(--primary-burgundy);
        border-color: var(--badge-pink);
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
        to { opacity: 1;
        transform: translateY(0); }
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

    .btn-confirm-logout {
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

    .btn-confirm-logout:hover {
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

    .select-approved {
        background-color: #f0fff4 !important;
        color: #2f855a !important;
        border-color: #c6f6d5 !important;
    }

    .select-rejected {
        background-color: #fff5f5 !important;
        color: #e53e3e !important;
        border-color: #fed7d7 !important;
    }

    .select-pending {
        background-color: #fffaf0 !important;
        color: #dd6b20 !important;
        border-color: #feebc8 !important;
    }

    .modal-box-view-image {
        background-color: var(--white);
        width: 90%;
        max-width: 650px;
        border-radius: var(--radius-lg);
        padding: 30px;
        text-align: center;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        animation: fadeIn 0.3s ease;
        position: relative;
        border: 1px solid var(--border-color);
    }

    .modal-box-view-image h4 {
        font-size: 18px;
        margin-bottom: 15px;
        color: var(--primary-burgundy);
        font-weight: 700;
        font-family: 'Playfair Display', serif;
    }

    .modal-box-view-image img {
        max-width: 100%;
        max-height: 60vh;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-color);
        object-fit: contain;
        margin-bottom: 15px;
    }

    .modal-box-view-image p {
        margin: 15px 0;
        font-size: 14px;
        color: var(--text-muted);
        font-weight: 500;
    }

    .nav-arrow {
        position: absolute;
        top: 45%;
        transform: translateY(-50%);
        background: var(--primary-burgundy);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        box-shadow: 0 4px 10px rgba(122, 34, 79, 0.2);
        transition: background 0.2s;
    }

    .nav-arrow:hover {
        background: var(--primary-dark);
    }

    .prev-arrow { left: 15px; }
    .next-arrow { right: 15px; }

    .btn-close-purple {
        background: linear-gradient(135deg, var(--primary-burgundy) 0%, var(--primary-dark) 100%);
        color: var(--white);
        border: none;
        padding: 11px 28px;
        font-size: 14px;
        font-weight: 600;
        border-radius: 30px;
        cursor: pointer;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(122, 34, 79, 0.2);
    }
    .btn-close-purple:hover {
        background: linear-gradient(135deg, var(--primary-dark) 0%, #400d27 100%);
    }
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="logo-area">Wedding Hall Management - Admin</div>
        <div class="nav-links">
            <a href="admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="admin_venues.php" class="active"><i class="fa-solid fa-building"></i> Venues</a>
            <a href="admin_packages.php"><i class="fa-solid fa-box-archive"></i> Packages</a>
            <a href="admin_users.php"><i class="fa-solid fa-users"></i> Users</a>
            <a href="admin_bookings.php"><i class="fa-regular fa-calendar-check"></i> Bookings</a>
            <a class="logout" onclick="openModal('modal-logout-confirmation', this)"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log out</a>
        </div>
    </div>

    <div class="welcome-banner">
        <h2>Welcome, Admin!</h2>
        <p>Manage users, venues and generate reports.</p>
    </div>

    <div class="content-section" style="display: block;">
        <div class="section-header">
            <div class="section-title-container">
                <div class="title-inline-container">
                    <h3>All Venues</h3>
                </div>
                <div class="sub-nav">
                    <span id="sub-chart" onclick="toggleView('chart')">Chart</span>
                    <span id="sub-list" class="active-sub" onclick="toggleView('list')">List</span>
                </div>
            </div>
        </div>

        <div id="view-chart" class="chart-container">
            <div class="chart-inner">
                <canvas id="venueOwnerChart"></canvas>
            </div>
        </div>

        <div id="view-list" style="display: block;">
            <table id="table-venues">
                <thead>
                    <tr>
                         <th>Owner Name</th>
                        <th>Venue Name</th>
                        <th>Full Address</th>
                        <th>Capacity</th>
                         <th>Venues Images</th>
                        <th>Status</th>
                    </tr>
                </thead>
                 <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                         while ($row = mysqli_fetch_assoc($result)) {
                            $venue_id = $row['venue_id'];
                            $owner_name = htmlspecialchars($row['owner_name']);
                            $venue_name = htmlspecialchars($row['venue_name']);
                            $venue_location = htmlspecialchars($row['venue_location']);
                            $venue_capacity = number_format($row['venue_capacity']);
                            $venue_image = htmlspecialchars($row['venue_image']);
                            $venue_status = isset($row['venue_status']) ? strtolower($row['venue_status']) : 'pending';

if ($venue_status == 'rejected') {
    $status_class = 'select-rejected';
} elseif ($venue_status == 'pending') {
    $status_class = 'select-pending';
} else {
    $status_class = 'select-approved';
}
                    ?>
                    <tr data-package="basic">
                        <td><?php echo $owner_name; ?></td>
                        <td><?php echo $venue_name; ?></td>
                        <td><?php echo $venue_location; ?></td>
                        <td><?php echo $venue_capacity; ?></td>
                        <td>
                            <button class="btn-view" onclick="openImageModal(['images/<?php echo $venue_image; ?>'], '<?php echo $venue_name; ?>')">View</button>
                        </td>
                        <td>
                             <form action="" method="POST" id="form-status-<?php echo $venue_id; ?>" style="margin:0; padding:0;">
                                <input type="hidden" name="venue_id" value="<?php echo $venue_id; ?>">
                               <select name="action_admin" class="package-select <?php echo $status_class; ?>" style="font-weight: 600;" onchange="submitStatusForm('<?php echo $venue_id; ?>', this)">
                                    <option value="pending" class="select-pending" <?php if($venue_status == 'pending') echo 'selected'; ?>>Pending</option>
                                    <option value="approved" class="select-approved" <?php if($venue_status == 'approved') echo 'selected'; ?>>Approved</option>
                                    <option value="rejected" class="select-rejected" <?php if($venue_status == 'rejected') echo 'selected'; ?>>Rejected</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php
                         }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>No venues found in database.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="toast-notification" id="toast-global"></div>

<div id="modal-view-image" class="modal-overlay">
    <div class="modal-box-view-image">
        <h4 id="img-title">Venue Pictures</h4>
        <img id="img-gallery-placeholder" src="" alt="Image">
         <p id="img-counter">Picture 1 of 2</p>
        <button id="btn-prev" class="nav-arrow prev-arrow" onclick="changeImage(-1)">&#10094;</button>
        <button id="btn-next" class="nav-arrow next-arrow" onclick="changeImage(1)">&#10095;</button>
        <button class="btn-close-purple" onclick="closeModal('modal-view-image')">Close</button>
    </div>
</div>

<div id="modal-delete-confirmation" class="modal-overlay">
    <div class="modal-box-delete">
        <p>Are you sure you want to delete this venue? This action cannot be undone.</p>
        <div class="form-actions" style="justify-content: center; margin-top: 0;">
            <button type="button" class="btn-cancel" style="padding: 10px 24px;" onclick="closeModal('modal-delete-confirmation')">Cancel</button>
            <button type="button" class="btn-confirm-ok" id="btn-confirm-delete-action">Delete</button>
        </div>
    </div>
</div>

<div id="modal-logout-confirmation" class="modal-overlay">
    <div class="modal-box-delete">
        <p>Are you sure you want to log out? You will need to login again to access the panel.</p>
        <div class="form-actions" style="justify-content: center; margin-top: 0;">
            <button type="button" class="btn-cancel" style="padding: 10px 24px;" onclick="closeModal('modal-logout-confirmation')">Cancel</button>
            <button type="button" class="btn-confirm-logout" id="btn-confirm-logout-action">Log Out</button>
        </div>
    </div>
</div>

<script>
    let rowToDelete = null;
    let myBarChart = null; 
    let currentImages = [];
    let currentIdx = 0;
    function submitStatusForm(venueId, selectElement) {
        updateDropdownColor(selectElement);
        document.getElementById('form-status-' + venueId).submit();
    }

    function updateDropdownColor(selectElement) {
    if (selectElement.value === 'approved') {
        selectElement.className = 'package-select select-approved';
    } else if (selectElement.value === 'rejected') {
        selectElement.className = 'package-select select-rejected';
    } else if (selectElement.value === 'pending') {
        selectElement.className = 'package-select select-pending';
    } else {
        selectElement.className = 'package-select';
    }
}

    function openImageModal(imageArray, title) {
        currentImages = imageArray;
        currentIdx = 0;
        document.getElementById('img-title').textContent = title;
        document.getElementById('img-gallery-placeholder').src = currentImages[currentIdx];
        document.getElementById('img-counter').textContent = `Picture ${currentIdx + 1} of ${currentImages.length}`;
        
        document.getElementById('btn-prev').style.display = 'flex';
        document.getElementById('btn-next').style.display = 'flex';
        
        document.getElementById('modal-view-image').style.display = 'flex';
    }

    function changeImage(step) {
        currentIdx += step;
        if (currentIdx < 0) currentIdx = currentImages.length - 1;
        if (currentIdx >= currentImages.length) currentIdx = 0;
        document.getElementById('img-gallery-placeholder').src = currentImages[currentIdx];
        document.getElementById('img-counter').textContent = `Picture ${currentIdx + 1} of ${currentImages.length}`;
    }

    function toggleView(viewType) {
        const chartView = document.getElementById('view-chart');
        const listView = document.getElementById('view-list');
        const subChart = document.getElementById('sub-chart');
        const subList = document.getElementById('sub-list');
        if (viewType === 'chart') {
            chartView.style.display = 'block';
            listView.style.display = 'none';
            subChart.classList.add('active-sub');
            subList.classList.remove('active-sub');
            renderBarChart(); 
        } else {
            chartView.style.display = 'none';
            listView.style.display = 'block';
            subChart.classList.remove('active-sub');
            subList.classList.add('active-sub');
        }
    }

    function renderBarChart() {
        const rows = document.querySelectorAll('#table-venues tbody tr');
        const ownerCounts = {};

        rows.forEach(row => {
            if (row.style.display !== 'none' && row.cells.length > 1) {
                const ownerName = row.cells[0].textContent.trim(); 
                ownerCounts[ownerName] = (ownerCounts[ownerName] || 0) + 1;
            }
        });
        const labels = Object.keys(ownerCounts);
        const data = Object.values(ownerCounts);

        if (myBarChart) {
            myBarChart.destroy();
        }

        if (labels.length === 0) {
            return;
        }

        const dynamicWidth = Math.max(600, labels.length * 80);
        document.querySelector('.chart-inner').style.width = dynamicWidth + 'px';

        const ctx = document.getElementById('venueOwnerChart').getContext('2d');
        myBarChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of halls',
                    data: data,
                    backgroundColor: '#7a224f',
                    borderColor: '#581637',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Number of Halls by Owner' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
             }
        });
    }

    function openModal(modalId, buttonElement) {
        document.getElementById(modalId).style.display = 'flex';
        if (modalId === 'modal-delete-confirmation') {
            rowToDelete = buttonElement.closest('tr');
            const confirmBtn = document.getElementById('btn-confirm-delete-action');
            confirmBtn.setAttribute('onclick', "confirmDelete('Venue Deleted Successfully!')");
        }

        if (modalId === 'modal-logout-confirmation') {
            const confirmLogoutBtn = document.getElementById('btn-confirm-logout-action');
            confirmLogoutBtn.setAttribute('onclick', "confirmLogout()");
        }
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.none = 'none';
        document.getElementById(modalId).style.display = 'none';
    }

    function confirmDelete(message) {
        if (rowToDelete) {
            rowToDelete.remove();
            rowToDelete = null;
        }
        closeModal('modal-delete-confirmation');
        triggerToast(message);
        if (document.getElementById('view-chart').style.display === 'block') {
            renderBarChart();
        }
    }

    function confirmLogout() {
        window.location.href = 'index.php';
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

    <?php if(isset($toast_backend_message) && !empty($toast_backend_message)): ?>
        window.addEventListener('DOMContentLoaded', () => {
            triggerToast("<?php echo $toast_backend_message; ?>");
        });
    <?php endif; ?>
</script>

</body>
</html>