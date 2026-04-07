/**
 * Conmuta la rejilla del catálogo entre vista cuadrícula y lista; persiste la preferencia en localStorage.
 */
const STORAGE_KEY = 'zp_catalogo_vista';

export function initViewToggle() {
  const grid = document.getElementById('catalogProductGrid');
  const viewBtns = document.querySelector('.catalog-main .view-btns');
  if (!grid || !viewBtns) return;

  const applyMode = (mode) => {
    const isList = mode === 'list';
    grid.classList.toggle('products-list-view', isList);
    viewBtns.querySelectorAll('.view-btn').forEach((b) => {
      const wantList = b.getAttribute('data-view') === 'list';
      b.classList.toggle('active', wantList === isList);
    });
    try {
      localStorage.setItem(STORAGE_KEY, mode);
    } catch {
      /* almacenamiento no disponible o bloqueado: se ignora */
    }
  };

  let initial = 'grid';
  try {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved === 'list' || saved === 'grid') initial = saved;
  } catch {
    /* sin acceso a localStorage: se usa la vista por defecto */
  }
  if (grid.getAttribute('data-initial-view') === 'list') initial = 'list';
  applyMode(initial);

  viewBtns.querySelectorAll('.view-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const mode = btn.getAttribute('data-view') === 'list' ? 'list' : 'grid';
      applyMode(mode);
    });
  });
}

