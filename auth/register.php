<?php
include("../database.php");

$error = '';
$success = '';
if($_POST){
    $name=$_POST['name'];
    $email=$_POST['email'];
    $password=password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email exists
    $check = $conn->users->findOne(['email' => $email]);
    if($check) {
        $error = 'Email already registered!';
    } else {
        $insert = $conn->users->insertOne([
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);
        if($insert->getInsertedCount() > 0){
            header("Location: login.php");
            exit;
        } else {
            $error = 'Registration failed. Try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | SmartGuide AI</title>
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
            max-width: 440px;
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
            animation: scan 5s linear infinite;
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
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 800;
            background: linear-gradient(to right, #fff, var(--primary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p.subtitle {
            font-size: 14px;
            color: #ccc;
            margin-bottom: 30px;
            font-weight: 300;
        }

        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group input {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s;
            outline: none;
        }
        .input-group input:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 20px rgba(0,247,255,0.1);
        }

        button {
            width: 100%;
            padding: 16px;
            background: #fff;
            color: #000;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        button:hover {
            background: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 247, 255, 0.4);
        }

        .links { margin-top: 30px; font-size: 14px; color: #ccc; }
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
            opacity: 0.8; 
            transition: 0.3s; 
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .back-home:hover { opacity: 1; transform: translateX(-5px); }

        .cursor-dot {
            position: fixed;
            top: 0; left: 0;
            width: 8px; height: 8px;
            background: var(--primary);
            pointer-events: none;
            z-index: 9999;
            clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
            box-shadow: 0 0 10px var(--primary);
            opacity: 0;
            margin-top: -4px; margin-left: -4px;
        }
    </style>
</head>
<body>
    <div id="canvas-container"></div>
    <a href="../index.php" class="back-home">← Return Home</a>

    <div class="login-box">
        <h2>Register Identity</h2>
        <p class="subtitle">Join the SmartGuide AI Global Intelligence Network</p>

        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <input name="name" type="text" placeholder="Full Designation (Name)" required autocomplete="off">
            </div>
            <div class="input-group">
                <input name="email" type="email" placeholder="Email Reference" required autocomplete="off">
            </div>
            <div class="input-group">
                <input name="password" type="password" placeholder="Define Key Phrase" required>
            </div>
            <button type="submit">Establish Link</button>
        </form>

        <div class="links">
            <p>Already identified? <a href="login.php">Login Identity</a></p>
        </div>
    </div>

    <script src="../assets/js/cursor.js"></script>
    <script src="../assets/js/auth-3d.js"></script>
</body>
</html>