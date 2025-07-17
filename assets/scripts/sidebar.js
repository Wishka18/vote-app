document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const mobileToggle = document.getElementById('mobile-sidebar-toggle');

    // Persistent Sidebar (desktop)
    const isSidebarExpanded = localStorage.getItem('sidebarExpanded') === 'true';
    if (isSidebarExpanded) {
        sidebar.classList.add('expanded');
    }

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('expanded');
        localStorage.setItem('sidebarExpanded', sidebar.classList.contains('expanded').toString());
    });

    // Mobile Sidebar Toggle
    mobileToggle.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-visible');
    });

    // Theme toggle remains unchanged
    const themeSwitch = document.getElementById('theme-switch');
    const body = document.body;
    const savedTheme = localStorage.getItem('theme') || 'light';
    body.classList.toggle('dark-theme', savedTheme === 'dark');
    themeSwitch.checked = savedTheme === 'dark';

    themeSwitch.addEventListener('change', () => {
        const isDarkMode = themeSwitch.checked;
        body.classList.toggle('dark-theme', isDarkMode);
        localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
    });
});