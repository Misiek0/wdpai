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
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <script src="public/scripts/dynamicMenu.js" defer></script>
    <script src="public/scripts/vehicles.js" defer></script>
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
                            <div class="status-dot on_road"></div>
                            <span>On the road</span>
                        </div>
                        <div class="label">
                            <div class="status-dot in_service"></div>
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
                        <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><?= $vehicle->getId() ?></td>
                            <td><?= htmlspecialchars($vehicle->getBrand()) ?></td>
                            <td><?= htmlspecialchars($vehicle->getModel()) ?></td>
                            <td><?= htmlspecialchars($vehicle->getRegNr()) ?></td>
                            <td>
                                <div class="status-dot-table <?= htmlspecialchars($vehicle->getStatus()) ?>"></div>
                                
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                  </table>
            </div>
            </div>
        </div>
        
        <div id="vehicle-information" class="grid-container">
            <h1>Information</h1>
            <div id="inside-grid-container-vehicle-information" class="inside-grid-container">
                <div id="no-vehicle-selected" class="no-vehicle-selected">
                    <span>Choose vehicle</span>
                </div>
                <div id="vehicle-details-container" style="display: none;">
                    <!-- Dynamic data from fetch api-->
                </div>
            </div>
        </div> 

    </main>
    <div id="add-vehicle-popup" class="grid-container">
    <h1>Add New Vehicle</h1>
        <form id="add-vehicle-form" action="addVehicle" method="POST" ENCTYPE="multipart/form-data">
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
            <input name="brand" type="text" placeholder="Enter brand">
            <input name="model" type="text" placeholder="Enter model">
            <input name="reg_number" type="text" placeholder="Enter Registration Number">
            <input name="mileage" type="number" placeholder="Enter mileage">
            <Label>Vehicle inspection expires: <input name="vehicle_inspection_expiry" type="date"></label>
            <Label>Vehicle OC/AC expires: <input name="oc_ac_expiry" type="date"></label>
            <input name="vin" type="text" placeholder="Enter VIN">
            <label>Vehicle photo: <input name="file" type="file"></label>
            <div id="buttons">
            <button id="inside-form-add-vehicle" type="submit">Add vehicle</button>
            <button id="inside-form-cancel" type="button">Cancel</button>
            </div>
        </form>
        
    </div>
</body>
</html>