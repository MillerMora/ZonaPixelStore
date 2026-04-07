/**
 * Menú desplegable del usuario en la barra de administración: foco, clic exterior y tecla Escape.
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
  
  // Cierra el menú si el clic ocurre fuera del contenedor
  document.addEventListener('click', (e) => {
    if (!dropdown.contains(e.target)) {
      dropdown.classList.remove('active');
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
  
  // Cierra con Escape por accesibilidad
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && dropdown.classList.contains('active')) {
      dropdown.classList.remove('active');
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
}
