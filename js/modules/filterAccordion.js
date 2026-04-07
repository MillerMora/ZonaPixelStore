/**
 * Acordeón de filtros en sidebar: alterna clase is-open en el título al hacer clic.
 */
export function initFilterAccordion() {
  document.querySelectorAll('.filter-title').forEach((title) => {
    const group = title.nextElementSibling;
    if (!group) return;

    title.classList.add('js-accordion');
    group.classList.add('filter-group');

    title.addEventListener('click', () => {
      title.classList.toggle('is-open');
    });
  });
}

