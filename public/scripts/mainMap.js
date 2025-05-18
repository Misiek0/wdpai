document.addEventListener('DOMContentLoaded', () => {
    // map init
    const map = L.map('leaflet-map').setView([52.237049, 19.017532], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap'
    }).addTo(map);
  
    //marker + list addition
    mapVehicles.forEach(vehicle => {

      const iconFile = mapIcons[Math.floor(Math.random() * mapIcons.length)];
      const carIcon = L.icon({
        iconUrl: `/public/images/${iconFile}`,
        iconSize:   [36, 36],
        iconAnchor: [16, 32],
        popupAnchor:[0, -32]
      });

      L.marker([vehicle.current_latitude, vehicle.current_longitude], { icon: carIcon })
       .addTo(map)
       .bindPopup(`<b>${vehicle.brand} ${vehicle.model}</b><br>${vehicle.reg_number}<br>Vehicle ID: ${vehicle.id}`)
  
      // right list position
      const li = document.createElement('li');
      li.classList.add('list-item');
      li.innerHTML = `
        <img
          src="/public/images/${iconFile}"
          class="vehicle-icon"
          alt="Vehicle icon"
        />
        <span>${vehicle.reg_number}</span>
        <span>ID: ${vehicle.id}</span>
      `;
      document.getElementById('vehicle-list').appendChild(li);
    });
  });
  