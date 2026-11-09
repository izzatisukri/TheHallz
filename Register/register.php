<?php
session_start();

include('db_connection.php');

$registration_status = "";
$message_content = "";
$role = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname'] ?? ''); 
    $email    = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $phone    = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = mysqli_real_escape_string($conn, $_POST['role'] ?? '');

    $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]).{6,}$/';

    if (!preg_match($passwordRegex, $password)) {
        $registration_status = "error";
        $message_content = "Password must be at least 6 characters with uppercase, lowercase, and symbols!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $default_status = "Active";

        if ($role === 'admin') {
            $check_admin_query = "SELECT COUNT(*) AS total_admin FROM admin";
            $check_admin_result = mysqli_query($conn, $check_admin_query);
            $admin_data = mysqli_fetch_assoc($check_admin_result);

            if ($admin_data['total_admin'] >= 1) {
                $registration_status = "error";
                $message_content = "Registration failed! An Admin account already exists. Only one Admin is allowed.";
            } else {
                $check_email = mysqli_query($conn, "SELECT admin_id FROM admin WHERE admin_email = '$email'");
                if (mysqli_num_rows($check_email) > 0) {
                    $registration_status = "error";
                    $message_content = "Email address is already registered as an Admin!";
                } else {
                    $insert_query = "INSERT INTO admin (admin_name, admin_email, admin_password, admin_role) 
                                      VALUES ('$fullname', '$email', '$hashed_password', 'admin')";
                    if (mysqli_query($conn, $insert_query)) {
                        $registration_status = "success";
                        
                        $_SESSION['admin_id'] = mysqli_insert_id($conn);
                        $_SESSION['admin_name'] = $fullname;
                        $_SESSION['admin_role'] = 'admin';
                    } else {
                        $registration_status = "error";
                        $message_content = "Database error: " . mysqli_error($conn);
                    }
                }
            }
        }

        elseif ($role === 'client') {
            $check_email = mysqli_query($conn, "SELECT client_id FROM client WHERE client_email = '$email'");
            if (mysqli_num_rows($check_email) > 0) {
                $registration_status = "error";
                $message_content = "Email address is already registered as a Client!";
            } else {
                $insert_query = "INSERT INTO client (client_name, client_email, client_phonenum, client_password, client_role, client_status) 
                                 VALUES ('$fullname', '$email', '$phone', '$hashed_password', 'client', '$default_status')";
                if (mysqli_query($conn, $insert_query)) {
                    $registration_status = "success";
                    
                    $_SESSION['client_id'] = mysqli_insert_id($conn);
                    $_SESSION['client_name'] = $fullname;
                    $_SESSION['client_role'] = 'client';
                } else {
                    $registration_status = "error";
                    $message_content = "Database error: " . mysqli_error($conn);
                }
            }
        }

        elseif ($role === 'venue_owner') {
            $check_email = mysqli_query($conn, "SELECT owner_id FROM venue_owner WHERE owner_email = '$email'");
            if (mysqli_num_rows($check_email) > 0) {
                $registration_status = "error";
                $message_content = "Email address is already registered as a Venue Owner!";
            } else {
                $insert_query = "INSERT INTO venue_owner (owner_name, owner_email, owner_phonenum, owner_password, owner_role, owner_status) 
                                 VALUES ('$fullname', '$email', '$phone', '$hashed_password', 'venue_owner', '$default_status')";
                if (mysqli_query($conn, $insert_query)) {
                    $registration_status = "success";
                    
                    $_SESSION['owner_id'] = mysqli_insert_id($conn);
                    $_SESSION['owner_name'] = $fullname;
                    $_SESSION['owner_role'] = 'venue_owner';
                } else {
                    $registration_status = "error";
                    $message_content = "Database error: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Account</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    background: radial-gradient(circle at center, #7d0552 0%, #520131 70%, #2b0019 100%); 
    min-height: 100vh; 
    width: 100vw;
    display: flex;
    align-items: center; 
    justify-content: center;
    overflow-x: hidden;
    overflow-y: auto; 
    padding: 20px 15px;
}

.container {
    text-align: center;
    width: 100%;
    max-width: 620px; 
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.title {
    color: #ffffff;
    font-size: 28px; 
    margin-bottom: 15px; 
    font-weight: 600; 
    letter-spacing: 0.8px;
    text-shadow: 0 2px 10px #0000004d;
    animation: fadeInDown 0.8s ease-out;
}

.form-card {
    background-color: #DCDCDC; 
    border: 1px solid #DCDCDC;
    border-radius: 24px; 
    padding: 25px 45px; 
    text-align: left;
    box-shadow: 
        0px 4px 6px -1px #0000001a,
        0px 20px 40px -10px #00000066;
    width: 100%;
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.form-group {
    margin-bottom: 12px;
}

.form-group label {
    display: block;
    font-size: 13.5px; 
    color: #2c2c2c;
    margin-bottom: 4px; 
    font-weight: bold;
    letter-spacing: 0.3px;
}

.password-hint {
    font-size: 11px;
    color: #710349;
    margin-bottom: 6px;
    display: block;
    font-style: italic;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px 16px; 
    border: 1.5px solid #e2e8f0; 
    border-radius: 10px; 
    font-size: 14px;
    color: #1a1a1a;
    background-color: #f8fafc; 
    outline: none;
    font-family: inherit; 
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-group input:focus,
.form-group select:focus {
    border-color: #710349;
    background-color: #ffffff;
    box-shadow: 0 0 0 4px #71034926;
}

.form-group select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;utf8,<svg fill='%23710349' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/><path d='M0 0h24v24H0z' fill='none'/></svg>");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 20px;
    padding-right: 45px;
}

.form-group input::placeholder {
    color: #94a3b8;
    font-size: 13px;
}

/* Privacy Notice Styling inside Form */
.privacy-notice-box {
    background-color: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 12px 15px;
    font-size: 12px;
    color: #334155;
    line-height: 1.4;
    margin-top: 15px;
    margin-bottom: 12px;
}

.privacy-notice-box p {
    margin-bottom: 8px;
}

.checkbox-group {
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.checkbox-group input[type="checkbox"] {
    width: auto;
    margin-top: 2px;
    cursor: pointer;
    accent-color: #710349;
}

.checkbox-group label {
    font-size: 12.5px;
    color: #1e293b;
    font-weight: 600;
    cursor: pointer;
    line-height: 1.3;
}

.btn-register {
    width: 100%;
    background-color: #710349;
    color: white;
    border: none;
    border-radius: 10px; 
    padding: 13px; 
    font-size: 15px;
    cursor: pointer;
    text-transform: uppercase;
    margin-top: 5px;
    margin-bottom: 12px; 
    letter-spacing: 2px;
    font-weight: bold;
    box-shadow: 0 4px 12px #7103494d;
    transition: all 0.2s ease;
}

.btn-register:hover:not(:disabled) {
    background-color: #540236;
    box-shadow: 0 6px 20px #71034966;
    transform: translateY(-1px);
}

.btn-register:disabled {
    background-color: #a3a3a3;
    box-shadow: none;
    cursor: not-allowed;
    opacity: 0.7;
}

.footer-links {
    text-align: center;
    font-size: 13.5px;
    color: #64748b;
}

.footer-links a {
    color: #710349;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.2s ease;
}

.footer-links a:hover {
    color: #4a002a;
    text-decoration: underline;
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-15px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Modal Styling */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); 
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

.modal-overlay.active {
    opacity: 1;
    pointer-events: auto;
}

.modal-card {
    background-color: #ffffff;
    border-radius: 24px; 
    padding: 30px 35px;
    max-width: 520px;
    width: 90%;
    text-align: left;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    transform: scale(0.9);
    transition: transform 0.3s ease;
}

.modal-overlay.active .modal-card {
    transform: scale(1);
}

.modal-title {
    font-size: 20px;
    font-weight: 700;
    color: #710349;
    margin-bottom: 12px;
    text-align: center;
}

.modal-text {
    font-size: 13.5px;
    color: #4a5568;
    line-height: 1.6;
    margin-bottom: 20px;
    max-height: 300px;
    overflow-y: auto;
    padding-right: 8px;
}

.modal-text p {
    margin-bottom: 10px;
}

.modal-btn-container {
    text-align: center;
}

.modal-btn {
    background-color: #710349; 
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 11px 32px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(113, 3, 73, 0.3);
    transition: all 0.2s ease;
}

.modal-btn:hover {
    background-color: #540236;
    box-shadow: 0 6px 16px rgba(113, 3, 73, 0.4);
}
</style>

</head>
<body>
    <div class="container">
        <h2 class="title">Create New Account</h2>

        <div class="form-card">
            <form action="" method="POST" id="registerForm">
                
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="client@gmail.com" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="01xxxxxxxx" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <span class="password-hint">* Must be at least 6 characters with uppercase, lowercase, and a symbol.</span>
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                        placeholder="Create a password" 
                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]).{6,}$"
                        title="Password must be at least 6 characters long and include at least one uppercase letter, one lowercase letter, and one symbol."
                        required>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" placeholder="Re-enter password" required>
                </div>

                <div class="form-group">
                    <label for="role">Register As</label>
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Select Role</option>
                        <option value="client">Client</option>
                        <option value="venue_owner">Venue Owner</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <!-- Privacy Notice Section -->
                <div class="privacy-notice-box">
                    <p>Before creating your account, please read and agree to our privacy policy.</p>
                    <p>Your personal information, including name, email address, phone number and booking details, will be collected and used for account management and wedding venue booking purpose. We take reasonable steps to protect your personal information from unauthorized access.</p>
                    <div class="checkbox-group">
                        <input type="checkbox" id="agree-privacy" name="agree_privacy" required>
                        <label for="agree-privacy">I have read and agree to the privacy policy and terms of use.</label>
                    </div>
                </div>

                <button type="submit" class="btn-register" id="btnRegister" disabled>REGISTER</button>

                <div class="footer-links">
                    Already have an account?
                    <a href="login.php">Login here</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Custom Modal for Success Privacy Policy & Error Messages -->
    <div class="modal-overlay" id="customModal">
        <div class="modal-card">
            <div class="modal-title" id="modalTitle">Privacy Policy & Terms</div>
            <div class="modal-text" id="modalMessage">
                <!-- Content set dynamically via JS -->
            </div>
            <div class="modal-btn-container">
                <button class="modal-btn" id="modalOkBtn">I Agree & Continue</button>
            </div>
        </div>
    </div>

    <script>
        const phpStatus = "<?php echo $registration_status; ?>";
        const phpMessage = "<?php echo $message_content; ?>";
        const chosenRole = "<?php echo $role; ?>";

        const modal = document.getElementById('customModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalOkBtn = document.getElementById('modalOkBtn');
        const agreeCheckbox = document.getElementById('agree-privacy');
        const btnRegister = document.getElementById('btnRegister');

        // Enable/disable register button based on checkbox state
        agreeCheckbox.addEventListener('change', function() {
            btnRegister.disabled = !this.checked;
        });

        // Handle Registration Success / Error Modal Display
        if (phpStatus === "success") {
            modalTitle.innerText = "Privacy Policy & Account Confirmation";
            modalMessage.innerHTML = `
                <p><strong>Welcome! Your registration was successful.</strong></p>
                <p>Before entering your account, please review our core Privacy Terms:</p>
                <p>1. <strong>Data Usage:</strong> Your personal information (Name, Email, Phone Number, and Booking Details) is collected exclusively for account management, venue bookings, and customer support.</p>
                <p>2. <strong>Data Protection:</strong> We implement administrative and technical security measures to prevent unauthorized access, data loss, or disclosure.</p>
                <p>3. <strong>Your Rights:</strong> You may update or request deletion of your account details at any time through your dashboard settings.</p>
                <p>Click below to accept and proceed to your account.</p>
            `;
            modalOkBtn.innerText = "I Agree & Continue";
            modal.classList.add('active');

            modalOkBtn.onclick = function() {
                modal.classList.remove('active');
                if (chosenRole === 'client') {
                    window.location.href = 'client.php';
                } else if (chosenRole === 'venue_owner') {
                    window.location.href = 'venue_owner.php';
                } else if (chosenRole === 'admin') {
                    window.location.href = 'admin_dashboard.php';
                }
            };
        } else if (phpStatus === "error") {
            modalTitle.innerText = "Registration Failed";
            modalMessage.innerText = phpMessage;
            modalOkBtn.innerText = "OK";
            modal.classList.add('active');
            modalOkBtn.onclick = function() {
                modal.classList.remove('active');
            };
        }

        // Form Validation before submit
        document.getElementById('registerForm').addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]).{6,}$/;
            if (!passwordRegex.test(password)) {
                event.preventDefault();
                modalTitle.innerText = "Invalid Password";
                modalMessage.innerText = 'Password must be at least 6 characters with uppercase, lowercase, and symbols!';
                modalOkBtn.innerText = "OK";
                modal.classList.add('active');
                modalOkBtn.onclick = function() {
                    modal.classList.remove('active');
                };
                return;
            }

            if (password !== confirmPassword) {
                event.preventDefault();
                modalTitle.innerText = "Password Mismatch";
                modalMessage.innerText = 'Password and Confirm Password do not match!';
                modalOkBtn.innerText = "OK";
                modal.classList.add('active');
                modalOkBtn.onclick = function() {
                    modal.classList.remove('active');
                };
                return;
            }
        });
    </script>
</body>
</html>