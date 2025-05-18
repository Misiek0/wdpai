document.addEventListener('DOMContentLoaded', () => {
  // map init
  const map = L.map('leaflet-map').setView([52.237049, 21.017532], 6);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
  }).addTo(map);

  // data from dashboard.php
  dashboardVehicles.forEach(vehicle => {
    const iconFile = dashboardIcons[Math.floor(Math.random() * dashboardIcons.length)];
    const carIcon = L.icon({
      iconUrl: `/public/images/${iconFile}`,
      iconSize:   [32, 32],
      iconAnchor: [16, 32],
      popupAnchor:[0, -32]
    });
    L.marker([vehicle.current_latitude, vehicle.current_longitude], { icon: carIcon })
     .addTo(map)
     .bindPopup(`<b>${vehicle.brand} ${vehicle.model}</b><br>${vehicle.reg_number}<br>Vehicle ID: ${vehicle.id}`)

     const li = document.createElement('li');
     li.innerHTML = `
       <img
         src="/public/images/${iconFile}"
         class="list-icon"
         alt="Car icon"
       />
       Vehicle #${vehicle.id}
     `;
     document.getElementById('vehicle-list').appendChild(li);
  });
});
