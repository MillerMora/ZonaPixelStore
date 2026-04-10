/**
 * Búsqueda en tiempo real del catálogo público: filtra .product-card mientras el usuario escribe.
 * Funciona en grid/list view; preserva submit del form para búsqueda exacta backend.
 */

let searchTimeout = null;

export function initCatalogSearch() {
  const input = document.getElementById('catalogoBusquedaInput');
  const grid = document.getElementById('catalogProductGrid');
  const form = document.getElementById('catalogoFiltroForm');
  const noResultsEl = document.getElementById('catalogNoResults');

  if (!input || !grid) return;

  const performSearch = (query) => {
    const q = query.toLowerCase().trim();
    const cards = grid.querySelectorAll('.product-card');
    let visibleCount = 0;

    cards.forEach((card) => {
      const text = [
        card.querySelector('.product-name')?.textContent || '',
        card.querySelector('.product-platform')?.textContent || ''
      ].join(' ').toLowerCase();

      const matches = q === '' || text.includes(q);
      card.style.display = matches ? '' : 'none';

      if (matches) visibleCount++;
    });

    // Toggle no-results message
    if (noResultsEl) {
      noResultsEl.style.display = (q !== '' && visibleCount === 0) ? 'block' : 'none';
    }

    // Update count if present
    const countEl = grid.parentElement?.querySelector('.catalog-count strong:first-of-type');
    if (countEl && cards.length > 0) {
      countEl.textContent = visibleCount;
    }
  };

  input.addEventListener('input', () => {
    const query = input.value;
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => performSearch(query), 250);
  });

  // Optional: prevent form submit during typing if desired (uncomment to disable instant backend search)
  /*
  if (form) {
    form.addEventListener('submit', (e) => {
      if (input.value.trim() === '') {
        e.preventDefault();
        return false;
      }
    });
  }
  */
}

