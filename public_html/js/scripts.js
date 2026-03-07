document.body.classList.add('js');

function initRecipeFilters() {
  const filterForm = document.querySelector('.recipe-filters form');
  if (!filterForm) return;

  const selects = filterForm.querySelectorAll('select');

  selects.forEach((select) => {
    select.addEventListener('change', () => {
      if (document.body.classList.contains('loading')) return;

      document.body.classList.add('loading');
      filterForm.submit();
    });
  });
}

function formatQuantityKitchen(value) {
  const num = Number(value);

  if (!Number.isFinite(num)) return '';
  if (num === 0) return '0';

  const whole = Math.floor(num);
  const fraction = num - whole;

  const commonFractions = [
    [0.125, '⅛'],
    [0.25, '¼'],
    [0.3333333333, '⅓'],
    [0.375, '⅜'],
    [0.5, '½'],
    [0.625, '⅝'],
    [0.6666666667, '⅔'],
    [0.75, '¾'],
    [0.875, '⅞'],
  ];

  let closestFraction = '';
  let smallestDiff = Infinity;

  commonFractions.forEach(([decimal, label]) => {
    const diff = Math.abs(fraction - decimal);
    if (diff < smallestDiff) {
      smallestDiff = diff;
      closestFraction = label;
    }
  });

  if (smallestDiff < 0.04) {
    if (whole > 0) {
      return `${whole} ${closestFraction}`;
    }
    return closestFraction;
  }

  if (fraction < 0.04) {
    return String(whole);
  }

  if ((1 - fraction) < 0.04) {
    return String(whole + 1);
  }

  let str = num.toFixed(2).replace(/\.?0+$/, '');
  if (str === '-0') str = '0';
  return str;
}

function initIngredientScaling() {
  const scaleButtons = document.querySelectorAll('.scale-button');
  const qtySpans = document.querySelectorAll('.recipe-ingredients .qty[data-base-qty]');
  const servingsValue = document.querySelector('.recipe-servings-value[data-base-servings]');

  if (!scaleButtons.length || !qtySpans.length) return;

  scaleButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const scale = Number(button.dataset.scale || '1');

      qtySpans.forEach((qtySpan) => {
        const baseQty = Number(qtySpan.dataset.baseQty || '0');
        const scaledQty = baseQty * scale;
        qtySpan.textContent = formatQuantityKitchen(scaledQty);
      });

      if (servingsValue) {
        const baseServings = Number(servingsValue.dataset.baseServings || '0');
        const scaledServings = baseServings * scale;
        servingsValue.textContent = formatQuantityKitchen(scaledServings);
      }

      scaleButtons.forEach((btn) => btn.classList.remove('is-active'));
      button.classList.add('is-active');
    });
  });
}

function initRecipeImageGallery() {
  const thumbs = document.querySelectorAll('.recipe-thumb');
  if (!thumbs.length) return;

  // placeholder for later
}

function init() {
  initRecipeFilters();
  initIngredientScaling();
  initRecipeImageGallery();
}

init();
