/**
 * Detalle de producto: miniaturas de galería, controles de cantidad y favoritos (solo presentación).
 */
export function initProductInteractions() {
  // Miniaturas: copian la imagen principal y marcan la activa
  document.querySelectorAll('.gallery-thumbs').forEach((thumbsEl) => {
    const main = thumbsEl
      .closest('.product-gallery')
      ?.querySelector('.gallery-main img');
    if (!main) return;

    thumbsEl.querySelectorAll('.gallery-thumb').forEach((thumb) => {
      thumb.addEventListener('click', () => {
        const src = thumb.querySelector('img')?.src;
        if (src) main.src = src;
        thumbsEl
          .querySelectorAll('.gallery-thumb')
          .forEach((t) => t.classList.remove('active'));
        thumb.classList.add('active');
      });
    });
  });

  // Botones +/- alrededor del input numérico de cantidad
  document.querySelectorAll('.qty-control').forEach((ctrl) => {
    const input = ctrl.querySelector('.qty-input');
    if (!input) return;

    ctrl.querySelector('.qty-minus')?.addEventListener('click', () => {
      const v = parseInt(input.value, 10);
      if (!Number.isFinite(v)) return;
      if (v > 1) input.value = String(v - 1);
    });

    ctrl.querySelector('.qty-plus')?.addEventListener('click', () => {
      const v = parseInt(input.value, 10);
      input.value = String((Number.isFinite(v) ? v : 0) + 1);
    });
  });

  // Corazón de lista de deseos: alterna iconos relleno/contorno sin persistencia en servidor
  document
    .querySelectorAll('.product-wishlist, .btn-wishlist-lg')
    .forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const icon = btn.querySelector('i');
        if (!icon) return;

        const active = icon.classList.contains('fas');
        icon.className = active ? 'far fa-heart' : 'fas fa-heart';
        btn.classList.toggle('is-wishlist-active', !active);
      });
    });
}

