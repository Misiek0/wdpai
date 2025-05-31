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
    <title>AutoFlow - Dashboard</title>
    <link rel="icon" href="public/images/AutoFlowFavicon.png" type="image/x-icon">
    <link href="public/styles/dashboard.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Krona+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Jeju+Gothic&display=swap" rel="stylesheet">
    <script src="public/scripts/dynamicMenu.js" defer></script>
    <script src="public/scripts/premiumTooltip.js" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

</head>
<body id="dashboard-page">
    <header class = "site-header">
        <div class="header-container">
            <div class="logo-portal-name-section">
                <div class="logo-section">
                    <img id="logo"src="public/images/AutoFlowLogo.png" alt="AutoFlow - Logo">
                    <img id="burger-menu" src="public/images/burger_menu.png" alt="menu" onclick=toggleMobileMenu()>
                </div>
                <div class="portal-name">AutoFlow</div>
            </div>
            <div class="menu-section">
                <ul class="menu">
                    <li class="menu-item-active"><a href="/dashboard">Dashboard</a></li>
                    <li class="menu-item"><a href="/vehicles">Vehicles</a></li>
                    <li class="menu-item"><a href="/drivers">Drivers</a></li>
                    <li class="menu-item"><a href="/map">Map</a></li>
                    <li class="menu-item menu-reports">Reports*</li>
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
            <li class="mobile-menu-item-active"><a href="/dashboard">Dashboard</a></li>
            <li class="mobile-menu-item"><a href="/vehicles">Vehicles</a></li>
            <li class="mobile-menu-item"><a href="/drivers">Drivers</a></li>
            <li class="mobile-menu-item"><a href="/map">Map</a></li>
            <li class="mobile-menu-item menu-reports" tabindex="0">Reports*</li>
        </ul>
    </div>
    <main>


        <div id="summary" class="grid-container">
            <h1>Summary</h1>
            <div class="inside-grid-container">
                <p>Vehicles</p>
                <div class="data-box">
                    <div class="vehicles-data-labels"> 
                        <div class="label">
                        <span>Total number of vehicles</span>
                        </div>
                        <div class="label">
                            <div class="status-dot available"></div>
                            <span>Available</span>
                        </div>
                        <div class="label">
                            <div class="status-dot on-the-road"></div>
                            <span>On the road</span>
                        </div>
                        <div class="label">
                            <div class="status-dot in-service"></div>
                            <span>In service</span>
                        </div>
                    </div>
                  
                    <div class="vehicles-data-values"> 
                      <div class="value" id="vehicles-total-number"><?= $vehiclesStats['total_vehicles'] ?? 0 ?></div>
                      <div class="value" id="vehicles-available"><?= $vehiclesStats['available'] ?? 0 ?></div>
                      <div class="value" id="vehicles-on-the-road"><?= $vehiclesStats['on_the_road'] ?? 0 ?></div>
                      <div class="value" id="vehicles-in-service"><?= $vehiclesStats['in_service'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
            <div class="inside-grid-container">
                <p>Drivers</p>
                <div class="data-box">
                    <div class="drivers-data-labels"> 
                        <div class="label">
                        <span>Total number of drivers</span>
                        </div>
                        <div class="label">
                            <div class="status-dot available"></div>
                            <span>Available</span>
                        </div>
                        <div class="label">
                            <div class="status-dot on-the-road"></div>
                            <span>On the road</span>
                        </div>
                        <div class="label">
                            <div class="status-dot on-leave"></div>
                            <span>On leave</span>
                        </div>
                    </div>
                  
                    <div class="vehicles-data-values"> 
                      <div class="value" id="vehicles-total-number"><?= $driversStats['total_drivers'] ?? 0 ?></div>
                      <div class="value" id="vehicles-available"><?= $driversStats['available'] ?? 0 ?></div>
                      <div class="value" id="vehicles-on-the-road"><?= $driversStats['on_the_road'] ?? 0 ?></div>
                      <div class="value" id="vehicles-on-leave"><?= $driversStats['on_leave'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>


        <div id="notifications" class="grid-container">
            <h1>Notifications</h1>
            <div  class="inside-grid-container">
                <img class="unseen" src="public/images/unread_mail.png">
                <div class="message">03.03.2025 11:12:00 Upcoming service deadline for vehicle #7</div>
            </div>
            <div  class="inside-grid-container">
                <img class="seen" src="public/images/read_mail.png">
                <div class="message">01.03.2025 10:30:00 A fault registered for Vehicle #2</div>
            </div>
        </div>


        <div id="statistics" class="grid-container">
            <h1>Statistics</h1>
            <div class="inside-grid-container">
                <p>Fuel consumption</p>
                <div class="data-box">
                    <div class="statistics-fuel-data"> 
                        <div class="label">
                        <span>Total fuel consumption</span>
                        </div>
                    <div class="statistics-fuel-data">
                        <span>Average fuel consumption</span>
                    </div>
                </div>
                <div class="fuel-consumption-data-values"> 
                    <div class="value" id="total-fuel-consumption"><?= $vehiclesStats['total_fuel'] ?? 0 ?> l/km</div>
                    <div class="value" id="average-fuel-consumption"><?= $vehiclesStats['avg_fuel'] ?? 0 ?> l/km</div>
                </div>
                </div>
            </div>
            <div class="inside-grid-container">
                    <p>Fleet mileage</p>
                    <div class="data-box">
                        <div class="statistics-fuel-data"> 
                            <div class="label">
                            <span>Total fleet mileage</span>
                            </div>
                            <div class="statistics-fuel-data">
                                <span>Average vehicle mileage</span>
                            </div>
                        </div>
                        <div class="fuel-consumption-data-values"> 
                            <div class="value" id="total-fleet-mileage"><?= $vehiclesStats['total_mileage'] ?? 0 ?> km</div>
                            <div class="value" id="average-fleet-mileage"><?= $vehiclesStats['avg_mileage'] ?? 0 ?> km</div>
                        </div>
                    </div>
                </div>
                <button>View details*</button>
            </div>
        </div>



        <div id="map" class="grid-container">
        <h1>Map</h1>
            <div class="map-cars">
                <div class="map-container">
                    <div id="leaflet-map"></div>
                </div>
                <?php
                // przygotuj dane PHP → JS
                $vehicles = array_map(fn($vehicle) => [
                    'id'                => $vehicle->getId(),
                    'brand'             => $vehicle->getBrand(),
                    'model'             => $vehicle->getModel(),
                    'reg_number'        => $vehicle->getRegNr(),
                    'current_latitude'  => $vehicle->getCurrentLatitude(),
                    'current_longitude' => $vehicle->getCurrentLongitude()
                ], $vehicles);
                $icons = ['car_pointer_black.png','car_pointer_blue.png','car_pointer_bronze.png','car_pointer_red.png','car_pointer_green.png'];
                ?>
                <script>
                window.dashboardVehicles = <?= json_encode($vehicles, JSON_NUMERIC_CHECK) ?>;
                window.dashboardIcons    = <?= json_encode($icons) ?>;
                </script>
                    <script src="/public/scripts/dashboardMap.js" defer></script>

                <div class="cars">
                    <h1 id="h1VehicleList">Vehicle list</h1>
                    <ul id="vehicle-list"></ul>
                </div>
            </div>
        </div>

    </main>
<div id="premium-tooltip" class="tooltip-modal">Available in premium version</div>
</body>


</html>