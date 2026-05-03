/**
 * Initialize recipe filter form auto-submit behavior and menu focus handling.
 */
function initRecipeFilters() {
  const filterForm = document.querySelector('.recipe-filters form');
  if (!filterForm) return;

  const checkboxInputs = filterForm.querySelectorAll('input[type="checkbox"]');
  const radioInputs = filterForm.querySelectorAll('input[type="radio"][name="sort"]');
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

  /*** Auto-submit the recipe filter form after storing the active input and menu.
   * 
   * @param {Object} input - filter input that triggered the form submission
   */
  function autoSubmitFromInput(input) {
    if (document.body.classList.contains('loading')) return;

    sessionStorage.setItem('recipeFilterFocusId', input.id);
    sessionStorage.setItem('recipeFilterMenu', input.dataset.filterMenu || '');

    document.body.classList.add('loading');
    filterForm.submit();
  }

  checkboxInputs.forEach((input) => {
    input.addEventListener('change', () => {
      autoSubmitFromInput(input);
    });
  });

  radioInputs.forEach((input) => {
    input.addEventListener('change', () => {
      autoSubmitFromInput(input);
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

/**
 * Initialize recipe ingredient scaling buttons and apply the selected scale.
 */
function initIngredientScaling() {
  const scaleButtons = document.querySelectorAll('.scale-button');
  const qtySpans = document.querySelectorAll('.recipe-ingredients .qty[data-base-qty]');
  const servingsValue = document.querySelector('.recipe-servings-value[data-base-servings]');

  if (!scaleButtons.length || !qtySpans.length) return;

  /**
   * Scale ingredient quantities and servings based on the selected scale value.
   * 
   * @param {string} scale - selected recipe scale value
   */
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

/**
 * Format a numeric quantity as a kitchen-friendly whole number, fraction, or decimal.
 *
 * @param {Number} value - quantity value to format
 * 
 * @return {string} formatted quantity
 */
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

/**
 * Initialize automatic recipe rating submission and clear rating behavior.
 */
function initAutoRating() {
  const ratingForm = document.querySelector('.rating-form form');
  if (!ratingForm) return;

  const isLoggedIn = ratingForm.dataset.isLoggedIn === 'true';
  const loginUrl = ratingForm.dataset.loginUrl || '';
  const radios = ratingForm.querySelectorAll('input[type="radio"][name="rating"]');
  const clearButton = ratingForm.querySelector('.rating-clear-button');

  /**
   * Submit recipe rating form data with fetch and update the rating display.
   * 
   * @param {Object} formData - rating form data to submit
   * 
   * @return {Promise} fetch promise for the rating submission
   */
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

/**
 * Update selected rating radio button based on the user's current rating.
 *
 * @param {Object} ratingForm - form element containing rating inputs
 * @param {Number|string} userRating - current user rating value
 */
function updateSelectedRating(ratingForm, userRating) {
  const radios = ratingForm.querySelectorAll('input[type="radio"][name="rating"]');

  radios.forEach((radio) => {
    radio.checked = Number(radio.value) === Number(userRating);
  });
}

/**
 * Update the displayed recipe rating average and rating count.
 *
 * @param {Object} data - rating response data
 */
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

/**
 * Show or hide the clear rating button based on whether or not the user has rated the recipe.
 *
 * @param {Object} ratingForm - form element containing the clear button
 * @param {Number|string|null} userRating - current user rating value
 */
function updateClearButton(ratingForm, userRating) {
  const clearButton = ratingForm.querySelector('.rating-clear-button');
  if (!clearButton) return;

  clearButton.hidden = !userRating;
}

/**
 * Initialize recipe image gallery thumbnail swapping and modal behavior.
 */
function initRecipeImageGallery() {
  const heroWrap = document.querySelector('.recipe-image-hero');
  const heroLink = document.querySelector('.recipe-hero-link');
  const thumbs = document.querySelectorAll('.recipe-thumb');
  const modal = document.querySelector('.recipe-image-modal');
  const modalImg = document.querySelector('.recipe-image-modal-img');
  const modalBackdrop = document.querySelector('.recipe-image-modal-backdrop');

  if (!heroWrap || !heroLink) return;

  let currentImage = heroWrap.querySelector('.recipe-hero-image-current');
  let nextImage = heroWrap.querySelector('.recipe-hero-image-next');
  let isTransitioning = false;

  /**
   * Open the recipe image modal using the current hero image.
   */
  function openModal() {
    if (!modal || !modalImg) return;

    const fullUrl = heroLink.getAttribute('href');
    const altText = currentImage ? currentImage.alt : '';

    if (!fullUrl) return;

    modalImg.src = fullUrl;
    modalImg.alt = altText;
    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    modal.classList.add('is-open');
    document.body.classList.add('modal-open');
  }

  /**
   * Close the recipe image modal and clear its image source.
   */
  function closeModal() {
    if (!modal || !modalImg) return;

    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    modal.hidden = true;
    modalImg.removeAttribute('src');
    modalImg.alt = '';
    document.body.classList.remove('modal-open');
  }

  heroLink.addEventListener('click', (event) => {
    event.preventDefault();
    openModal();
  });

  if (modalBackdrop) {
    modalBackdrop.addEventListener('click', () => {
      closeModal();
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal && !modal.hidden) {
      closeModal();
    }
  });

  thumbs.forEach((thumb) => {
    thumb.addEventListener('click', (event) => {
      event.preventDefault();

      if (isTransitioning) return;

      const fullUrl = thumb.dataset.fullUrl;
      const heroSrc = thumb.dataset.heroSrc || fullUrl;
      const heroSrcset = thumb.dataset.heroSrcset || '';
      const heroSizes = thumb.dataset.heroSizes || '';
      const altText = thumb.dataset.alt || currentImage.alt || '';

      if (!fullUrl || !heroSrc) return;

      const currentHref = heroLink.getAttribute('href') || '';
      if (currentHref === fullUrl) return;

      isTransitioning = true;

      thumbs.forEach((t) => t.classList.remove('is-active'));
      thumb.classList.add('is-active');

      const loader = new Image();

      loader.onload = () => {
        nextImage.src = heroSrc;

        if (heroSrcset) {
          nextImage.srcset = heroSrcset;
        } else {
          nextImage.removeAttribute('srcset');
        }

        if (heroSizes) {
          nextImage.sizes = heroSizes;
        } else {
          nextImage.removeAttribute('sizes');
        }

        nextImage.alt = altText;

        requestAnimationFrame(() => {
          requestAnimationFrame(() => {
            heroWrap.classList.add('is-transitioning');
          });
        });

        setTimeout(() => {
          heroLink.href = fullUrl;

          currentImage.classList.remove('recipe-hero-image-current');
          currentImage.classList.add('recipe-hero-image-next');

          nextImage.classList.remove('recipe-hero-image-next');
          nextImage.classList.add('recipe-hero-image-current');

          const oldCurrent = currentImage;
          currentImage = nextImage;
          nextImage = oldCurrent;

          nextImage.removeAttribute('src');
          nextImage.removeAttribute('srcset');
          nextImage.removeAttribute('sizes');
          nextImage.alt = '';

          heroWrap.classList.remove('is-transitioning');
          isTransitioning = false;
        }, 250);
      };

      loader.onerror = () => {
        isTransitioning = false;
      };

      loader.src = heroSrc;
    });
  });
}

/**
 * Initialize horizontal scrolling behavior for home page carousels.
 */
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

/**
 * Initialize recipe form enhancements for cuisine limits, ingredients, and steps.
 */
function initRecipeFormEnhancements() {
  const cuisineCheckboxes = document.querySelectorAll('input[name="cuisines[]"]');
  const cuisineMax = 3;
  const addIngredientButton = document.getElementById('add-ingredient-button');
  const addStepButton = document.getElementById('add-step-button');

  /**
   * Update cuisine checkbox disabled states when the maximum selection limit is reached.
   */
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
        label.textContent = `Step ${newIndex + 1} (500 char max)`;
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

/**
 * Show a dismissible toast message on the page.
 *
 * @param {string} message - message text to display
 */
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

/**
 * Add close, timeout, and hover behavior to a toast message.
 *
 * @param {Object} flashToast - toast element to wire
 */
function wireToast(flashToast) {
  const closeButton = flashToast.querySelector('.flash-toast-close');
  let hideTimeout = null;

  /**
   * Hide and remove the toast message from the page.
   */
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

/**
 * Initialize behavior for an existing flash toast message.
 */
function initFlashToast() {
  const flashToast = document.querySelector('.flash-toast');
  if (!flashToast) return;
  wireToast(flashToast);
}

/**
 * Initialize desktop header user menu toggle and close behavior.
 */
function initHeaderUserMenu() {
  const menu = document.querySelector('.header-user-menu');
  const toggle = document.getElementById('header-user-menu-toggle');
  const panel = document.getElementById('header-user-menu-panel');

  if (!menu || !toggle || !panel) return;

  /**
   * Check whether the header is currently using the mobile menu layout.
   * 
   * @return {boolean} true if the mobile menu layout is active
   */
  function isMobileMenuMode() {
    return window.innerWidth <= 850;
  }

  /**
   * Close the desktop user menu.
   */
  function closeMenu() {
    if (isMobileMenuMode()) return;
    menu.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
  }

  /**
   * Open the desktop user menu.
   */
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

/**
 * Initialize responsive placement for the header search form.
 */
function initResponsiveHeaderSearch() {
  const searchForm = document.querySelector('#search-form');
  const siteHeaderBar = document.querySelector('#site-header-bar');
  const menuToggleLabel = document.querySelector('#nav-toggle-label');
  const headerInfoSection = document.querySelector('#header-info-section');

  if (!searchForm || !siteHeaderBar || !menuToggleLabel || !headerInfoSection) return;

  let currentPlacement = '';

  /**
   * Move the header search form to the correct location for the current screen width.
   */
  function placeSearchForm() {
    const width = window.innerWidth;

    if (width < 450) {
      if (currentPlacement !== 'small') {
        siteHeaderBar.parentElement.insertBefore(searchForm, siteHeaderBar);
        currentPlacement = 'small';
      }
    } else if (width <= 850) {
      if (currentPlacement !== 'medium') {
        siteHeaderBar.insertBefore(searchForm, menuToggleLabel);
        currentPlacement = 'medium';
      }
    } else {
      if (currentPlacement !== 'large') {
        headerInfoSection.parentElement.insertBefore(searchForm, headerInfoSection);
        currentPlacement = 'large';
      }
    }
  }

  placeSearchForm();
  window.addEventListener('resize', placeSearchForm);
}

/**
 * Initialize admin category form panels for selecting, editing, and deleting items.
 */
function initAdminCategoryForms() {
  const panels = document.querySelectorAll('[data-category-panel]');
  if (!panels.length) return;

  panels.forEach((panel) => {
    const select = panel.querySelector('[data-category-select]');
    const input = panel.querySelector('[data-category-input]');
    const helper = panel.querySelector('[data-category-helper]');
    const loadButton = panel.querySelector('[data-category-load]');
    const editButton = panel.querySelector('[data-category-edit]');
    const deleteButton = panel.querySelector('[data-category-delete]');

    if (!select || !input || !editButton || !deleteButton) return;

    /**
     * Synchronize the category panel controls with the selected category item.
     */
    function syncPanel() {
      const selectedOption = select.options[select.selectedIndex];
      const hasSelection = !!select.value;

      if (hasSelection && selectedOption) {
        input.value = selectedOption.dataset.name || selectedOption.textContent.trim();
      }

      editButton.disabled = !hasSelection;
      deleteButton.disabled = !hasSelection;

      if (helper) {
        helper.textContent = hasSelection
          ? 'Edit or delete the selected item, or change the text and click Create to add a new one.'
          : 'Select an item to edit or delete, or enter a new name to create one.';
      }

      if (loadButton) {
        loadButton.hidden = true;
      }
    }

    syncPanel();
    select.addEventListener('change', syncPanel);

    deleteButton.addEventListener('click', (event) => {
      if (!select.value) {
        event.preventDefault();
        return;
      }

      const selectedOption = select.options[select.selectedIndex];
      const itemName = selectedOption.dataset.name || selectedOption.textContent.trim();
      const usageCount = Number(selectedOption.dataset.usageCount || '0');

      let message = `Are you sure you want to delete "${itemName}"?`;

      if (usageCount > 0) {
        message = `"${itemName}" is currently assigned to ${usageCount} recipe${usageCount === 1 ? '' : 's'}. Deleting it will remove it from those recipes. Do you want to continue?`;
      }

      if (!window.confirm(message)) {
        event.preventDefault();
      } else {
        let confirmInput = panel.querySelector('input[name="confirm_delete"]');
        if (!confirmInput) {
          confirmInput = document.createElement('input');
          confirmInput.type = 'hidden';
          confirmInput.name = 'confirm_delete';
          confirmInput.value = '1';
          panel.querySelector('form').appendChild(confirmInput);
        } else {
          confirmInput.value = '1';
        }
      }
    });
  });
}

/**
 * Initialize AJAX filtering for the admin user results table.
 */
function initAdminUserFilters() {
  const form = document.querySelector('#admin-user-filters');
  if (!form) return;

  let results = document.querySelector('#admin-user-results');
  if (!results) return;

  const statusSelect = form.querySelector('#admin-user-status');
  const roleSelect = form.querySelector('#admin-user-role');
  const searchInput = form.querySelector('#admin-user-search');
  const resetButton = form.querySelector('.admin-user-reset-button');

  let timeoutId = null;
  let controller = null;

  /**
   * Update admin user results based on the current filter form values.
   */
  async function updateResults() {
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    const query = params.toString();
    const url = query ? `${form.action}?${query}` : form.action;

    if (controller) {
      controller.abort();
    }

    controller = new AbortController();

    try {
      const response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        signal: controller.signal,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) return;

      const html = await response.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const newResults = doc.querySelector('#admin-user-results');

      if (!newResults || !results) return;

      results.replaceWith(newResults);
      results = newResults;

      history.replaceState(null, '', url);
    } catch (error) {
      if (error.name !== 'AbortError') {
        console.error('Failed to update user results.', error);
      }
    }
  }

  if (statusSelect) {
    statusSelect.addEventListener('change', updateResults);
  }

  if (roleSelect) {
    roleSelect.addEventListener('change', updateResults);
  }

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      window.clearTimeout(timeoutId);
      timeoutId = window.setTimeout(updateResults, 300);
    });
  }

  if (resetButton) {
    resetButton.addEventListener('click', () => {
      form.reset();

      if (searchInput) {
        searchInput.value = '';
      }

      if (statusSelect) {
        statusSelect.value = '';
      }

      if (roleSelect) {
        roleSelect.value = '';
      }

      updateResults();
    });
  }
}

/**
 * Initialize print recipe page controls for printing and hiding images.
 */
function initPrintRecipePage() {
  const printPage = document.querySelector('.print-recipe-page');
  if (!printPage) return;

  const printButton = document.querySelector('.print-recipe-print-button');
  const hideImagesToggle = document.getElementById('print-hide-images-toggle');
  const hideImagesLabel = document.querySelector('label[for="print-hide-images-toggle"]');

  /**
   * Synchronize the print image visibility state with the toggle control.
   */
  function syncHideImagesState() {
    if (!hideImagesToggle || !hideImagesLabel) return;

    const imagesHidden = hideImagesToggle.checked;

    document.body.classList.toggle('print-hide-images', imagesHidden);

    if (imagesHidden) {
      hideImagesLabel.textContent = 'Show Image';
      hideImagesLabel.classList.remove('button-danger');
    } else {
      hideImagesLabel.textContent = 'Hide Image';
      hideImagesLabel.classList.add('button-danger');
    }
  }

  if (printButton) {
    printButton.addEventListener('click', () => {
      window.print();
    });
  }

  if (hideImagesToggle) {
    hideImagesToggle.addEventListener('change', syncHideImagesState);
    syncHideImagesState();
  }
}

/**
 * Initialize AJAX controls for dashboard AI recommendations.
 */
function initDashboardAiRecommendations() {
  let section = document.querySelector('#dashboard-ai-recommendations');
  if (!section) return;

  /**
   * Bind dashboard AI recommendation form buttons and submission behavior.
   */
  function bindSection() {
    const form = section.querySelector('.dashboard-ai-form');
    if (!form) return;

    const generateButton = form.querySelector('button[value="generate_ai_recommendations"]');
    const clearButton = form.querySelector('button[value="clear_ai_recommendations"]');

    let pendingAction = '';

    if (generateButton) {
      generateButton.addEventListener('click', () => {
        pendingAction = 'generate_ai_recommendations';
      });
    }

    if (clearButton) {
      clearButton.addEventListener('click', () => {
        pendingAction = 'clear_ai_recommendations';
      });
    }

    form.addEventListener('submit', async (event) => {
      event.preventDefault();

      if (!pendingAction) {
        return;
      }

      const formData = new FormData(form);
      formData.set('action', pendingAction);

      if (pendingAction === 'generate_ai_recommendations' && generateButton) {
        generateButton.textContent = 'Generating...';
        generateButton.classList.add('is-loading');
        generateButton.disabled = true;
      }

      const actionUrl = form.getAttribute('action') || window.location.href;

      try {
        const response = await fetch(actionUrl, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (!response.ok) {
          throw new Error('Request failed.');
        }

        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newSection = doc.querySelector('#dashboard-ai-recommendations');

        if (!newSection) {
          throw new Error('Updated AI section not found.');
        }

        section.replaceWith(newSection);
        section = newSection;
        bindSection();
      } catch (error) {
        console.error('Failed to update AI recommendations.', error);

        if (generateButton) {
          generateButton.textContent = 'Generate Recommendations';
          generateButton.classList.remove('is-loading');
          generateButton.disabled = false;
        }
      } finally {
        pendingAction = '';
      }
    });
  }

  bindSection();
}

/**
 * Initialize all JavaScript functionality for the application.
 */
function init() {
  initRecipeFilters();
  initIngredientScaling();
  initAutoRating();
  initRecipeImageGallery();
  initHomeCarousels();
  initRecipeFormEnhancements();
  initFlashToast();
  initHeaderUserMenu();
  initResponsiveHeaderSearch();
  initAdminCategoryForms();
  initAdminUserFilters();
  initPrintRecipePage();
  initDashboardAiRecommendations();
}

document.body.classList.add('js');
init();
