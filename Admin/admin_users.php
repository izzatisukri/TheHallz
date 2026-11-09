<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $role = strtolower(trim($_POST['role']));
    
    $success = false;
    if ($role === 'client') {
        $query_update = "UPDATE client SET client_status = '$status' WHERE client_email = '$email'";
        if (mysqli_query($conn, $query_update)) {
            $success = true;
        } else {
            $query_update_fallback = "UPDATE client SET status = '$status' WHERE client_email = '$email'";
            if (mysqli_query($conn, $query_update_fallback)) { $success = true; }
        }
    } elseif ($role === 'owner') {
        $query_update = "UPDATE venue_owner SET owner_status = '$status' WHERE owner_email = '$email'";
        if (mysqli_query($conn, $query_update)) {
            $success = true;
        } else {
            $query_update_fallback = "UPDATE venue_owner SET status = '$status' WHERE owner_email = '$email'";
            if (mysqli_query($conn, $query_update_fallback)) { $success = true; }
        }
    }
    
    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
    exit;
}

$query_client = "SELECT * FROM client";
$result_client = mysqli_query($conn, $query_client);

$query_owner = "SELECT * FROM venue_owner";
$result_owner = mysqli_query($conn, $query_owner);

$users_list = [];

if ($result_client && mysqli_num_rows($result_client) > 0) {
    while ($row = mysqli_fetch_assoc($result_client)) {
        $status = isset($row['client_status']) ? $row['client_status'] : (isset($row['status']) ? $row['status'] : 'Active');
        $users_list[] = [
            'name'  => $row['client_name'],
            'email' => $row['client_email'],
            'phone' => $row['client_phonenum'],
            'role'  => 'Client',
            'status' => $status
        ];
    }
}

