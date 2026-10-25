const menuButton = document.querySelector('#menuButton');
const sidebar = document.querySelector('#sidebar');
const overlay = document.querySelector('#sidebarOverlay');

function closeSidebar() {
    sidebar?.classList.add('-translate-x-full');
    overlay?.classList.add('hidden');
}

function openSidebar() {
    sidebar?.classList.remove('-translate-x-full');
    overlay?.classList.remove('hidden');
}

menuButton?.addEventListener('click', () => {
    const isClosed = sidebar?.classList.contains('-translate-x-full');

    if (isClosed) {
        openSidebar();
    } else {
        closeSidebar();
    }
});

overlay?.addEventListener('click', closeSidebar);

sidebar?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeSidebar);
});
