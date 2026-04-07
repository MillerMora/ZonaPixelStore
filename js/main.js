/**
 * ZonaPixel Store — main.js (ESM)
 * Punto de entrada: registra módulos de UI (navegación, catálogo, carrito, formularios).
 * Solo animaciones, realce visual e interacciones ligeras; sin reglas de negocio ni llamadas a API.
 */

import { initMobileNav } from './modules/mobileNav.js';
import { initNavbarShadow } from './modules/navbarShadow.js';
import { initCategoryChips } from './modules/categoryChips.js';
import { initProductInteractions } from './modules/productInteractions.js';
import { initScrollReveal } from './modules/scrollReveal.js';
import { initAddToCartFeedback } from './modules/addToCartFeedback.js';
import { initOptionChips } from './modules/optionChips.js';
import { initViewToggle } from './modules/viewToggle.js';
import { initDashboardNav } from './modules/dashboardNav.js';
import { initFilterAccordion } from './modules/filterAccordion.js';
import { initCartInteractions } from './modules/cartInteractions.js';
import { initStarRating } from './modules/starRating.js';
import { initUserDropdown } from './modules/userDropdown.js';

initMobileNav();
initNavbarShadow();
initCategoryChips();
initProductInteractions();
initScrollReveal();
initAddToCartFeedback();
initOptionChips();
initViewToggle();
initDashboardNav();
initFilterAccordion();
initCartInteractions();
initStarRating();
initUserDropdown();
