function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu-container');
    mobileMenu.classList.toggle('active'); 
    document.body.classList.toggle('menu-open'); 
}


function populateVehiclesTable() {
    const vehicles = [
      { id: 1, brand: "Skoda", model: "Fabia", reg: "KR 4FM11", status: "green" },
      { id: 2, brand: "Skoda", model: "Roomster", reg: "KR 4FM12", status: "green" },
      { id: 3, brand: "Skoda", model: "Octavia", reg: "KR 4FM13", status: "red" },

    ];
  
    const tableBody = document.querySelector("#vehicles-table tbody");
    if (tableBody) {
        vehicles.forEach(vehicle => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${vehicle.id}</td>
                <td>${vehicle.brand}</td>
                <td>${vehicle.model}</td>
                <td>${vehicle.reg}</td>
                <td><span class="status-dot-table status-${vehicle.status}"></span></td>
            `;
            tableBody.appendChild(row);
        });
    }
}


function populateDriversTable() {
    const drivers = [
        { id: 1, name: "Jan", surname: "Kowalski", phone: "123 456 789", status: "green" },
        { id: 2, name: "Anna", surname: "Nowak", phone: "987 654 321", status: "red" },

    ];

    const tableBody = document.querySelector("#drivers-table tbody");
    if (tableBody) {
        drivers.forEach(driver => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${driver.id}</td>
                <td>${driver.name}</td>
                <td>${driver.surname}</td>
                <td>${driver.phone}</td>
                <td><span class="status-dot-table status-${driver.status}"></span></td>
            `;
            tableBody.appendChild(row);
        });
    }
}


document.addEventListener("DOMContentLoaded", () => {

    if (document.getElementById('vehicles-page')) {
        populateVehiclesTable();
    }
    
    if (document.getElementById('drivers-page')) {
        populateDriversTable();
    }
});