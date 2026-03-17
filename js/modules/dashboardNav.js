export function initDashboardNav() {
  const navItems = document.querySelectorAll('.dash-nav-item[data-section]');
  if (!navItems.length) return;

  const titleEl = document.getElementById('dashPageTitle');
  const dashContent = document.getElementById('dashContent');
  const iframeUsuarios = document.getElementById('dashIframeUsuarios');
  const iframeProductos = document.getElementById('dashIframeProductos');

  const hideAll = () => {
    dashContent?.classList.add('is-hidden');
    iframeUsuarios?.classList.add('is-hidden');
    iframeProductos?.classList.add('is-hidden');
  };

  const show = (section) => {
    hideAll();
    if (section === 'usuarios') iframeUsuarios?.classList.remove('is-hidden');
    else if (section === 'productos')
      iframeProductos?.classList.remove('is-hidden');
    else dashContent?.classList.remove('is-hidden');
  };

  navItems.forEach((item) => {
    item.addEventListener('click', () => {
      navItems.forEach((i) => i.classList.remove('active'));
      item.classList.add('active');

      const label = item.textContent?.trim();
      if (titleEl && label) titleEl.textContent = label;

      const section = item.dataset.section;
      show(section);
    });
  });

  // Initial state: if some nav item is marked active, enforce it.
  const active = document.querySelector('.dash-nav-item.active[data-section]');
  if (active) {
    const label = active.textContent?.trim();
    if (titleEl && label) titleEl.textContent = label;
    show(active.dataset.section);
  }
}

