/**
 * Grupos de chips mutuamente excluyentes (un solo .active por .option-chips).
 */
export function initOptionChips() {
  document.querySelectorAll('.option-chips').forEach((group) => {
    group
      .querySelectorAll('.option-chip:not(.disabled)')
      .forEach((chip) => {
        chip.addEventListener('click', () => {
          group
            .querySelectorAll('.option-chip')
            .forEach((c) => c.classList.remove('active'));
          chip.classList.add('active');
        });
      });
  });
}

