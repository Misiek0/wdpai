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
    <script src="public/scripts/dynamicMenu.js" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</head>
<body id="map-page">
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
            <div id="leaflet-map"></div>
        </div>
        <?php
        
        $icons = ['car_pointer_black.png','car_pointer_blue.png','car_pointer_bronze.png','car_pointer_red.png','car_pointer_green.png'];
        ?>
        <script>
            window.mapVehicles = <?= json_encode($vehicles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            window.mapIcons    = <?= json_encode($icons) ?>;
        </script>
        <script src="/public/scripts/mainMap.js" defer></script>

        <!-- Lista pojazdów obok mapy -->
        <div id="list-container" class="grid-container">
            <ul id="vehicle-list"></ul>
        </div>
    </main>
</body>
</html>