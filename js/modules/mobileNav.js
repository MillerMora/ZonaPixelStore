export function initMobileNav() {
  const toggle = document.getElementById('navToggle');
  const nav = document.getElementById('mobileNav');
  const close = document.getElementById('mobileNavClose');
  const overlay = nav ? nav.querySelector('.mobile-nav-overlay') : null;

  if (!nav) return;

  function openNav() {
    nav.classList.add('open');
    document.body.classList.add('no-scroll');
  }

  function closeNav() {
    nav.classList.remove('open');
    document.body.classList.remove('no-scroll');
  }

  if (toggle) toggle.addEventListener('click', openNav);
  if (close) close.addEventListener('click', closeNav);
  if (overlay) overlay.addEventListener('click', closeNav);
}