if ($result_owner && mysqli_num_rows($result_owner) > 0) {
    while ($row = mysqli_fetch_assoc($result_owner)) {
        $status = isset($row['owner_status']) ? $row['owner_status'] : (isset($row['status']) ? $row['status'] : 'Active');
        $users_list[] = [
            'name'  => $row['owner_name'],
            'email' => $row['owner_email'],
            'phone' => $row['owner_phonenum'],
            'role'  => 'Owner',
            'status' => $status
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - All Users</title>

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

        /* TAMBAHAN: Menyusun susunan tajuk dan sub-menu rapat */
        .section-title-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .section-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-burgundy);
            font-family: 'Playfair Display', serif;
        }

        /* TAMBAHAN: Gaya reka bentuk sub-menu Chart & List */
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

        /* TAMBAHAN: Container khas untuk meletakkan saiz Pie Chart */
        .chart-container {
            display: none;
            /* Disembunyikan secara lalai (default) */
            max-width: 400px;
            margin: 20px auto;
        }

        /* Butang Delete Premium */
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

        /* PENGUBAHSUAIAN GAYA UNTUK DROPDOWN STATUS */
        .status-select {
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 13.5px;
            border: 1.5px solid #e2e8f0;
            background-color: var(--white);
            cursor: pointer;
            outline: none;
            transition: all 0.2s ease;
        }

        .status-active {
            color: #2f855a;
            background-color: #f0fff4;
            border-color: #c6f6d5;
        }

        .status-suspended {
            color: #dd6b20;
            background-color: #fffaf0;
            border-color: #feebc8;
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
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <div class="logo-area">Wedding Hall Management - Admin</div>
            <div class="nav-links">
                <a href="admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                <a href="admin_venues.php"><i class="fa-solid fa-building"></i> Venues</a>
                 <a href="admin_packages.php"><i class="fa-solid fa-box-archive"></i> Packages</a>
                <a href="admin_users.php" class="active"><i class="fa-solid fa-users"></i> Users</a>
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
                    <h3>All Users</h3>
      
                    <div class="sub-nav">
                        <span id="sub-chart" onclick="toggleView('chart')">Chart</span>
                        <span id="sub-list" class="active-sub" onclick="toggleView('list')">List</span>
                    </div>
            
                </div>
            </div>

            <div id="view-chart" class="chart-container">
                <canvas id="userRoleChart"></canvas>
            </div>

            <div id="view-list" style="display: block;">
                <table id="table-users">
         
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
         
                            <th>Phone Number</th>
                            <th>Role</th>
                            <th>Status</th>
                        
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($users_list)) {
          
                            foreach ($users_list as $user) {
                                $currentStatus = ucfirst(strtolower(trim($user['status'])));
                                $statusClass = ($currentStatus === 'Suspended') ? 'status-suspended' : 'status-active';
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td>
                                        <select class="status-select <?php echo $statusClass; ?>" 
                                                data-email="<?php echo htmlspecialchars($user['email']); ?>" 
                                                data-role="<?php echo htmlspecialchars($user['role']); ?>" 
                                                onchange="updateStatusStyle(this)">
                  
                                            <option value="Active" style="color: #2f855a;" <?php echo ($currentStatus === 'Active' || $currentStatus === '') ? 'selected' : ''; ?>>Active</option>
                                            <option value="Suspended" style="color: #dd6b20;" <?php echo ($currentStatus === 'Suspended') ? 'selected' : ''; ?>>Suspended</option>
                                        </select>
                                    </td>
               
                                </tr>
                        <?php
                            }
                        } else {
     
                            echo "<tr><td colspan='5' style='text-align:center;'>No users found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="toast-notification" id="toast-global"></div>
 
    </div>

    <div id="modal-delete-confirmation" class="modal-overlay">
        <div class="modal-box-delete">
            <p>Are you sure you want to delete this user?
            This action cannot be undone.</p>
            <div class="form-actions" style="justify-content: center; margin-top: 0;">
                <button type="button" class="btn-cancel" style="padding: 10px 24px;" onclick="closeModal('modal-delete-confirmation')">Cancel</button>
                <button type="button" class="btn-confirm-ok" id="btn-confirm-delete-action">Delete</button>
            </div>
        </div>
    </div>

    <div id="modal-logout-confirmation" class="modal-overlay">
        <div class="modal-box-delete">
            <p>Are you sure you want to log out?
            You will need to login again to access the panel.</p>
            <div class="form-actions" style="justify-content: center; margin-top: 0;">
                <button type="button" class="btn-cancel" style="padding: 10px 24px;" onclick="closeModal('modal-logout-confirmation')">Cancel</button>
                <button type="button" class="btn-confirm-ok" id="btn-confirm-logout-action">Log Out</button>
            </div>
        </div>
    </div>

    <script>
        let rowToDelete = null;
        let userPieChart = null;

        function updateStatusStyle(selectElement) {
            const userEmail = selectElement.getAttribute('data-email');
            const userRole = selectElement.getAttribute('data-role');
            const newStatus = selectElement.value;

            if (newStatus === "Active") {
                selectElement.classList.add('status-active');
                selectElement.classList.remove('status-suspended');
            } else if (newStatus === "Suspended") {
                selectElement.classList.add('status-suspended');
                selectElement.classList.remove('status-active');
            }

            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('email', userEmail);
            formData.append('status', newStatus);
            formData.append('role', userRole);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    triggerToast("User status updated to " + newStatus + " & saved successfully!");
                } else {
                    alert("Database update failed: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("An error occurred while updating status in the database.");
            });
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
                renderUserChart();
            } else {
                chartView.style.display = 'none';
                listView.style.display = 'block';
                subChart.classList.remove('active-sub');
                subList.classList.add('active-sub');
            }
        }

        function renderUserChart() {
            const rows = document.querySelectorAll('#table-users tbody tr');
            let clientCount = 0;
            let ownerCount = 0;

            rows.forEach(row => {
                if (row.cells.length >= 4) {
                    const role = row.cells[3].textContent.trim().toLowerCase();
                    if (role === 'client') {
                       
                        clientCount++;
                    } else if (role === 'owner') {
                        ownerCount++;
                    }
                }
            });
            if (userPieChart) {
                userPieChart.destroy();
            }

            const ctx = document.getElementById('userRoleChart').getContext('2d');
            userPieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Client', 'Owner'],
                    datasets: [{
                   
                        label: 'Total Users',
                        data: [clientCount, ownerCount],
                        backgroundColor: [
                            '#7a224f',
              
                            '#581637',
                            '#ed64a6',
                            '#ecc94b'
                        ],
      
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
       
                    plugins: {
                        legend: {
                            position: 'top',
                        },
        
                        title: {
                            display: true,
                            text: 'Breakdown of User Roles'
                      
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
                confirmBtn.setAttribute('onclick', "confirmDelete('User Delete Successfully!')");
            }

            if (modalId === 'modal-logout-confirmation') {
                const confirmLogoutBtn = document.getElementById('btn-confirm-logout-action');
                confirmLogoutBtn.setAttribute('onclick', "confirmLogout()");
            }
        }

        function closeModal(modalId) {
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
                renderUserChart();
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
    </script>

</body>

</html>