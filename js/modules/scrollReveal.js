/**
 * Añade clase al entrar en viewport (IntersectionObserver) con retraso escalonado solo visual.
 */
export function initScrollReveal() {
  const els = document.querySelectorAll(
    '.product-card, .review-card, .opinion-card, .deal-card, .trust-item, .dash-stat-card',
  );
  if (!els.length) return;
  if (!('IntersectionObserver' in window)) return;

  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add('is-revealed');
          io.unobserve(e.target);
        }
      });
    },
    { threshold: 0.1 },
  );

  els.forEach((el, i) => {
    el.classList.add('reveal-init');
    // Retraso incremental entre tarjetas; solo estética, sin efecto en datos
    el.style.setProperty('--reveal-delay', `${i * 0.05}s`);
    io.observe(el);
  });
}

