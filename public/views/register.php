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
    <title>AutoFlow - Sign up</title>
    <link rel="icon" href="public/images/AutoFlowFavicon.png" type="image/x-icon">
    <link href="public/styles/login.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Krona+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jeju+Gothic&display=swap" rel="stylesheet">


</head>
<body id="register-page">
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
                    <li class="menu-item"><a href="/reports">Reports</a></li>
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
                <form id="register-form" action="register" method="POST">
                    
                    <div id="mobile-icon-div">
                        <img id="mobile-icon" src="public/images/AutoFlowLogo.png" alt="AutoFlow - Logo">
                    </div>
                    <div class="messages">
                        <?php if (isset($messages)){
                            foreach($messages as $message){
                                echo $message;
                            }
                        }

                        ?>
                    </div>
                    <p>Already have an account?<a href="/login">Log In.</a></p>
                    <input class="form" name="name" type="text" placeholder="Enter your name" required>
                    <input class="form" name="surname" type="text" placeholder="Enter your surname" required>
                    <input class="form" name="email" type="text" placeholder="Enter your email" required>
                    <input class="form" name="password" type="password" placeholder="Enter your password" required>
                    <input class="form" name="confirmedPassword" type="password" placeholder="Confirm password">
                    
                    <button class="form" type="submit">Sign up</button>
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