document.addEventListener('DOMContentLoaded', () => {
  const addButton = document.getElementById('add-button');
  const cancelButton = document.getElementById('inside-form-cancel');
  const tableRows = Array.from(document.querySelectorAll('#vehicles-table tbody tr'));
  const noVehicleSelected = document.getElementById('no-vehicle-selected');
  const vehicleDetailsContainer = document.getElementById('vehicle-details-container');
  const removeButton = document.getElementById('remove-button');

  // show / hide popup "Add vehicle"
  addButton.addEventListener('click', () => {
    document.getElementById('add-vehicle-popup').style.display = 'flex';
  });
  cancelButton.addEventListener('click', () => {
    document.getElementById('add-vehicle-popup').style.display = 'none';
  });

  // table click
  tableRows.forEach(row => row.addEventListener('click', () => selectRow(row)));

  // request back/forward
  window.addEventListener('popstate', () => {
    const id = new URLSearchParams(location.search).get('id');
    if (id) {
      highlightRow(id);
      fetchVehicleDetails(id);
    } else {
      vehicleDetailsContainer.style.display = 'none';
      noVehicleSelected.style.display = 'block';
    }
  });

  // if ?id= in URL fetch
  const initialId = new URLSearchParams(location.search).get('id');
  if (initialId) {
    highlightRow(initialId);
    fetchVehicleDetails(initialId);
  }

  // on click  .validity-date → show input
  vehicleDetailsContainer.addEventListener('click', e => {
    if (e.target.matches('.validity-date')) {
      e.target.style.display = 'none';
      const inp = e.target.nextElementSibling;
      if (inp && inp.matches('input.edit-date')) {
        inp.style.display = 'inline-block';
      }
    }
  });

  // change .edit-date → send fetch
  vehicleDetailsContainer.addEventListener('change', e => {
    if (e.target.matches('input.edit-date')) {
      const newDate = e.target.value;
      const type = e.target.dataset.type;
      const vehicleId = new URLSearchParams(location.search).get('id');
      if (!vehicleId || !newDate) {
        alert('Vehicle ID or date is missing!');
        return;
      }
      fetch('/updateVehicleDate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ vehicleId, type, newDate })
      })
      .then(r => { if (!r.ok) throw new Error(); return r.json(); })
      .then(() => { alert('Date updated successfully'); location.reload(); })
      .catch(() => alert('Failed to update date'));
    }
  });

  // remove vehicle
  removeButton.addEventListener('click', () => {
    const vehicleId = new URLSearchParams(location.search).get('id');
    if (!vehicleId) {
      alert("Please select a vehicle to remove.");
      return;
    }
    if (!confirm(`Are you sure you want to delete vehicle with ID = #${vehicleId}?`)) {
      return;
    }
    fetch('deleteVehicle', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: vehicleId })
    })
    .then(r => { if (!r.ok) throw new Error(); return r.json(); })
    .then(() => window.location.href = '/vehicles')
    .catch(() => alert("An error occurred while deleting the vehicle."));
  });

  // help functions

  function selectRow(row) {
    tableRows.forEach(r => r.classList.remove('selected'));
    row.classList.add('selected');
    const id = row.cells[0].textContent;
    history.pushState({}, '', `?id=${id}`);
    fetchVehicleDetails(id);
  }

  function highlightRow(id) {
    tableRows.forEach(r => {
      r.classList.toggle('selected', r.cells[0].textContent === id);
    });
  }

  function fetchVehicleDetails(id) {
    vehicleDetailsContainer.innerHTML = '<div class="loader">Loading...</div>';
    vehicleDetailsContainer.style.display = 'flex';
    noVehicleSelected.style.display = 'none';

    fetch(`api/vehicles?id=${id}`)
      .then(res => res.ok ? res.json() : Promise.reject(res))
      .then(json => displayVehicleDetails(json.data))
      .catch(() => {
        vehicleDetailsContainer.innerHTML = '<div class="error">Failed to load vehicle data</div>';
      });
  }

  function displayVehicleDetails(v) {
    const formatDate = ds => new Date(ds).toLocaleDateString('en-GB');
    const validInspection = new Date(v.vehicle_inspection_expiry) > new Date();
    const validOcAc = new Date(v.oc_ac_expiry) > new Date();

    vehicleDetailsContainer.innerHTML = `
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
          <span class="info-label">State:</span>
          <span class="info-label">Localization:</span>
          <span class="info-label">Assigned driver:</span>
        </div>
        <div id="vehicle-information-data-values" class="vehicle-information-data-labels-values">
          <span class="value">${v.brand}</span>
          <span class="value">${v.model}</span>
          <span class="value">${v.reg_number}</span>
          <span class="value">${v.mileage} km</span>
          <div class="value">
            <span class="inspection-validity" data-type="vehicle_inspection_expiry">
              ${validInspection ? 'valid' : 'expired'}
            </span>
            <span class="validity-date" data-type="vehicle_inspection_expiry">
              (until ${formatDate(v.vehicle_inspection_expiry)})
            </span>
            <input type="date"
                   class="edit-date"
                   data-type="vehicle_inspection_expiry"
                   value="${v.vehicle_inspection_expiry}"
                   style="display:none">
          </div>
          <div class="value">
            <span class="oc-ac-validity" data-type="oc_ac_expiry">
              ${validOcAc ? 'valid' : 'expired'}
            </span>
            <span class="validity-date" data-type="oc_ac_expiry">
              (until ${formatDate(v.oc_ac_expiry)})
            </span>
            <input type="date"
                   class="edit-date"
                   data-type="oc_ac_expiry"
                   value="${v.oc_ac_expiry}"
                   style="display:none">
          </div>
          <span class="value">${v.vin}</span>
          <span class="value">${v.avg_fuel_consumption ?? 'N/A'} l</span>
          <div class="value">
            <div class="status-dot ${v.status}"></div>
            <span>${v.status.replace('_',' ')}</span>
          </div>
          <span class="value">
            ${v.current_latitude && v.current_longitude
              ? `Lat: ${v.current_latitude}, Long: ${v.current_longitude}`
              : 'Unknown'}
          </span>
          <span class="value">Not assigned</span>
        </div>
        <div id="vehicle-image" class="inside-grid-container">
          <img id="car-picture" src="public/uploads/${v.photo}" alt="${v.brand} ${v.model}">
        </div>
      </div>

      <div class="map-container">
        <div id="leaflet-map" style="width:100%; height:300px"></div>
      </div>

      <div id="information-buttons">
        <button id="service-history-button" class="information-button">
          <img class="download-icon" src="public/images/download_icon_white.png">Service History
        </button>
        <button id="gps-history-button" class="information-button">
          <img class="download-icon" src="public/images/download_icon_white.png">GPS History
        </button>
      </div>
    `;

    // map init
    if (v.current_latitude && v.current_longitude) {
      if (window.vehicleMap) window.vehicleMap.remove();

      window.vehicleMap = L.map('leaflet-map')
        .setView([v.current_latitude, v.current_longitude], 13);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
      }).addTo(window.vehicleMap);

      // marker
      const carIcon = L.icon({
        iconUrl: 'public/images/car_pointer_black.png',
        iconSize:   [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
      });

      L.marker([v.current_latitude, v.current_longitude], { icon: carIcon })
        .addTo(window.vehicleMap)
        .bindPopup(`<b>${v.brand} ${v.model}</b><br>${v.reg_number}<br>Vehicle ID: ${v.id}`)
        .openPopup();
    }
  }
});
