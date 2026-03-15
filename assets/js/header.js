document.addEventListener('DOMContentLoaded', function () {
  const menuToggle       = document.querySelector('.site-header__toggle');
  const mobileNavigation = document.getElementById('mobile-navigation');

  const searchToggle      = document.querySelector('[data-search-toggle]');
  const searchOverlay     = document.getElementById('header-search-overlay');
  const searchCloseButtons = document.querySelectorAll('[data-search-close]');
  const searchTitle       = document.getElementById('header-search-title');
  const searchPanel       = searchOverlay
    ? searchOverlay.querySelector('.site-header__search-panel')
    : null;

  // Prevedeni string se čita iz data atributa koje puni PHP — Polylang kompatibilno
  const searchLabel = searchTitle
    ? (searchTitle.dataset.searchLabel || 'Search for products')
    : 'Search for products';

  // Čuva aktivne MutationObservere da ih možemo disconnectovati pri zatvaranju
  const searchObservers = [];

  // =====================================================================
  // Focus trap — zadržava fokus unutar search dialoga (WCAG 2.1.1, 2.4.3)
  // =====================================================================
  const FOCUSABLE_QUERY = [
    'a[href]:not([tabindex="-1"])',
    'button:not([disabled]):not([tabindex="-1"])',
    'input:not([disabled]):not([tabindex="-1"])',
    'select:not([disabled]):not([tabindex="-1"])',
    'textarea:not([disabled]):not([tabindex="-1"])',
    '[tabindex]:not([tabindex="-1"])',
  ].join(', ');

  function handleFocusTrap(event) {
    if (event.key !== 'Tab' || !searchPanel) {
      return;
    }

    const focusable = Array.from(searchPanel.querySelectorAll(FOCUSABLE_QUERY)).filter(
      function (el) { return el.offsetParent !== null; }
    );

    if (!focusable.length) {
      return;
    }

    const first = focusable[0];
    const last  = focusable[focusable.length - 1];

    if (event.shiftKey) {
      if (document.activeElement === first) {
        event.preventDefault();
        last.focus();
      }
    } else {
      if (document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  }

  // =====================================================================
  // Mobile menu
  // =====================================================================
  function openMobileMenu() {
    if (!menuToggle || !mobileNavigation) {
      return;
    }

    menuToggle.setAttribute('aria-expanded', 'true');
    menuToggle.setAttribute('aria-label', menuToggle.dataset.closeLabel || 'Close menu');
    menuToggle.classList.add('is-active');
    mobileNavigation.hidden = false;
  }

  function closeMobileMenu() {
    if (!menuToggle || !mobileNavigation) {
      return;
    }

    menuToggle.setAttribute('aria-expanded', 'false');
    menuToggle.setAttribute('aria-label', menuToggle.dataset.openLabel || 'Open menu');
    menuToggle.classList.remove('is-active');
    mobileNavigation.hidden = true;
  }

  function toggleMobileMenu() {
    if (!menuToggle || !mobileNavigation) {
      return;
    }

    if (menuToggle.getAttribute('aria-expanded') === 'true') {
      closeMobileMenu();
    } else {
      openMobileMenu();
    }
  }

  // =====================================================================
  // Broj AWS rezultata
  // =====================================================================
  function getAwsResultCount() {
    if (!searchOverlay) {
      return 0;
    }

    const possibleSelectors = [
      '.aws-search-result .aws_result_item',
      '.aws-search-results .aws_result_item',
      '.aws-container .aws_result_item',
      '.aws-container .aws-search-result li',
      '.aws-container .aws-search-results li',
    ];

    for (const selector of possibleSelectors) {
      const items = searchOverlay.querySelectorAll(selector);

      if (items.length) {
        return Array.from(items).filter(function (item) {
          return item.offsetParent !== null;
        }).length;
      }
    }

    return 0;
  }

  function updateSearchTitle() {
    if (!searchTitle) {
      return;
    }

    // Koristi prevedeni searchLabel iz data atributa — ne hard-coded engleski string
    searchTitle.textContent = searchLabel + ' (' + getAwsResultCount() + ')';
  }

  // =====================================================================
  // AWS MutationObserveri — prate promene rezultata u DOM-u
  // =====================================================================
  function disconnectSearchObservers() {
    searchObservers.forEach(function (obs) { obs.disconnect(); });
    searchObservers.length = 0;

    // Resetuj flags da se observeri mogu ponovo priključiti pri sledećem otvaranju
    if (searchOverlay) {
      searchOverlay.querySelectorAll('[data-tersa-observed]').forEach(function (el) {
        delete el.dataset.tersaObserved;
      });
    }
  }

  function attachSearchObservers() {
    if (!searchOverlay) {
      return;
    }

    const awsInput = searchOverlay.querySelector(
      '.aws-search-field, input[type="search"], input[type="text"]'
    );

    if (awsInput && !awsInput.dataset.tersaBound) {
      awsInput.dataset.tersaBound = 'true';

      awsInput.addEventListener('input', function () {
        setTimeout(updateSearchTitle, 120);
      });

      awsInput.addEventListener('focus', function () {
        setTimeout(updateSearchTitle, 120);
      });
    }

    const awsContainers = searchOverlay.querySelectorAll(
      '.aws-container, .aws-search-result, .aws-search-results'
    );

    awsContainers.forEach(function (container) {
      if (container.dataset.tersaObserved) {
        return;
      }

      container.dataset.tersaObserved = 'true';

      const observer = new MutationObserver(function () {
        requestAnimationFrame(updateSearchTitle);
      });

      observer.observe(container, { childList: true, subtree: true, attributes: true });
      searchObservers.push(observer);
    });
  }

  // =====================================================================
  // Search open / close
  // =====================================================================
  function openSearch() {
    if (!searchToggle || !searchOverlay) {
      return;
    }

    searchOverlay.hidden = false;
    searchToggle.setAttribute('aria-expanded', 'true');
    searchToggle.classList.add('is-active');

    document.documentElement.classList.add('is-search-open');
    document.body.classList.add('is-search-open');

    attachSearchObservers();
    updateSearchTitle();

    // Aktiviraj focus trap unutar dialoga
    document.addEventListener('keydown', handleFocusTrap);

    const searchInput = searchOverlay.querySelector(
      '.aws-search-field, input[type="search"], input[type="text"]'
    );

    if (searchInput) {
      setTimeout(function () {
        searchInput.focus();
        updateSearchTitle();
      }, 30);
    }
  }

  function closeSearch(returnFocus) {
    if (returnFocus === undefined) {
      returnFocus = true;
    }

    if (!searchToggle || !searchOverlay) {
      return;
    }

    searchOverlay.hidden = true;
    searchToggle.setAttribute('aria-expanded', 'false');
    searchToggle.classList.remove('is-active');

    document.documentElement.classList.remove('is-search-open');
    document.body.classList.remove('is-search-open');

    // Ukloni focus trap i disconnectuj observere
    document.removeEventListener('keydown', handleFocusTrap);
    disconnectSearchObservers();

    if (searchTitle) {
      searchTitle.textContent = searchLabel + ' (0)';
    }

    if (returnFocus) {
      searchToggle.focus();
    }
  }

  function toggleSearch() {
    if (!searchToggle || !searchOverlay) {
      return;
    }

    if (searchToggle.getAttribute('aria-expanded') === 'true') {
      closeSearch();
    } else {
      openSearch();
    }
  }

  // =====================================================================
  // Event listeners
  // =====================================================================
  if (menuToggle && mobileNavigation) {
    menuToggle.addEventListener('click', toggleMobileMenu);
  }

  if (searchToggle && searchOverlay) {
    searchToggle.addEventListener('click', function (event) {
      event.preventDefault();
      toggleSearch();
    });

    searchCloseButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        closeSearch();
      });
    });
  }

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
      return;
    }

    if (searchOverlay && !searchOverlay.hidden) {
      closeSearch();
    } else if (mobileNavigation && !mobileNavigation.hidden) {
      closeMobileMenu();
    }
  });
});
