/**
 * sidebar.js — mobile sidebar toggle
 * Works with the `.sidebar` + `.sidebar--open` CSS classes.
 */
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    const toggle  = document.querySelector('.sidebar-toggle');

    if (!sidebar || !toggle) return;

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar--open');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth > 768) return;
        if (!sidebar.contains(e.target) && e.target !== toggle) {
            sidebar.classList.remove('sidebar--open');
        }
    });
});
