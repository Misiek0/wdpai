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
    <title>AutoFlow - Drivers</title>
    <link rel="icon" href="public/images/AutoFlowFavicon.png" type="image/x-icon">
    <link href="public/styles/drivers.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Krona+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jeju+Gothic&display=swap" rel="stylesheet">
    <script src="public/scripts/dynamicMenu.js" defer></script>
    <script src="public/scripts/drivers.js" defer></script>
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
                    <li class="menu-item-active"><a href="/drivers">Drivers</a></li>
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
            <li class="mobile-menu-item"><a href="/vehicles">Vehicles</a></li>
            <li class="mobile-menu-item-active"><a href="/drivers">Drivers</a></li>
            <li class="mobile-menu-item"><a href="/map">Map</a></li>
            <li class="mobile-menu-item"><a href="/reports">Reports</a></li>
        </ul>
    </div>
    <main>
        <div id="buttons">
            <button id="add-button"><img class="icon-add-remove" src="public/images/add_plus_white.png">Add driver</button>
            <button id="remove-button"><img class="icon-add-remove" src="public/images/remove_minus_white.png">Remove driver</button>
        </div>
        <div id="driver-summary" class="grid-container">
            <div id="inside-grid-container-driver-summary" class="inside-grid-container">
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
                            <div class="status-dot on_road"></div>
                            <span>On the road</span>
                        </div>
                        <div class="label">
                            <div class="status-dot on_leave"></div>
                            <span>On leave</span>
                        </div>
                    </div>
                  
                    <div class="drivers-data-values"> 
                        <div class="value" id="drivers-total-number"><?= $driversStats['total_drivers'] ?? 0 ?></div>
                        <div class="value" id="drivers-available"><?= $driversStats['available'] ?? 0 ?></div>
                        <div class="value" id="drivers-on-the-road"><?= $driversStats['on_the_road'] ?? 0 ?></div>
                        <div class="value" id="drivers-on-leave"><?= $driversStats['on_leave'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="driver-list" class="grid-container">
            <h1>List of drivers</h1>
            <div id="inside-grid-container-driver-list" class="inside-grid-container">
                <table id="drivers-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Surname</th>
                            <th>Phone nr.</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be added dynamically -->
                    <?php foreach ($drivers as $driver): ?>
                    <tr>
                        <td><?= $driver->getId() ?></td>
                        <td><?= htmlspecialchars($driver->getName()) ?></td>
                        <td><?= htmlspecialchars($driver->getSurname()) ?></td>
                        <td><?= htmlspecialchars($validator->validatePhoneNumber($driver->getPhone()) ?? 'Invalid number') ?></td>

                        <td>
                            <div class="status-dot-table <?= htmlspecialchars($driver->getDriverStatus()) ?>"></div>
                            
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="driver-information" class="grid-container">
            <h1>Information</h1>
            <div id="inside-grid-container-driver-information" class="inside-grid-container">
                <div id="no-driver-selected" class="no-driver-selected">
                    <span>Choose driver</span>
                </div>
                <div id="driver-details-container" style="display: none;">
                    <!-- Dynamic driver data loaded via JS -->
                </div>
            </div>

        </div>
    </main>
    <div id="add-driver-popup" class="grid-container">
    <h1>Add New Driver</h1>
        <form id="add-driver-form" action="addDriver" method="POST" ENCTYPE="multipart/form-data">
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
            <input name="name" type="text" placeholder="Enter name">
            <input name="surname" type="text" placeholder="Enter surname">
            <input name="phone" type="number" placeholder="Enter phone number">
            <input name="email" type="text" placeholder="Enter email">
            <Label>Driver license expires: <input name="license_expiry" type="date"></label>
            <Label>Medical examination expires: <input name="medical_exam_expiry" type="date"></label>
            <label>Driver photo: <input name="file" type="file"></label>
            <div id="buttons">
            <button id="inside-form-add-driver" type="submit">Add Driver</button>
            <button id="inside-form-cancel" type="button">Cancel</button>
            </div>
        </form>
        
    </div>
</body>
</html>