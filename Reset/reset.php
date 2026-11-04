<?php
include('db_connection.php');

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['step']) && $_POST['step'] == '2') {
    $email = trim($_POST['user_email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        
        $check_email = "SELECT client_email FROM client WHERE client_email = ?";
        $stmt_check = mysqli_prepare($conn, $check_email);
        
        if ($stmt_check) {
            mysqli_stmt_bind_param($stmt_check, "s", $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE client SET client_password = ? WHERE client_email = ?";
                $stmt_update = mysqli_prepare($conn, $update_query);
                
                if ($stmt_update) {
                    mysqli_stmt_bind_param($stmt_update, "ss", $hashed_password, $email);
                    
                    if (mysqli_stmt_execute($stmt_update)) {
                        echo "<script>
                                alert('Password has been successfully updated in the database!');
                                window.location.href = 'login.php';
                              </script>";
                        exit();
                    } else {
                        $error_message = "Failed to update password. Please try again.";
                    }
                    mysqli_stmt_close($stmt_update);
                } else {
                    $error_message = "Database error: Unable to prepare update statement.";
                }
                
            } else {
                $error_message = "Email address not found in our records.";
            }
            mysqli_stmt_close($stmt_check);
        } else {
            $error_message = "Database error: Unable to prepare verification statement.";
        }
    } else {
        $error_message = "Passwords do not match!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    background: radial-gradient(circle at center, #7d0552 0%, #520131 70%, #2b0019 100%); 
    height: 100vh; 
    width: 100vw;
    display: flex;
    align-items: center; 
    justify-content: center;
    overflow: hidden; 
    padding: 20px;
}

.container {
    text-align: center;
    width: 100%;
    max-width: 580px; 
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.logo-container {
    margin-bottom: 8px;
    width: 100%;
    animation: fadeInDown 0.8s ease-out;
}

.logo {
    width: 100%;
    max-width: 220px; 
    height: auto;
    display: block;
    margin: 0 auto;
    filter: drop-shadow(0px 4px 8px #61033f);
}

.title {
    color: #ffffff;
    font-size: 26px; 
    margin: 5px 0 25px 0;
    font-weight: 600;
    letter-spacing: 0.8px;
    text-shadow: 0 2px 10px #2b0019;
    animation: fadeInDown 0.8s ease-out;
}

.form-card {
    background-color: #DCDCDC;
    border: 1px solid #DCDCDC; 
    border-radius: 24px; 
    padding: 35px 45px; 
    text-align: left; 
    width: 100%;
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.instruction {
    color: #555555; 
    font-size: 14.5px;
    text-align: center;
    margin-bottom: 25px;
    font-weight: 500;
    line-height: 1.4;
}

.form-group {
    margin-bottom: 18px;
    transition: all 0.4s ease;
}

#password-section {
    display: none;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.4s ease;
}

.form-group label {
    display: block;
    font-size: 13.5px; 
    color: #2c2c2c;
    margin-bottom: 6px;
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

.form-group input {
    width: 100%;
    padding: 13px 16px; 
    border: 1.5px solid #e2e8f0; 
    border-radius: 10px; 
    font-size: 14.5px;
    color: #1a1a1a;
    background-color: #f8fafc; 
    outline: none;
    font-family: inherit;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-group input:focus {
    border-color: #710349;
    background-color: #ffffff;
    box-shadow: 0 0 0 4px #f1e6ed;
}

.btn-action {
    width: 100%;
    background-color: #710349;
    color: white;
    border: none;
    border-radius: 10px; 
    padding: 14px; 
    font-size: 15px;
    cursor: pointer;
    text-transform: uppercase;
    margin-top: 10px;
    margin-bottom: 18px; 
    letter-spacing: 2px;
    font-weight: bold;
    box-shadow: 0 4px 12px #ceb3c5;
    transition: all 0.2s ease;
}

.btn-action:hover {
    background-color: #540236;
    box-shadow: #c69fb7 0px 6px 20px;
    transform: translateY(-1px);
}

.login-link {
    text-align: center;
    font-size: 13.5px;
    color: #64748b;
}

.login-link a {
    color: #710349;
    text-decoration: none;
    font-weight: bold;
}

.login-link a:hover {
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
</style>

</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="images/wedding_logo.png" alt="Wedding Logo" class="logo">
        </div>

        <h2 class="title">Reset Password</h2>

        <div class="form-card">
            <p id="instruction-text" class="instruction">Enter your registered email address to find your account.</p>

            <?php if(!empty($error_message)): ?>
                 <p style="color: #e53e3e; text-align: center; font-weight: bold; margin-bottom: 15px; font-size: 14px;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form id="resetForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                
                <input type="hidden" name="step" id="php-step" value="1">
                <input type="hidden" name="user_email" id="hidden-email" value="">

                <div class="form-group" id="email-section">
                     <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="customer@gmail.com" required>
                </div>

                <div id="password-section">
                    <div class="form-group">
                         <label for="new-password">New Password</label>
                        <span class="password-hint">* Must be at least 6 characters with uppercase, lowercase, and a symbol.</span>
                        <input 
                            type="password" 
                            id="new-password" 
                            name="new_password"
                            placeholder="Create a password">
                     </div>

                    <div class="form-group">
                        <label for="confirm-password">Confirm New Password</label>
                        <input type="password" id="confirm-password" name="confirm_password" placeholder="Re-enter password">
                   </div>
                </div>

                <button type="submit" id="submit-btn" class="btn-action">Next</button>

                <div class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const resetForm = document.getElementById('resetForm');
        const emailSection = document.getElementById('email-section');
        const passwordSection = document.getElementById('password-section');
        const submitBtn = document.getElementById('submit-btn');
        const instructionText = document.getElementById('instruction-text');
        const phpStep = document.getElementById('php-step');
        const hiddenEmail = document.getElementById('hidden-email');
        const emailInput = document.getElementById('email');

        let currentStep = 1;
        
        resetForm.addEventListener('submit', function(event) {
            if (currentStep === 1) {
                event.preventDefault();

                hiddenEmail.value = emailInput.value;

                emailSection.style.display = 'none';
                passwordSection.style.display = 'block';
            
                instructionText.innerHTML = '<span style="color: #710349; font-weight: bold;">Account found.</span> Please enter your new password.';
                
                setTimeout(() => {
                    passwordSection.style.opacity = '1';
                    passwordSection.style.transform = 'translateY(0)';
                }, 50);

                document.getElementById('new-password').required = true;
                document.getElementById('confirm-password').required = true;

                submitBtn.innerText = 'Reset Password';

                phpStep.value = '2';
                currentStep = 2;

            } else if (currentStep === 2) {
                const newPassword = document.getElementById('new-password').value;
                const confirmPassword = document.getElementById('confirm-password').value;

                if (newPassword !== confirmPassword) {
                    event.preventDefault();
                    alert('Passwords do not match! Please check again.');
                }
            }
        });
    </script>
</body>
</html>