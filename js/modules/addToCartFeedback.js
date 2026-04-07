/**
 * Feedback breve al pulsar «añadir al carrito»: texto de confirmación y contador de insignia solo visual.
 */
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

      const productId = btn.getAttribute('data-producto-id') || btn.getAttribute('data-product-id');
      if (!productId) return;

      const qtyInput = document.querySelector('.qty-input');
      let cantidad = parseInt(qtyInput?.value || '1', 10);
      if (!Number.isFinite(cantidad) || cantidad < 1) cantidad = 1;

      const edicionActiva = document.querySelector('.option-chip.active[data-edicion-id]');
      const fd = new FormData();
      fd.append('action', 'add');
      fd.append('producto_id', productId);
      fd.append('cantidad', String(cantidad));
      if (edicionActiva) {
        fd.append('edicion_id', edicionActiva.getAttribute('data-edicion-id'));
      }

      fetch('/views/php/carrito/api.php', { method: 'POST', body: fd })
        .then((r) => r.json())
        .then((data) => {
          if (!data?.ok || !data?.resumen) return;
          const count = Number(data.resumen.cantidad || 0);
          const badge = document.querySelector('.badge-count');
          if (badge) {
            if (count > 0) {
              badge.textContent = String(count);
            } else {
              badge.remove();
            }
            return;
          }
          if (count > 0) {
            const cartBtn = document.querySelector('a.nav-icon-btn[title="Carrito"]');
            if (cartBtn) {
              const newBadge = document.createElement('span');
              newBadge.className = 'badge-count';
              newBadge.textContent = String(count);
              cartBtn.appendChild(newBadge);
            }
          }
        })
        .catch(() => {});
    });
  });
}

