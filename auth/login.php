<?php
session_start();
include("../database.php");

$error = '';
if($_POST){
    $role = $_POST['role'] ?? 'user';
    $identifier = $_POST['identifier'];
    $password = $_POST['password'];

    if($role === 'admin'){
        // Admin login
        $admin = $conn->admins->findOne(['username' => $identifier]);
        
        $verify = false;
        if($admin) {
            $verify = password_verify($password, $admin['password']);
        }
        
        if($admin && $verify){
            $_SESSION['admin_id'] = (string)$admin['_id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['user'] = $admin['username'];
            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            $error = 'Invalid admin credentials!';
        }
    } else {
        // User login
        $user = $conn->users->findOne(['email' => $identifier]);
        
        $verify = false;
        if($user) {
            $verify = password_verify($password, $user['password']);
        }
        
        if($user && $verify){
            $_SESSION['user'] = $identifier;
            header("Location: ../dashboard.php");
            exit;
        } else {
            // Check for legacy md5 password as fallback (optional but safer for migration)
            if($user && $user['password'] === md5($password)) {
                // Update to new hash format
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $conn->users->updateOne(['_id' => $user['_id']], ['$set' => ['password' => $new_hash]]);
                $_SESSION['user'] = $identifier;
                header("Location: ../dashboard.php");
                exit;
            }
            $error = 'Invalid email or password!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SmartGuide AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <style>
        :root {
            --primary: #00f7ff;
            --border: rgba(255, 255, 255, 0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
            color: #fff;
            overflow: hidden;
            position: relative;
        }

        #canvas-container {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 1;
        }

        .login-box {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border: 1px solid var(--border);
            border-radius: 30px;
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
            z-index: 2;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* AI Scanning Line Animation */
        .login-box::before {
            content: "";
            position: absolute;
            top: -100%;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, transparent, var(--primary), transparent);
            box-shadow: 0 0 15px var(--primary);
            animation: scan 4s linear infinite;
            z-index: 3;
            opacity: 0.5;
        }

        @keyframes scan {
            0% { top: -10%; }
            50% { top: 110%; }
            100% { top: -10%; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            font-size: 30px;
            margin-bottom: 8px;
            font-weight: 800;
            background: linear-gradient(to right, #fff, var(--primary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p.subtitle {
            font-size: 13px;
            color: #aaa;
            margin-bottom: 28px;
            font-weight: 300;
        }

        /* Role Selector Toggle */
        .role-toggle {
            display: flex;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 5px;
            margin-bottom: 28px;
            gap: 5px;
        }

        .role-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: #888;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .role-btn .role-icon {
            font-size: 18px;
        }

        .role-btn.active {
            background: var(--primary);
            color: #000;
            box-shadow: 0 4px 20px rgba(0, 247, 255, 0.35);
            transform: scale(1.02);
        }

        .role-btn:not(.active):hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }

        .input-group { margin-bottom: 18px; text-align: left; }
        .input-group label {
            display: block;
            font-size: 11px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .input-group input {
            width: 100%;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            transition: all 0.3s;
            outline: none;
        }
        .input-group input:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 20px rgba(0,247,255,0.1);
        }
        .input-group input::placeholder { color: rgba(255,255,255,0.25); }

        /* Password Eye Toggle */
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 52px;
        }
        .eye-btn {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: rgba(255,255,255,0.35);
            font-size: 18px;
            line-height: 1;
            transition: color 0.2s;
            display: flex;
            align-items: center;
        }
        .eye-btn:hover { color: var(--primary); }

        .error-msg {
            background: rgba(255, 80, 80, 0.1);
            border: 1px solid rgba(255, 80, 80, 0.3);
            color: #ff6b6b;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 18px;
            text-align: left;
        }

        button[type="submit"] {
            width: 100%;
            padding: 16px;
            background: #fff;
            color: #000;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        button[type="submit"]:hover {
            background: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 247, 255, 0.4);
        }

        .links { margin-top: 28px; font-size: 13px; color: #666; }
        .links a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .links a:hover { text-decoration: underline; }

        .back-home { 
            position: absolute; 
            top: 30px; 
            left: 30px; 
            color: #fff; 
            text-decoration: none; 
            font-size: 14px; 
            font-weight: 600;
            opacity: 0.7; 
            transition: 0.3s; 
            z-index: 10;
        }
        .back-home:hover { opacity: 1; transform: translateX(-5px); }

        /* Admin-specific label accent */
        .admin-mode label { color: #00f7ff99; }
        .admin-mode input:focus {
            border-color: var(--primary);
        }
    </style>
</head>
<body>
    <div id="canvas-container"></div>
    <a href="../index.php" class="back-home">← Return Home</a>

    <div class="login-box" id="loginBox">
        <h2>Welcome Back</h2>
        <p class="subtitle">Access your SmartGuide AI account</p>

        <!-- Role Toggle -->
        <div class="role-toggle">
            <button type="button" class="role-btn active" id="btnUser" onclick="setRole('user')">
                <span class="role-icon">🧭</span> User
            </button>
            <button type="button" class="role-btn" id="btnAdmin" onclick="setRole('admin')">
                <span class="role-icon">🛡️</span> Admin
            </button>
        </div>

        <?php if($error): ?>
            <div class="error-msg">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <input type="hidden" name="role" id="roleInput" value="user">

            <div class="input-group">
                <label id="identifierLabel">Email Address</label>
                <input name="identifier" type="text" id="identifierInput" placeholder="Enter your email" required autocomplete="off">
            </div>
            <div class="input-group">
                <label>Password</label>
                <div class="password-wrapper">
                    <input name="password" type="password" id="passwordInput" placeholder="Enter your password" required>
                    <button type="button" class="eye-btn" id="eyeBtn" onclick="togglePassword()" title="Show/Hide Password">
                        <span id="eyeIcon">👁️</span>
                    </button>
                </div>
            </div>
            <button type="submit" id="submitBtn">Sign In as User</button>
        </form>

        <div class="links" id="registerLink">
            <p>New here? <a href="register.php">Create an account</a></p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = '🙈';
            } else {
                input.type = 'password';
                icon.textContent = '👁️';
            }
        }

        function setRole(role) {
            const btnUser = document.getElementById('btnUser');
            const btnAdmin = document.getElementById('btnAdmin');
            const roleInput = document.getElementById('roleInput');
            const identifierLabel = document.getElementById('identifierLabel');
            const identifierInput = document.getElementById('identifierInput');
            const submitBtn = document.getElementById('submitBtn');
            const registerLink = document.getElementById('registerLink');
            const loginBox = document.getElementById('loginBox');

            roleInput.value = role;

            if(role === 'admin') {
                btnAdmin.classList.add('active');
                btnUser.classList.remove('active');
                identifierLabel.textContent = 'Admin Username';
                identifierInput.placeholder = 'Enter admin username';
                submitBtn.textContent = 'Sign In as Admin';
                registerLink.style.display = 'none';
                loginBox.classList.add('admin-mode');
            } else {
                btnUser.classList.add('active');
                btnAdmin.classList.remove('active');
                identifierLabel.textContent = 'Email Address';
                identifierInput.placeholder = 'Enter your email';
                submitBtn.textContent = 'Sign In as User';
                registerLink.style.display = 'block';
                loginBox.classList.remove('admin-mode');
            }

            // Refocus input
            identifierInput.focus();
        }

        // Preserve role on page reload if there was an error
        <?php if(isset($_POST['role'])): ?>
        setRole('<?php echo htmlspecialchars($_POST['role']); ?>');
        <?php endif; ?>
    </script>

    <script src="../assets/js/cursor.js"></script>
    <script src="../assets/js/auth-3d.js"></script>
</body>
</html>