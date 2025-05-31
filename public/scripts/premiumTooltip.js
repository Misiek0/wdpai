document.addEventListener('DOMContentLoaded', () => {
  const tooltip = document.getElementById('premium-tooltip');

  const showTooltip = (x, y) => {
    tooltip.style.display = 'block';
    tooltip.style.left = x + 15 + 'px';
    tooltip.style.top = y + 15 + 'px';

    requestAnimationFrame(() => {
      const rect = tooltip.getBoundingClientRect();

      if (rect.right > window.innerWidth) {
        tooltip.style.left = Math.max(x - rect.width - 15, 0) + 'px';
      }
      if (rect.bottom > window.innerHeight) {
        tooltip.style.top = Math.max(y - rect.height - 15, 0) + 'px';
      }
    });

    clearTimeout(window.tooltipTimeout);
    window.tooltipTimeout = setTimeout(() => {
      tooltip.style.display = 'none';
    }, 2000);
  };

const handleShow = (e) => {
  const x = e.touches ? e.touches[0].clientX : e.clientX;
  const y = e.touches ? e.touches[0].clientY : e.clientY;
  showTooltip(x, y);
};

  const hideTooltip = () => {
    tooltip.style.display = 'none';
  };

  // Obsługa dynamicznych przycisków: .information-button
  ['click', 'touchstart'].forEach(eventType => {
    document.body.addEventListener(eventType, (e) => {
      const target = e.target.closest('.information-button');
      if (target) {
        handleShow(e);
      }
    });
  });

  // Statyczne elementy (istnieją od początku)
  const staticTargets = [
    document.querySelector('#statistics button'),
    ...document.querySelectorAll('.menu-reports')
  ];

  staticTargets.forEach(el => {
    if (!el) return;
    el.addEventListener('mouseenter', handleShow);
    el.addEventListener('mouseleave', hideTooltip);
    el.addEventListener('click', handleShow);
    el.addEventListener('touchstart', handleShow);
  });
});
