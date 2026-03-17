export function initCartInteractions() {
  // Cart remove animation (visual)
  document.querySelectorAll('.cart-remove').forEach((btn) => {
    btn.addEventListener('click', () => {
      const row = btn.closest('.cart-item');
      if (!row) return;
      row.classList.add('is-removing');
      window.setTimeout(() => row.remove(), 310);
    });
  });

  // Promo code feedback (visual)
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

