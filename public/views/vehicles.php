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
    <title>AutoFlow - Vehicles</title>
    <link rel="icon" href="public/images/AutoFlowFavicon.png" type="image/x-icon">
    <link href="public/styles/vehicles.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Krona+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jeju+Gothic&display=swap" rel="stylesheet">
    <script src="public/scripts/app.js" defer></script>
</head>
<body id="vehicles-page">
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
                    <li class="menu-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="menu-item-active"><a href="/vehicles">Vehicles</a></li>
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
    <div id="mobile-menu-container" class="mobile-menu-container">
        <ul class="menu" id="mobile-menu">
            <li class="mobile-menu-item"><a href="/dashboard">Dashboard</a></li>
            <li class="mobile-menu-item-active"><a href="/vehicles">Vehicles</a></li>
            <li class="mobile-menu-item"><a href="/drivers">Drivers</a></li>
            <li class="mobile-menu-item"><a href="/map">Map</a></li>
            <li class="mobile-menu-item"><a href="/reports">Reports</a></li>
        </ul>
    </div>
    <main>
        <div id="buttons">
            <button id="add-button"><img class="icon-add-remove" src="public/images/add_plus_white.png">Add vehicle</button>
            <button id="remove-button"><img class="icon-add-remove" src="public/images/remove_minus_white.png">Remove vehicle</button>
        </div>
        <div id="vehicle-summary" class="grid-container">
            <div id="inside-grid-container-vehicle-summary" class="inside-grid-container">
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
                      <div class="value" id="vehicles-total-number">1</div>
                      <div class="value" id="vehicles-available">1</div>
                      <div class="value" id="vehicles-on-the-road">1</div>
                      <div class="value" id="vehicles-in-service">1</div>
                    </div>
                </div>
            </div>
        </div>
        <div id="vehicle-list" class="grid-container">
            <h1>List of vehicles</h1>
            <div id ="inside-grid-container-vehicle-list" class="inside-grid-container">
                <table id="vehicles-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Reg. Nr.</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Rows will be added dynamically -->
                    </tbody>
                  </table>
            </div>
            </div>
        </div>
        <div id="vehicle-information" class="grid-container">
            <h1>Information</h1>
            <div id="inside-grid-container-vehicle-information" class="inside-grid-container">
                <div id="vehicle-information-data-labels-values-image">
                    <div id="vehicle-information-data-labels" class="vehicle-information-data-labels-values">
                        <span class="info-label">Brand:</span>
                        <span class="info-label">Model:</span>
                        <span class="info-label">Reg. Nr.:</span>
                        <span class="info-label">Mileage:</span>
                        <span class="info-label">Vehicle inspection:</span>
                        <span class="info-label">OC/AC:</span>
                        <span class="info-label">VIN:</span>
                        <span class="info-label">Avg. fuel consumption:</span>
                        <span class="info-label">Current state:</span>
                        <span class="info-label">Current localization:</span>
                        <span class="info-label">Assigned driver:</span>
                    </div>
                    <div id="vehicle-information-data-values" class="vehicle-information-data-labels-values">
                        <span id="car-brand" class="value">Skoda</span>
                        <span id="car-model" class="value">Fabia</span>
                        <span id="car-reg-nr" class="value">KR 4FM11</span>
                        <span id="car-mileage" class="value">120 000 km</span>
                        <div id="vahicle-inspection" class="value">
                            <span>valid</span>
                            <span class="validity-date">(until 23.06.2025)</span>
                        </div>
                        <div id="car-oc-ac" class="value">
                            <span>valid</span>
                            <span class="validity-date">(until 07.04.2025)</span>
                        </div>
                        <span id="car-vin" class="value">1HGCM82633A123456</span>
                        <span id="car-avg-fuel-consumption" class="value">6.5 l</span>
                        <div id="car-current-state" class="value">
                            <div class="status-dot available"></div>
                            <span>Available</span>
                        </div>
                        <span id="car-current-localization" class="value">Newag, Nowy Sącz, Poland</span>
                        <span id="car-assigned-driver" class="value">Jan Kowalski</span>
                    </div>
                    <div id="vehicle-image" class="inside-grid-container">
                        <img id="car-picture"src="public/images/fabia.png">
                    </div>
                </div>
                
                    <div class="map-container" class="inside-grid-container">
                        <iframe id="map-api" 
                        src="https://maps.google.com/maps?q=Krakow&output=embed"
                        frameborder="0"
                        style="width:100%;height:100%">
                        </iframe>
                    </div>
                    <div id="information-buttons">
                        <button id="service-history-button" class="information-button"><img class="download-icon" src="public/images/download_icon_white.png">Service History</button>
                        <button id="gps-history-button" class="information-button"><img class="download-icon" src="public/images/download_icon_white.png">GPS History</button>
                    </div>
                
            </div>



            </div>
        </div>
    </main>
</body>
</html>