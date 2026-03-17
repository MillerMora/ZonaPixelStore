export function initCategoryChips() {
  document.querySelectorAll('.cat-chip').forEach((chip) => {
    chip.addEventListener('click', () => {
      chip
        .closest('.category-strip')
        ?.querySelectorAll('.cat-chip')
        .forEach((c) => c.classList.remove('active'));
      chip.classList.add('active');
    });
  });
}

