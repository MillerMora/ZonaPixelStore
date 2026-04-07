/**
 * Carrito: animación al quitar filas y respuesta visual al «aplicar» código promocional (sin validación real).
 */
export function initCartInteractions() {
  const fmt = (n) =>
    '$' + Math.round(Number(n) || 0).toLocaleString('es-CO');

  const updateSummary = (resumen) => {
    if (!resumen) return;
    const subtotal = document.getElementById('summarySubtotal');
    const discount = document.getElementById('summaryDiscount');
    const total = document.getElementById('summaryTotal');
    const count1 = document.getElementById('summaryItemCount');
    const count2 = document.getElementById('cartItemsCount');
    if (subtotal) subtotal.textContent = fmt(resumen.subtotal || 0);
    if (discount) discount.textContent = '−' + fmt(resumen.descuento || 0);
    if (total) total.textContent = fmt(resumen.total || 0);
    if (count1) count1.textContent = String(resumen.cantidad || 0);
    if (count2) count2.textContent = String(resumen.cantidad || 0);

    const badge = document.querySelector('.badge-count');
    const c = Number(resumen.cantidad || 0);
    if (badge) {
      if (c > 0) badge.textContent = String(c);
      else badge.remove();
    } else if (c > 0) {
      const cartBtn = document.querySelector('a.nav-icon-btn[title="Carrito"]');
      if (cartBtn) {
        const span = document.createElement('span');
        span.className = 'badge-count';
        span.textContent = String(c);
        cartBtn.appendChild(span);
      }
    }
  };

  document.querySelectorAll('.cart-item').forEach((row) => {
    const idItem = row.getAttribute('data-item-id');
    const input = row.querySelector('.qty-input');
    const totalEl = row.querySelector('.cart-item-total');
    const price = Number(row.querySelector('.cart-item-price')?.getAttribute('data-price') || 0);

    const syncQty = () => {
      let qty = parseInt(input?.value || '1', 10);
      if (!Number.isFinite(qty) || qty < 1) qty = 1;
      if (input) input.value = String(qty);
      if (totalEl) totalEl.textContent = fmt(price * qty);
      const fd = new FormData();
      fd.append('action', 'update');
      fd.append('id_item', idItem || '');
      fd.append('cantidad', String(qty));
      fetch('/views/php/carrito/api.php', { method: 'POST', body: fd })
        .then((r) => r.json())
        .then((data) => updateSummary(data?.resumen))
        .catch(() => {});
    };

    row.querySelector('.qty-minus')?.addEventListener('click', syncQty);
    row.querySelector('.qty-plus')?.addEventListener('click', syncQty);
    input?.addEventListener('change', syncQty);

    row.querySelector('.cart-remove')?.addEventListener('click', () => {
      const fd = new FormData();
      fd.append('action', 'remove');
      fd.append('id_item', idItem || '');
      fetch('/views/php/carrito/api.php', { method: 'POST', body: fd })
        .then((r) => r.json())
        .then((data) => {
          row.classList.add('is-removing');
          window.setTimeout(() => row.remove(), 310);
          updateSummary(data?.resumen);
          const leftRows = document.querySelectorAll('.cart-item').length;
          if (leftRows <= 1) {
            const emptyMsg = document.getElementById('cartEmptyMsg');
            if (!emptyMsg) {
              const parent = document.querySelector('.cart-table-header')?.parentElement;
              if (parent) {
                const d = document.createElement('div');
                d.id = 'cartEmptyMsg';
                d.className = 'text-muted py-3';
                d.textContent = 'El carrito está vacío';
                parent.insertBefore(d, parent.querySelector('.d-flex.justify-content-between'));
              }
            }
          }
        })
        .catch(() => {});
    });
  });

  const applyBtn = document.querySelector('.btn-apply');
  const promoInput = document.querySelector('.promo-input');
  if (applyBtn && promoInput) {
    applyBtn.addEventListener('click', () => {
      const fd = new FormData();
      fd.append('action', 'apply_coupon');
      fd.append('codigo', promoInput.value.trim());
      fetch('/views/php/carrito/api.php', { method: 'POST', body: fd })
        .then((r) => r.json())
        .then((data) => {
          if (data?.ok) {
            applyBtn.textContent = '✓';
            applyBtn.classList.add('is-applied');
          } else {
            applyBtn.textContent = 'Aplicar';
            applyBtn.classList.remove('is-applied');
          }
          updateSummary(data?.resumen);
          if (window.zpCartUi?.setMessage) {
            window.zpCartUi.setMessage(data?.mensaje || '', !!data?.ok);
          }
        })
        .catch(() => {});
    });
  }

  const checkoutBtn = document.querySelector('.btn-checkout');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      const fd = new FormData();
      fd.append('action', 'checkout');
      fetch('/views/php/carrito/api.php', { method: 'POST', body: fd })
        .then((r) => r.json())
        .then((data) => {
          if (window.zpCartUi?.setMessage) {
            window.zpCartUi.setMessage(data?.mensaje || '', !!data?.ok);
          }
          if (data?.ok) {
            document.querySelectorAll('.cart-item').forEach((el) => el.remove());
            updateSummary(data?.resumen);
            const parent = document.querySelector('.cart-table-header')?.parentElement;
            if (parent && !document.getElementById('cartEmptyMsg')) {
              const d = document.createElement('div');
              d.id = 'cartEmptyMsg';
              d.className = 'text-muted py-3';
              d.textContent = 'El carrito está vacío';
              parent.insertBefore(d, parent.querySelector('.d-flex.justify-content-between'));
            }
          }
        })
        .catch(() => {});
    });
  }
}

