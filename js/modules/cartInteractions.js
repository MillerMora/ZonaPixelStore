/**
 * Carrito: animación al quitar filas y respuesta visual al «aplicar» código promocional (sin validación real).
 */
export function initCartInteractions() {
  // Elimina la fila tras una transición CSS
  document.querySelectorAll('.cart-remove').forEach((btn) => {
    btn.addEventListener('click', () => {
      const row = btn.closest('.cart-item');
      if (!row) return;
      row.classList.add('is-removing');
      window.setTimeout(() => row.remove(), 310);
    });
  });

  // Cambio de aspecto del botón si el campo no está vacío; no comprueba el cupón en servidor
  const applyBtn = document.querySelector('.btn-apply');
  const promoInput = document.querySelector('.promo-input');
  if (applyBtn && promoInput) {
    applyBtn.addEventListener('click', () => {
      if (promoInput.value.trim()) {
        applyBtn.textContent = '✓';
        applyBtn.classList.add('is-applied');
      }
    });
  }
}

