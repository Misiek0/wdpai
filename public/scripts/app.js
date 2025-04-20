function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu-container');
    mobileMenu.classList.toggle('active'); 
    document.body.classList.toggle('menu-open'); 
}


document.addEventListener("DOMContentLoaded", () => {
    const vehicles = [
      { id: 1, brand: "Skoda", model: "Fabia", reg: "KR 4FM11", status: "green" },
      { id: 2, brand: "Skoda", model: "Roomster", reg: "KR 4FM12", status: "green" },
      { id: 3, brand: "Skoda", model: "Octavia", reg: "KR 4FM13", status: "red" },
      { id: 4, brand: "Audi", model: "A4", reg: "KR 4FM14", status: "red" },
      { id: 5, brand: "Audi", model: "A5", reg: "KK 11R23", status: "red" },
      { id: 6, brand: "Skoda", model: "Octavia", reg: "KK 11R24", status: "red" },
      { id: 7, brand: "Skoda", model: "Octavia", reg: "KR 4FM17", status: "green" },
      { id: 8, brand: "Audi", model: "Q7", reg: "KR 4FM15", status: "red" },
      { id: 9, brand: "Audi", model: "Q7", reg: "KR 4FM16", status: "red" },
      { id: 10, brand: "Skoda", model: "Fabia", reg: "KR 4FM10", status: "black" },
      { id: 11, brand: "Audi", model: "A4", reg: "KK 11R25", status: "green" },
      { id: 12, brand: "Audi", model: "A4", reg: "KK 11R25", status: "red" },
    ];
  
    const tableBody = document.querySelector("#vehicles-table tbody");
  
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
  });
