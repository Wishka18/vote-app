document.addEventListener('DOMContentLoaded', () => {
  // Polling Station Card Hover Effects
  const pollingStations = document.querySelectorAll('.polling-station-card');
  pollingStations.forEach(station => {
    station.addEventListener('mouseenter', () => {
      station.style.transform = 'scale(1.03)';
    });

    station.addEventListener('mouseleave', () => {
      station.style.transform = 'scale(1)';
    });
  });

  //Result Page

  
  // DESKTOP toggle
  const tabs = document.querySelectorAll('.results-sidebar .position-tab');
  const blocks = document.querySelectorAll('.results-desktop .position-block');

  if (tabs.length > 0) {
    const firstPosition = tabs[0].dataset.position;
    tabs[0].classList.add('active');
    blocks.forEach(block => {
      block.style.display = (block.dataset.position === firstPosition) ? 'block' : 'none';
    });

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const position = tab.dataset.position;
        blocks.forEach(block => {
          block.style.display = (block.dataset.position === position) ? 'block' : 'none';
        });
      });
    });
  }

  // MOBILE dropdown toggle
  const dropdown = document.getElementById('resultPositionDropdown');
  const mobileBlocks = document.querySelectorAll('.results-mobile .position-block');

  if (dropdown) {
    const firstMobilePosition = dropdown.value;
    mobileBlocks.forEach(block => {
      block.style.display = (block.dataset.position === firstMobilePosition) ? 'block' : 'none';
    });

    dropdown.addEventListener('change', () => {
      const position = dropdown.value;
      mobileBlocks.forEach(block => {
        block.style.display = (block.dataset.position === position) ? 'block' : 'none';
      });
    });
  }


});