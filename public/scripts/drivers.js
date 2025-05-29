document.addEventListener('DOMContentLoaded', () => {
  const addButton = document.getElementById('add-button');
  const cancelButton = document.getElementById('inside-form-cancel');
  const tableRows = Array.from(document.querySelectorAll('#drivers-table tbody tr'));
  const noDriverSelected = document.getElementById('no-driver-selected');
  const driverDetailsContainer = document.getElementById('driver-details-container');
  const removeButton = document.getElementById('remove-button');

  // show / hide popup "Add driver"
  addButton.addEventListener('click', () => {
    document.getElementById('add-driver-popup').style.display = 'flex';
  });
  cancelButton.addEventListener('click', () => {
    document.getElementById('add-driver-popup').style.display = 'none';
  });
  
  // table click
  tableRows.forEach(row => {
    row.addEventListener('click', () => selectRow(row));
  });

  // request back/forward
   window.addEventListener('popstate', () => {
    const id = new URLSearchParams(location.search).get('id');
    if (id) {
      highlightRow(id);
      fetchDriverDetails(id);
    } else {
      driverDetailsContainer.style.display = 'none';
      noDriverSelected.style.display = 'block';
    }
  });

  // if ?id= in URL fetch
  const initialId = new URLSearchParams(location.search).get('id');
  if (initialId) {
    highlightRow(initialId);
    fetchDriverDetails(initialId);
  }

  // on click  .validity-date → show input
  driverDetailsContainer.addEventListener('click', e => {
    if (e.target.matches('.validity-date')) {
      e.target.style.display = 'none';
      const inp = e.target.nextElementSibling;
      if (inp && inp.matches('input.edit-date')) {
        inp.style.display = 'inline-block';
      }
    }
  });

  // change .edit-date → send fetch
  driverDetailsContainer.addEventListener('change', e => {
    if (e.target.matches('input.edit-date')) {
      const newDate = e.target.value;
      const type = e.target.dataset.type;
      const driverId = new URLSearchParams(location.search).get('id');
      if (!driverId || !newDate) {
        alert('Driver ID or date is missing!');
        return;
      }
      fetch('/updateDriverDate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ driverId, type, newDate })
      })
      .then(r => { if (!r.ok) throw new Error(); return r.json(); })
      .then(() => { alert('Date updated successfully'); location.reload(); })
      .catch(() => alert('Failed to update date'));
    }
  });

    // remove driver
  removeButton.addEventListener('click', () => {
    const driverId = new URLSearchParams(location.search).get('id');
    if (!driverId) {
      alert("Please select a driver to remove.");
      return;
    }
    if (!confirm(`Are you sure you want to delete driver with ID = #${driverId}?`)) {
      return;
    }
    fetch('deleteDriver', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: driverId })
    })
    .then(r => { if (!r.ok) throw new Error(); return r.json(); })
    .then(() => window.location.href = '/drivers')
    .catch(() => alert("An error occurred while deleting the driver."));
  });
 
//help functions
  function selectRow(row) {
    tableRows.forEach(r => r.classList.remove('selected'));
    row.classList.add('selected');
    const id = row.cells[0].textContent;
    history.pushState({}, '', `?id=${id}`);
    fetchDriverDetails(id);
  }

  function highlightRow(id) {
    tableRows.forEach(r => {
      r.classList.toggle('selected', r.cells[0].textContent === id);
    });
  }

  function fetchDriverDetails(id) {
    driverDetailsContainer.innerHTML = '<div class="loader">Loading...</div>';
    driverDetailsContainer.style.display = 'flex';
    noDriverSelected.style.display = 'none';

    fetch(`api/drivers?id=${id}`)
      .then(res => res.ok ? res.json() : Promise.reject())
      .then(json => displayDriverDetails(json.data))
      .catch(() => {
        driverDetailsContainer.innerHTML = '<div class="error">Failed to load driver data</div>';
      });
  }

  function displayDriverDetails(driver) {
    const formatDate = ds => new Date(ds).toLocaleDateString('en-GB');
    const validLicense = new Date(driver.license_expiry) > new Date();
    const validMedicalExamExpiry = new Date(driver.medical_exam_expiry) > new Date();
    const assigned = driver.assigned_vehicle;
    const assignedHtml = assigned
      ? `<span class="value">${assigned.brand} ${assigned.model} ${assigned.reg_number}</span>`
      : `<span class="value">Unassigned</span>`;



    driverDetailsContainer.innerHTML = `
      <div id="driver-information-data-labels-values-image">

      <div id="label-value-container">
        <div class="driver-information-data-labels-values">
          <span class="info-label">Name:</span>
          <span class="info-label">Surname:</span>
          <span class="info-label">Phone number:</span>
          <span class="info-label">Email:</span>
          <span class="info-label">Driver license:</span>
          <span class="info-label">Medical examination:</span>
          <span class="info-label">Assigned vehicle:</span>
          <span class="info-label">State:</span>
        </div>
        <div class="driver-information-data-labels-values">
          <span class="value">${driver.name}</span>
          <span class="value">${driver.surname}</span>
          <span class="value">${driver.phone}</span>
          <span class="value">${driver.email}</span>
          <div class="value">
            <span>${validLicense? 'valid' : 'expired'}</span>
            <span class="validity-date">(until ${formatDate(driver.license_expiry)})</span>
            <input type="date"
                   class="edit-date"
                   data-type="license_expiry"
                   value="${driver.license_expiry}"
                   style="display:none">
          </div>
          <div class="value">
            <span>${validMedicalExamExpiry ? 'valid' : 'expired'}</span>
            <span class="validity-date">(until ${formatDate(driver.medical_exam_expiry)})</span>
            <input type="date"
                   class="edit-date"
                   data-type="medical_exam_expiry"
                   value="${driver.medical_exam_expiry}"
                   style="display:none"> 
            </div>
          ${assignedHtml}
          <div class="value">
            <div class="status-dot ${driver.driver_status}"></div>
            <span>${driver.driver_status.replace('_', ' ')}</span>
          </div>

          </div>
        </div>
        <div id="driver-image" class="inside-grid-container">
          <img id="driver-picture" src="public/uploads/${driver.photo}">
        </div>
      </div>

      <div class="report-container">
        <span><b>Recently reported issues</b></span>
        <div class="report"></div>
      </div>

      <div id="information-buttons">
        <button id="driver-history-button" class="information-button">
          <img class="download-icon" src="public/images/download_icon_white.png">Driver history
        </button>
        <button id="trips-history-button" class="information-button">
          <img class="download-icon" src="public/images/download_icon_white.png">Incident report
        </button>
      </div>
    `;
  }
});
