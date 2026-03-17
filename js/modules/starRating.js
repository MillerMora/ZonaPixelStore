export function initStarRating() {
  const wrap = document.getElementById('starRating');
  if (!wrap) return;

  const stars = Array.from(wrap.querySelectorAll('span'));
  if (!stars.length) return;

  const render = (selectedCount) => {
    stars.forEach((s, i) => {
      s.style.color = i < selectedCount ? '#ffd700' : 'var(--border-light)';
    });
  };

  stars.forEach((star) => {
    star.addEventListener('mouseover', () => {
      const val = parseInt(star.dataset.val || '0', 10);
      render(Number.isFinite(val) ? val : 0);
    });

    star.addEventListener('click', () => {
      const val = parseInt(star.dataset.val || '0', 10);
      const selected = Number.isFinite(val) ? val : 0;
      stars.forEach((s, i) => {
        s.dataset.selected = i < selected ? '1' : '0';
      });
      render(selected);
    });
  });

  wrap.addEventListener('mouseleave', () => {
    const selected = stars.filter((s) => s.dataset.selected === '1').length;
    render(selected);
  });
}

