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
    <title>AutoFlow - Map</title>
    <link rel="icon" href="public/images/AutoFlowFavicon.png" type="image/x-icon">
    <link href="public/styles/map.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Krona+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jeju+Gothic&display=swap" rel="stylesheet">
    <script src="public/scripts/app.js" defer></script>
</head>
<body id="drivers-page">
    <header class="site-header">
        <div class="header-container">
            <div class="logo-portal-name-section">
                <div class="logo-section">
                    <img id="logo" src="public/images/AutoFlowLogo.png" alt="AutoFlow - Logo">
                    <img id="burger-menu" src="public/images/burger_menu.png" alt="menu" onclick=toggleMobileMenu()>
                </div>
                <div class="portal-name">AutoFlow</div>
            </div>
            <div class="menu-section">
                <ul class="menu">
                    <li class="menu-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="menu-item"><a href="/vehicles">Vehicles</a></li>
                    <li class="menu-item"><a href="/drivers">Drivers</a></li>
                    <li class="menu-item-active"><a href="/map">Map</a></li>
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
    <div id="mobile-menu-container" class="mobile-menu-container">
        <ul class="menu" id="mobile-menu">
            <li class="mobile-menu-item"><a href="/dashboard">Dashboard</a></li>
            <li class="mobile-menu-item"><a href="/vehicles">Vehicles</a></li>
            <li class="mobile-menu-item"><a href="/drivers">Drivers</a></li>
            <li class="mobile-menu-item-active"><a href="/map">Map</a></li>
            <li class="mobile-menu-item"><a href="/reports">Reports</a></li>
        </ul>
    </div>
    <main>
        <div id="map-container" class="grid-container">
            <iframe id="map-api" 
                src="https://maps.google.com/maps?q=Krakow&output=embed"
                frameborder="0"
                style="width:100%;height:100%">
            </iframe>
        </div>
        <div id="list-container" class="grid-container">
            <div class="list-item">
                <img class="vehicle-icon"src="public/images/car_pointer_black.png">
                <span>KR4FM14</span>
                <span>Wieliczka</span>
                
            </div>
            <div class="list-item">
                <img class="vehicle-icon"src="public/images/car_pointer_black.png">
                <span>KR4FM14</span>
                <span>Wieliczka</span>
                
            </div>
            <div class="list-item">
                <img class="vehicle-icon"src="public/images/car_pointer_black.png">
                <span>KR4FM14</span>
                <span>Wieliczka</span>
                
            </div>


        </div>

    </main>
</body>
</html>