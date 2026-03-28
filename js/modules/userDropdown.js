/**
 * ZonaPixel Store — userDropdown.js
 * Maneja el menú desplegable del usuario en navbar_admin
 */

export function initUserDropdown() {
  const toggle = document.querySelector('.user-dropdown-toggle');
  const dropdown = document.querySelector('.user-dropdown');
  
  if (!toggle || !dropdown) return;
  
  const toggleMenu = (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('active');
    const isActive = dropdown.classList.contains('active');
    toggle.setAttribute('aria-expanded', isActive);
  };
  
  toggle.addEventListener('click', toggleMenu);
  
  // Cerrar al hacer click fuera
  document.addEventListener('click', (e) => {
    if (!dropdown.contains(e.target)) {
      dropdown.classList.remove('active');
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
  
  // Cerrar al presionar ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && dropdown.classList.contains('active')) {
      dropdown.classList.remove('active');
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
}
