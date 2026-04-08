function initRecipeFilters() {
  const filterForm = document.querySelector('.recipe-filters form');
  if (!filterForm) return;

  const inputs = filterForm.querySelectorAll('input[type="checkbox"]');
  const menus = filterForm.querySelectorAll('.filter-menu');

  const restoreId = sessionStorage.getItem('recipeFilterFocusId');
  const restoreMenu = sessionStorage.getItem('recipeFilterMenu');

  if (restoreMenu) {
    const menuEl = filterForm.querySelector(`.filter-menu[data-filter-menu="${restoreMenu}"]`);
    if (menuEl) {
      menuEl.classList.add('is-open');
    }
  }

  if (restoreId) {
    const restoreInput = document.getElementById(restoreId);
    if (restoreInput) {
      setTimeout(() => {
        restoreInput.focus();
        restoreInput.scrollIntoView({ block: 'nearest' });
      }, 0);
    }

    sessionStorage.removeItem('recipeFilterFocusId');
    sessionStorage.removeItem('recipeFilterMenu');
  }

  inputs.forEach((input) => {
    input.addEventListener('change', () => {
      if (document.body.classList.contains('loading')) return;

      sessionStorage.setItem('recipeFilterFocusId', input.id);
      sessionStorage.setItem('recipeFilterMenu', input.dataset.filterMenu || '');

      document.body.classList.add('loading');
      filterForm.submit();
    });
  });

  document.addEventListener('focusin', (event) => {
    const currentMenu = event.target.closest('.filter-menu');

    if (!filterForm.contains(event.target)) {
      menus.forEach((menu) => menu.classList.remove('is-open'));
      return;
    }

    menus.forEach((menu) => {
      if (menu !== currentMenu) {
        menu.classList.remove('is-open');
      }
    });

    if (currentMenu) {
      currentMenu.classList.add('is-open');
    }
  });

  document.addEventListener('click', (event) => {
    if (!filterForm.contains(event.target)) {
      menus.forEach((menu) => menu.classList.remove('is-open'));
    }
  });
}

