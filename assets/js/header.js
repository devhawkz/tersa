document.addEventListener('DOMContentLoaded', function () {
  var menuToggle         = document.querySelector('.site-header__toggle');
  var mobileNavigation   = document.getElementById('mobile-navigation');
  var mobileCloseButtons = document.querySelectorAll('[data-mobile-close]');

  var searchToggle       = document.querySelector('[data-search-toggle]');
  var searchOverlay      = document.getElementById('header-search-overlay');
  var searchCloseButtons = document.querySelectorAll('[data-search-close]');
  var searchTitle        = document.getElementById('header-search-title');
  var searchPanel        = searchOverlay
    ? searchOverlay.querySelector('.site-header__search-panel')
    : null;

  // Prevedeni string se čita iz data atributa koje puni PHP — Polylang kompatibilno
  var searchLabel = searchTitle
    ? (searchTitle.dataset.searchLabel || 'Search for products')
    : 'Search for products';

  // Čuva aktivne MutationObservere da ih možemo disconnectovati pri zatvaranju
  var searchObservers = [];

  // Stack za praćenje otvorenih mobilnih podmeni panela: [{ item, label }, ...]
  var mobileNavStack = [];

  // Flag da se mobilni nav klonira samo jednom (pri prvom otvaranju menija)
  var mobileNavPopulated = false;

  // Kašnjenja vezana za CSS animacije — centralizovana da se ne pojavljuju kao magic numbers
  var MOBILE_PANEL_FOCUS_DELAY   = 50;  // ms: čeka da panel postane vidljiv
  var MOBILE_SUBMENU_FOCUS_DELAY = 320; // ms: čeka kraj slide animacije (.3s + buffer)

  // =====================================================================
  // Utility: debounce
  // =====================================================================
  function debounce(fn, wait) {
    var timer;
    return function () {
      clearTimeout(timer);
      timer = setTimeout(fn, wait);
    };
  }

  // =====================================================================
  // Focus trap factory — vraća Tab handler koji drži fokus unutar
  // zadatog kontejnera (WCAG 2.1.1, 2.4.3).
  // Jedan factory umesto dupliranog koda za search i mobilni meni.
  // =====================================================================
  var FOCUSABLE_QUERY = [
    'a[href]:not([tabindex="-1"])',
    'button:not([disabled]):not([tabindex="-1"])',
    'input:not([disabled]):not([tabindex="-1"])',
    'select:not([disabled]):not([tabindex="-1"])',
    'textarea:not([disabled]):not([tabindex="-1"])',
    '[tabindex]:not([tabindex="-1"])',
  ].join(', ');

  /**
   * @param {Element|null} container
   * @returns {Function} keydown handler
   */
  function makeFocusTrap(container) {
    return function (event) {
      if (event.key !== 'Tab' || !container) {
        return;
      }

      var focusable = Array.from(container.querySelectorAll(FOCUSABLE_QUERY)).filter(
        function (el) {
          var rect = el.getBoundingClientRect();
          return rect.width > 0 || rect.height > 0;
        }
      );

      if (!focusable.length) {
        return;
      }

      var first = focusable[0];
      var last  = focusable[focusable.length - 1];

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
    };
  }

  var handleFocusTrap       = makeFocusTrap(searchPanel);
  var handleMobileFocusTrap = makeFocusTrap(mobileNavigation);

  // =====================================================================
  // Desktop submenus — keyboard/click dropdown
  // =====================================================================

  /**
   * Zatvara sve otvorene desktop podmeniije, opciono izuzimajući jedan item.
   *
   * @param {Element|null} exceptItem
   */
  function closeAllSubmenus(exceptItem) {
    document.querySelectorAll('.site-header__nav--desktop .menu-item-has-children.is-submenu-open').forEach(function (item) {
      if (item === exceptItem) {
        return;
      }

      item.classList.remove('is-submenu-open');

      var btn = item.querySelector(':scope > .submenu-toggle');
      if (btn) {
        btn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /**
   * Inicijalizuje desktop podmeniije: kreira toggle dugme i dropdown logiku.
   * Mobilni podmeniiji se inicijalizuju odvojeno via event delegacijom
   * (kloniran DOM ne nasleđuje event listenere).
   */
  function initSubmenus() {
    var submenuItems = document.querySelectorAll('.site-header__nav--desktop .menu-item-has-children');

    submenuItems.forEach(function (item) {
      var parentLink = item.querySelector(':scope > a');
      if (!parentLink) {
        return;
      }

      var nav           = parentLink.closest('.site-header__nav');
      var labelTemplate = nav ? (nav.dataset.submenuLabel || 'Open submenu for %s') : 'Open submenu for %s';
      var ariaLabel     = labelTemplate.replace('%s', parentLink.textContent.trim());

      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'submenu-toggle';
      btn.setAttribute('aria-expanded', 'false');
      btn.setAttribute('aria-label', ariaLabel);

      var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      svg.setAttribute('viewBox', '0 0 24 24');
      svg.setAttribute('aria-hidden', 'true');
      svg.setAttribute('focusable', 'false');

      var polyline = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
      polyline.setAttribute('points', '6 9 12 15 18 9');
      svg.appendChild(polyline);
      btn.appendChild(svg);

      parentLink.insertAdjacentElement('afterend', btn);

      btn.addEventListener('click', function (event) {
        event.stopPropagation();
        var isOpen = btn.getAttribute('aria-expanded') === 'true';
        closeAllSubmenus(item);
        btn.setAttribute('aria-expanded', String(!isOpen));
        item.classList.toggle('is-submenu-open', !isOpen);
      });
    });

    // Click outside zatvara desktop podmeniije
    if (submenuItems.length) {
      document.addEventListener('click', function (event) {
        if (!event.target.closest('.site-header__nav--desktop .menu-item-has-children')) {
          closeAllSubmenus();
        }
      });
    }
  }

  // =====================================================================
  // Mobile submenus — event delegation
  // Kloniran DOM nema event listenere — delegacija rešava problem za sve dubine.
  // =====================================================================
  function initMobileSubmenus() {
    if (!mobileNavigation) {
      return;
    }

    mobileNavigation.addEventListener('click', function (event) {
      var btn = event.target.closest('.submenu-toggle');
      if (!btn) {
        return;
      }

      event.stopPropagation();

      var item = btn.closest('.menu-item-has-children');
      if (!item) {
        return;
      }

      var parentLink = item.querySelector(':scope > a');
      var label = parentLink ? parentLink.textContent.trim() : '';
      openMobileSubmenu(item, label);
    });
  }

  // =====================================================================
  // Populate mobile nav — klonira desktop nav pri prvom otvaranju
  // Desktop nav je jedini u HTML izvoru — Googlebot vidi linkove samo jednom.
  // =====================================================================
  function populateMobileNav() {
    if (mobileNavPopulated || !mobileNavigation) {
      return;
    }

    var mobileNavBody = mobileNavigation.querySelector('.mobile-nav__body');
    if (!mobileNavBody) {
      return;
    }

    var desktopNav = document.querySelector('.site-header__nav--desktop');
    if (!desktopNav) {
      return;
    }

    var mobileNav = desktopNav.cloneNode(true);
    mobileNav.className = 'site-header__nav site-header__nav--mobile';

    // Postavi prevedeni aria-label iz data atributa na panelu (Polylang kompatibilno)
    var mobileNavLabel = mobileNavigation.dataset.mobileNavLabel || 'Mobile navigation';
    mobileNav.setAttribute('aria-label', mobileNavLabel);

    // Desktop-specifičan atribut nije potreban u mobilnom klonu
    mobileNav.removeAttribute('data-submenu-label');

    // Resetuj stanje kloniranih toggle dugmića i otvorenih stavki
    mobileNav.querySelectorAll('.submenu-toggle').forEach(function (btn) {
      btn.setAttribute('aria-expanded', 'false');
    });
    mobileNav.querySelectorAll('.menu-item-has-children.is-submenu-open').forEach(function (li) {
      li.classList.remove('is-submenu-open');
    });

    mobileNavBody.appendChild(mobileNav);
    mobileNavPopulated = true;
  }

  // =====================================================================
  // Mobile menu — open / close sa CSS animacijom
  // =====================================================================
  function openMobileMenu() {
    if (!menuToggle || !mobileNavigation) {
      return;
    }

    // Klonira desktop nav u mobilni panel (samo pri prvom otvaranju)
    populateMobileNav();

    mobileNavigation.removeAttribute('inert');
    mobileNavigation.classList.add('is-open');

    menuToggle.setAttribute('aria-expanded', 'true');
    menuToggle.setAttribute('aria-label', menuToggle.dataset.closeLabel || 'Close menu');
    menuToggle.classList.add('is-active');

    document.body.style.overflow = 'hidden';
    document.addEventListener('keydown', handleMobileFocusTrap);

    // Pomeri fokus na ✕ dugme unutar panela (WCAG 2.4.3)
    var closeBtn = mobileNavigation.querySelector('.mobile-nav__close');
    if (closeBtn) {
      setTimeout(function () { closeBtn.focus(); }, MOBILE_PANEL_FOCUS_DELAY);
    }
  }

  function closeMobileMenu() {
    if (!menuToggle || !mobileNavigation) {
      return;
    }

    mobileNavigation.classList.remove('is-open');
    document.removeEventListener('keydown', handleMobileFocusTrap);

    menuToggle.setAttribute('aria-expanded', 'false');
    menuToggle.setAttribute('aria-label', menuToggle.dataset.openLabel || 'Open menu');
    menuToggle.classList.remove('is-active');

    // cleanup se poziva iz transitionend ILI iz timeout fallbacka
    // (timeout je neophodan za prefers-reduced-motion gde transition: none
    //  znači da transitionend nikad ne okine)
    var cleanupDone = false;
    function cleanup() {
      if (cleanupDone) {
        return;
      }
      cleanupDone = true;
      mobileNavigation.setAttribute('inert', '');
      resetMobileNav();
      document.body.style.overflow = '';
      menuToggle.focus();
    }

    mobileNavigation.addEventListener('transitionend', function onEnd(e) {
      // Ignoriši visibility transition — čekamo samo transform
      if (e.target !== mobileNavigation || e.propertyName !== 'transform') {
        return;
      }
      mobileNavigation.removeEventListener('transitionend', onEnd);
      cleanup();
    });

    // Fallback: transitionend ne okida ako je transition:none (prefers-reduced-motion)
    setTimeout(cleanup, 400);
  }

  // =====================================================================
  // Mobile submenu — push navigacija
  // =====================================================================

  /**
   * Otvara podmeni u mobilnom meniju (klizi s desna).
   *
   * @param {Element} item  - Li element koji ima podmeni
   * @param {string}  label - Tekst parent linka
   */
  function openMobileSubmenu(item, label) {
    var subMenu = item.querySelector(':scope > .sub-menu');
    if (!subMenu) {
      return;
    }

    // Višestruki nivoi: svaki dublji panel dobija viši z-index
    subMenu.style.zIndex = 401 + mobileNavStack.length;

    item.classList.add('is-submenu-open');
    mobileNavStack.push({ item: item, label: label });

    // Pomeri fokus na prvi link u sub-panelu nakon što animacija završi (WCAG 2.4.3)
    setTimeout(function () {
      var firstLink = subMenu.querySelector('a');
      if (firstLink) {
        firstLink.focus();
      }
    }, MOBILE_SUBMENU_FOCUS_DELAY);
  }

  /**
   * Zatvara poslednji otvoreni mobilni podmeni (jedan korak nazad).
   */
  function closeMobileSubmenu() {
    if (mobileNavStack.length === 0) {
      return;
    }

    var top = mobileNavStack.pop();
    top.item.classList.remove('is-submenu-open');

    // Resetuj z-index tek nakon što animacija završi
    var subMenu = top.item.querySelector(':scope > .sub-menu');
    if (subMenu) {
      subMenu.addEventListener('transitionend', function onEnd() {
        subMenu.removeEventListener('transitionend', onEnd);
        subMenu.style.zIndex = '';
      });
    }

    // Vrati fokus na toggle dugme koje je otvorilo podmeni (WCAG 2.4.3)
    var toggleBtn = top.item.querySelector(':scope > .submenu-toggle');
    if (toggleBtn) {
      toggleBtn.focus();
    }
  }

  /**
   * Zatvara sve mobilne podmeniije i resetuje stanje navigacije.
   */
  function resetMobileNav() {
    mobileNavStack.forEach(function (entry) {
      entry.item.classList.remove('is-submenu-open');
      var subMenu = entry.item.querySelector(':scope > .sub-menu');
      if (subMenu) {
        subMenu.style.zIndex = '';
      }
    });
    mobileNavStack = [];
  }

  // =====================================================================
  // Broj AWS rezultata
  // =====================================================================
  function getAwsResultCount() {
    if (!searchOverlay) {
      return 0;
    }

    var possibleSelectors = [
      '.aws-search-result .aws_result_item',
      '.aws-search-results .aws_result_item',
      '.aws-container .aws_result_item',
      '.aws-container .aws-search-result li',
      '.aws-container .aws-search-results li',
    ];

    for (var i = 0; i < possibleSelectors.length; i++) {
      var items = searchOverlay.querySelectorAll(possibleSelectors[i]);

      if (items.length) {
        return Array.from(items).filter(function (item) {
          var rect = item.getBoundingClientRect();
          return rect.width > 0 || rect.height > 0;
        }).length;
      }
    }

    return 0;
  }

  function updateSearchTitle() {
    if (!searchTitle) {
      return;
    }

    searchTitle.textContent = searchLabel + ' (' + getAwsResultCount() + ')';
  }

  var debouncedUpdateSearchTitle = debounce(updateSearchTitle, 150);

  // =====================================================================
  // AWS MutationObserveri
  // =====================================================================
  function disconnectSearchObservers() {
    searchObservers.forEach(function (obs) { obs.disconnect(); });
    searchObservers.length = 0;

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

    var awsInput = searchOverlay.querySelector(
      '.aws-search-field, input[type="search"], input[type="text"]'
    );

    if (awsInput && !awsInput.dataset.tersaBound) {
      awsInput.dataset.tersaBound = 'true';
      awsInput.addEventListener('input', debouncedUpdateSearchTitle);
      awsInput.addEventListener('focus', debouncedUpdateSearchTitle);
    }

    var awsContainers = searchOverlay.querySelectorAll(
      '.aws-container, .aws-search-result, .aws-search-results'
    );

    awsContainers.forEach(function (container) {
      if (container.dataset.tersaObserved) {
        return;
      }

      container.dataset.tersaObserved = 'true';

      var observer = new MutationObserver(function () {
        requestAnimationFrame(updateSearchTitle);
      });

      // attributeFilter ograničava okidanje — prati samo relevantne promene
      observer.observe(container, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'hidden', 'style'],
      });
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
    document.addEventListener('keydown', handleFocusTrap);

    var searchInput = searchOverlay.querySelector(
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
    menuToggle.addEventListener('click', function () {
      if (menuToggle.getAttribute('aria-expanded') === 'true') {
        closeMobileMenu();
      } else {
        openMobileMenu();
      }
    });
  }

  mobileCloseButtons.forEach(function (btn) {
    btn.addEventListener('click', closeMobileMenu);
  });

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

    // Mobilni podmeni ima prioritet
    if (mobileNavStack.length > 0) {
      closeMobileSubmenu();
      return;
    }

    // Desktop podmeni
    var openSubmenuItem = document.querySelector('.site-header__nav--desktop .menu-item-has-children.is-submenu-open');
    if (openSubmenuItem) {
      closeAllSubmenus();
      var submenuBtn = openSubmenuItem.querySelector(':scope > .submenu-toggle');
      if (submenuBtn) {
        submenuBtn.focus();
      }
      return;
    }

    if (searchOverlay && !searchOverlay.hidden) {
      closeSearch();
    } else if (mobileNavigation && mobileNavigation.classList.contains('is-open')) {
      closeMobileMenu();
    }
  });

  // =====================================================================
  // Init
  // =====================================================================
  initSubmenus();
  initMobileSubmenus();
});
