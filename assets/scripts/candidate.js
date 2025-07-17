
document.addEventListener('DOMContentLoaded', () => {
    // DESKTOP: handle sidebar clicks
    const tabs = document.querySelectorAll('.candidate-tab');
    const desktopCards = document.querySelectorAll('#candidateGrid .candidate-card');

    if (tabs.length > 0) {
        const firstPosition = tabs[0].dataset.position;

        // Show first position by default
        tabs[0].classList.add('active');
        desktopCards.forEach(card => {
            card.style.display = (card.dataset.position === firstPosition) ? 'block' : 'none';
        });

        // On tab click
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const position = tab.dataset.position;
                desktopCards.forEach(card => {
                    card.style.display = (card.dataset.position === position) ? 'block' : 'none';
                });
            });
        });
    }

    // MOBILE: handle dropdown change
    const dropdown = document.getElementById('positionDropdown');
    const mobileCards = document.querySelectorAll('#candidateGridMobile .candidate-card');

    if (dropdown) {
        const firstMobilePosition = dropdown.value;

        // Show first position by default
        mobileCards.forEach(card => {
            card.style.display = (card.dataset.position === firstMobilePosition) ? 'block' : 'none';
        });

        // On dropdown change
        dropdown.addEventListener('change', () => {
            const position = dropdown.value;
            mobileCards.forEach(card => {
                card.style.display = (card.dataset.position === position) ? 'block' : 'none';
            });
        });
    }
});