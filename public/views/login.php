<?php
$isLoggedIn = isset($_SESSION['user']);
$name = $isLoggedIn ? $_SESSION['user']['name'] : null;
$firstNameLetter = $name ? strtoupper(substr($name, 0, 1)) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoFlow - Log in</title>
    <link rel="icon" href="public/images/AutoFlowFavicon.png" type="image/x-icon">
    <link href="public/styles/login.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Krona+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jeju+Gothic&display=swap" rel="stylesheet">


</head>
<body id="login-page">
    <header class = "site-header">
        <div class="header-container">
            <div class="logo-portal-name-section">
                <div class="logo-section">
                    <img id="logo"src="public/images/AutoFlowLogo.png" alt="AutoFlow - Logo">
                </div>
                <div class="portal-name">AutoFlow</div>
            </div>
            <div class="menu-section">
                <ul class="menu">
                    <li class="menu-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="menu-item"><a href="/vehicles">Vehicles</a></li>
                    <li class="menu-item"><a href="/drivers">Drivers</a></li>
                    <li class="menu-item"><a href="/map">Map</a></li>
                    <li class="menu-item">Reports*</li>
                </ul>
            </div>
            <div class="profile-section">

            <div class="profile-image">
                <?php if ($isLoggedIn): ?>
                    <div class="avatar-circle"><?= $firstNameLetter ?></div>
                <?php else: ?>
                    <img id="question" src="public/images/profile-image-not-logged-in.png">
                <?php endif; ?>
            </div>
            <div class="profile-name">
                <?php if ($isLoggedIn): ?>
                    <a href="/logout"><span>Log out</span></a>
                <?php else: ?>
                    <a href="/login"><span>Log in</span></a>
                <?php endif; ?>
            </div>

            </div>
        </div>
    </header>
    <main class="main-cointainer">
        <div id="left-section">
            <div class="form-parent">
                <h1>AutoFlow</h1>
                <form id="login-form" action="login" method="POST">
                    
                    <div id="mobile-icon-div">
                        <img id="mobile-icon" src="public/images/AutoFlowLogo.png" alt="AutoFlow - Logo">
                    </div>
                    <div class="messages">
                        <?php 
                        if (isset($messages)) {
                            foreach ($messages as $message) {
                                echo $message;
                            }
                        }


                        if (isset($_SESSION['error_message'])) {
                            echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']);
                        }
                        ?>
                    </div>
                    <p>Don't have an account?<a href="/register">Sign Up.</a></p>
                    <input name="email" type="text" placeholder="Enter your email">
                    <input name="password" type="password" placeholder="Enter your password">
                    <a href="#" class="forgot-password">Forgot password?</a>
                    <button type="submit">Log in</button>
                </form>
        
            </div>
        </div>
        <div id="right-section">
            <div class="blank-rectangle">
                <p class="experience-autoflow">Experience efficient fleet management with AutoFlow today!</p>
                <div class="image-frame">
                    <img class="example-dashboard"src="public/images/example-dashboard.png" alt="Example of AutoFlow User Interface">
                </div>
            </div>
        </div>
    </main>
</body>
</html>