function initIngredientScaling() {
  const scaleButtons = document.querySelectorAll('.scale-button');
  const qtySpans = document.querySelectorAll('.recipe-ingredients .qty[data-base-qty]');
  const servingsValue = document.querySelector('.recipe-servings-value[data-base-servings]');

  if (!scaleButtons.length || !qtySpans.length) return;

  function applyScale(scale) {
    const numericScale = Number(scale || '1');

    qtySpans.forEach((qtySpan) => {
      const baseQty = Number(qtySpan.dataset.baseQty || '0');
      const scaledQty = baseQty * numericScale;
      qtySpan.textContent = formatQuantityKitchen(scaledQty);
    });

    if (servingsValue) {
      const baseServings = Number(servingsValue.dataset.baseServings || '0');
      const scaledServings = baseServings * numericScale;
      servingsValue.textContent = formatQuantityKitchen(scaledServings);
    }

    scaleButtons.forEach((btn) => {
      btn.classList.toggle('is-active', btn.dataset.scale === String(scale));
    });

    const url = new URL(window.location.href);

    if (numericScale === 1) {
      url.searchParams.delete('scale');
    } else {
      url.searchParams.set('scale', numericScale);
    }

    window.history.replaceState({}, '', url);
  }

  scaleButtons.forEach((button) => {
    button.disabled = false;

    button.addEventListener('click', () => {
      applyScale(button.dataset.scale || '1');
    });
  });

  const urlParams = new URLSearchParams(window.location.search);
  const initialScale = urlParams.get('scale');

  if (initialScale && [...scaleButtons].some((btn) => btn.dataset.scale === initialScale)) {
    applyScale(initialScale);
  } else {
    applyScale('1');
  }
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

function initAutoRating() {
  const ratingForm = document.querySelector('.rating-form form');
  if (!ratingForm) return;

  const isLoggedIn = ratingForm.dataset.isLoggedIn === 'true';
  const loginUrl = ratingForm.dataset.loginUrl || '';
  const radios = ratingForm.querySelectorAll('input[type="radio"][name="rating"]');
  const clearButton = ratingForm.querySelector('.rating-clear-button');

  function submitRating(formData) {
    return fetch(ratingForm.action, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then((response) => response.json())
      .then((data) => {
        showToast(data.message);

        if (data.ok) {
          updateRatingSummary(data);
          updateClearButton(ratingForm, data.user_rating);
          updateSelectedRating(ratingForm, data.user_rating);
        }
      })
      .catch(() => {
        showToast('Something went wrong.');
      });
  }

  radios.forEach((radio) => {
    radio.addEventListener('change', () => {
      if (!isLoggedIn) {
        if (loginUrl) {
          window.location.href = loginUrl;
        }
        return;
      }

      const formData = new FormData(ratingForm);
      formData.delete('clear_rating');

      submitRating(formData);
    });
  });

  if (clearButton) {
    clearButton.addEventListener('click', (event) => {
      if (!isLoggedIn) {
        return;
      }

      event.preventDefault();

      const formData = new FormData(ratingForm);
      formData.delete('rating');
      formData.set('clear_rating', '1');

      submitRating(formData);
    });
  }
}

function updateSelectedRating(ratingForm, userRating) {
  const radios = ratingForm.querySelectorAll('input[type="radio"][name="rating"]');

  radios.forEach((radio) => {
    radio.checked = Number(radio.value) === Number(userRating);
  });
}

function updateRatingSummary(data) {
  const summary = document.querySelector('[data-rating-summary]');
  if (!summary) return;

  const text = summary.querySelector('[data-rating-text]');
  let count = summary.querySelector('[data-rating-count]');

  if (data.rating_avg === null) {
    if (text) text.textContent = 'Not rated yet';
    if (count) count.remove();
    return;
  }

  if (text) {
    text.textContent = `${Number(data.rating_avg).toFixed(1)}/5`;
  }

  if (!count) {
    count = document.createElement('span');
    count.setAttribute('data-rating-count', '');
    summary.appendChild(document.createTextNode(' '));
    summary.appendChild(count);
  }

  count.textContent = `(${data.rating_count})`;
}

function updateClearButton(ratingForm, userRating) {
  const clearButton = ratingForm.querySelector('.rating-clear-button');
  if (!clearButton) return;

  clearButton.hidden = !userRating;
}

function initRecipeImageGallery() {
  const heroWrap = document.querySelector('.recipe-image-hero');
  const heroLink = document.querySelector('.recipe-hero-link');
  const thumbs = document.querySelectorAll('.recipe-thumb');

  if (!heroWrap || !heroLink || !thumbs.length) return;

  let currentImage = heroWrap.querySelector('.recipe-hero-image-current');
  let nextImage = heroWrap.querySelector('.recipe-hero-image-next');
  let isTransitioning = false;

  thumbs.forEach((thumb) => {
    thumb.addEventListener('click', (event) => {
      event.preventDefault();

      if (isTransitioning) return;

      const fullUrl = thumb.dataset.fullUrl;
      const altText = thumb.dataset.alt || currentImage.alt;

      if (!fullUrl || currentImage.src === fullUrl) return;

      isTransitioning = true;

      thumbs.forEach((t) => t.classList.remove('is-active'));
      thumb.classList.add('is-active');

      const loader = new Image();

      loader.onload = () => {
        nextImage.src = fullUrl;
        nextImage.alt = altText;

        // let browser paint new src before transition
        requestAnimationFrame(() => {
          requestAnimationFrame(() => {
            heroWrap.classList.add('is-transitioning');
          });
        });

        setTimeout(() => {
          heroLink.href = fullUrl;

          // swap class roles
          currentImage.classList.remove('recipe-hero-image-current');
          currentImage.classList.add('recipe-hero-image-next');

          nextImage.classList.remove('recipe-hero-image-next');
          nextImage.classList.add('recipe-hero-image-current');

          // update JS refs
          const oldCurrent = currentImage;
          currentImage = nextImage;
          nextImage = oldCurrent;

          // clear old hidden image so it won't flash stale content later
          nextImage.src = '';
          nextImage.alt = '';

          heroWrap.classList.remove('is-transitioning');
          isTransitioning = false;
        }, 250);
      };

      loader.src = fullUrl;
    });
  });
}

function initHomeCarousels() {

  const wrappers = document.querySelectorAll('.home-carousel-wrapper');

  wrappers.forEach((wrapper) => {

    const carousel = wrapper.querySelector('.home-carousel');
    const prev = wrapper.querySelector('.carousel-prev');
    const next = wrapper.querySelector('.carousel-next');

    if (!carousel || !prev || !next) return;

    const card = carousel.querySelector('.recipe-card');
    const scrollAmount = card ? card.offsetWidth + 20 : 300;

    prev.addEventListener('click', () => {
      carousel.scrollBy({
        left: -scrollAmount,
        behavior: 'smooth'
      });
    });

    next.addEventListener('click', () => {
      carousel.scrollBy({
        left: scrollAmount,
        behavior: 'smooth'
      });
    });

  });

}

function initRecipeFormEnhancements() {
  const cuisineCheckboxes = document.querySelectorAll('input[name="cuisines[]"]');
  const cuisineMax = 3;
  const addIngredientButton = document.getElementById('add-ingredient-button');
  const addStepButton = document.getElementById('add-step-button');

  function updateCuisineState() {
    const checked = Array.from(cuisineCheckboxes).filter(c => c.checked);
    const isAtMax = checked.length >= cuisineMax;

    cuisineCheckboxes.forEach(cb => {
      if (!cb.checked) {
        cb.disabled = isAtMax;
      } else {
        cb.disabled = false;
      }

      const pill = cb.nextElementSibling;
      if (!pill) { return };

      if (cb.disabled) {
        pill.classList.add('is-disabled');
      } else {
        pill.classList.remove('is-disabled');
      }
    });
  }

  if (cuisineCheckboxes.length) {
    cuisineCheckboxes.forEach(cb => {
      cb.addEventListener('change', updateCuisineState);
    });

    updateCuisineState();
  }

  if (addIngredientButton) {
    addIngredientButton.addEventListener('click', function (event) {
      event.preventDefault();

      const fieldset = addIngredientButton.closest('fieldset');
      if (!fieldset) return;

      const rows = fieldset.querySelectorAll('.ingredient-row');
      if (!rows.length) return;

      const lastRow = rows[rows.length - 1];
      const newRow = lastRow.cloneNode(true);
      const newIndex = rows.length;

      const labels = newRow.querySelectorAll('label');
      const inputs = newRow.querySelectorAll('input, select');

      labels.forEach((label) => {
        const oldFor = label.getAttribute('for');
        if (!oldFor) return;

        let newFor = oldFor
          .replace(/qty-ingredient-\d+/, `qty-ingredient-${newIndex}`)
          .replace(/unit-ingredient-\d+/, `unit-ingredient-${newIndex}`)
          .replace(/name-ingredient-\d+/, `name-ingredient-${newIndex}`);

        label.setAttribute('for', newFor);
      });

      inputs.forEach((field) => {
        const oldId = field.getAttribute('id');
        const oldName = field.getAttribute('name');

        if (oldId) {
          const newId = oldId
            .replace(/qty-ingredient-\d+/, `qty-ingredient-${newIndex}`)
            .replace(/unit-ingredient-\d+/, `unit-ingredient-${newIndex}`)
            .replace(/name-ingredient-\d+/, `name-ingredient-${newIndex}`);
          field.setAttribute('id', newId);
        }

        if (oldName) {
          const newName = oldName.replace(/\[\d+\]/, `[${newIndex}]`);
          field.setAttribute('name', newName);
        }

        if (field.tagName === 'SELECT') {
          field.selectedIndex = 0;
        } else {
          field.value = '';
        }
      });

      addIngredientButton.insertAdjacentElement('beforebegin', newRow);

      const firstField = newRow.querySelector('input, select');
      if (firstField) {
        firstField.focus();
      }
    });
  }

  if (addStepButton) {
    addStepButton.addEventListener('click', function (event) {
      event.preventDefault();

      const fieldset = addStepButton.closest('fieldset');
      if (!fieldset) return;

      const rows = fieldset.querySelectorAll('.direction-row');
      if (!rows.length) return;

      const lastRow = rows[rows.length - 1];
      const newRow = lastRow.cloneNode(true);
      const newIndex = rows.length;

      const label = newRow.querySelector('label');
      const textarea = newRow.querySelector('textarea');

      if (label) {
        label.setAttribute('for', `direction-step-${newIndex}`);
        label.textContent = `Step ${newIndex + 1}`;
      }

      if (textarea) {
        textarea.setAttribute('id', `direction-step-${newIndex}`);
        const oldName = textarea.getAttribute('name');
        if (oldName) {
          textarea.setAttribute('name', oldName.replace(/\[\d+\]/, `[${newIndex}]`));
        }
        textarea.value = '';
      }

      addStepButton.insertAdjacentElement('beforebegin', newRow);

      if (textarea) {
        textarea.focus();
      }
    });
  }
}

function showToast(message) {
  if (!message) return;

  const existingToast = document.querySelector('.flash-toast');
  if (existingToast) {
    existingToast.remove();
  }

  const toast = document.createElement('div');
  toast.className = 'flash-toast';
  toast.setAttribute('role', 'status');
  toast.setAttribute('aria-live', 'polite');

  toast.innerHTML = `
    <div class="flash-toast-inner">
      <p></p>
      <button type="button" class="flash-toast-close" aria-label="Dismiss message">&times;</button>
    </div>
  `;

  toast.querySelector('p').textContent = message;
  document.body.appendChild(toast);

  wireToast(toast);
}

function wireToast(flashToast) {
  const closeButton = flashToast.querySelector('.flash-toast-close');
  let hideTimeout = null;

  function hideFlashToast() {
    flashToast.classList.add('is-hiding');

    window.setTimeout(() => {
      flashToast.remove();
    }, 300);
  }

  if (closeButton) {
    closeButton.addEventListener('click', hideFlashToast);
  }

  hideTimeout = window.setTimeout(hideFlashToast, 4000);

  flashToast.addEventListener('mouseenter', () => {
    window.clearTimeout(hideTimeout);
  });

  flashToast.addEventListener('mouseleave', () => {
    hideTimeout = window.setTimeout(hideFlashToast, 2500);
  });
}

function initFlashToast() {
  const flashToast = document.querySelector('.flash-toast');
  if (!flashToast) return;
  wireToast(flashToast);
}

function initHeaderUserMenu() {
  const menu = document.querySelector('.header-user-menu');
  const toggle = document.getElementById('header-user-menu-toggle');
  const panel = document.getElementById('header-user-menu-panel');

  if (!menu || !toggle || !panel) return;

  function isMobileMenuMode() {
    return window.innerWidth <= 850;
  }

  function closeMenu() {
    if (isMobileMenuMode()) return;
    menu.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
  }

  function openMenu() {
    if (isMobileMenuMode()) return;
    menu.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
  }

  toggle.addEventListener('click', function () {
    if (isMobileMenuMode()) return;

    const isOpen = menu.classList.contains('is-open');
    if (isOpen) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  document.addEventListener('click', function (event) {
    if (isMobileMenuMode()) return;

    if (!menu.contains(event.target)) {
      closeMenu();
    }
  });

  document.addEventListener('keydown', function (event) {
    if (isMobileMenuMode()) return;

    if (event.key === 'Escape') {
      closeMenu();
      toggle.focus();
    }
  });

  window.addEventListener('resize', function () {
    if (isMobileMenuMode()) {
      menu.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
    }
  });
}

function init() {
  initRecipeFilters();
  initIngredientScaling();
  initAutoRating();
  initRecipeImageGallery();
  initHomeCarousels();
  initRecipeFormEnhancements();
  initFlashToast();
  initHeaderUserMenu();
}

document.body.classList.add('js');
init();
