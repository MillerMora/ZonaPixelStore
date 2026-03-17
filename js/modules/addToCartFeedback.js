export function initAddToCartFeedback() {
  document.querySelectorAll('.btn-add-cart, .btn-add-to-cart').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (btn.classList.contains('is-added')) return;

      const orig = btn.innerHTML;
      btn.dataset.origHtml = orig;

      btn.innerHTML = '<i class="fas fa-check"></i> Añadido';
      btn.classList.add('is-added');

      window.setTimeout(() => {
        btn.innerHTML = btn.dataset.origHtml || orig;
        btn.classList.remove('is-added');
        delete btn.dataset.origHtml;
      }, 1400);

      // Visual bump only (no business logic)
      const badge = document.querySelector('.badge-count');
      if (badge) {
        const v = parseInt(badge.textContent || '0', 10);
        badge.textContent = String((Number.isFinite(v) ? v : 0) + 1);
      }
    });
  });
}

