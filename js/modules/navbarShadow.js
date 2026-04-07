/**
 * Añade sombra a la barra fija cuando la ventana se desplaza ligeramente hacia abajo.
 */
export function initNavbarShadow() {
  const navbar = document.querySelector('.navbar');
  if (!navbar) return;

  const onScroll = () => {
    navbar.classList.toggle('is-scrolled', window.scrollY > 10);
  };

  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });
}

