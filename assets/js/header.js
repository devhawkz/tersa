document.addEventListener('DOMContentLoaded', function () {
  // ─── Sticky shrink header on scroll ──────────────────────────────────
  // Smanjuje visinu i veličinu logotipa nakon što korisnik skrola dolje,
  // kako sticky header ne bi zaklanjao previše vidljive površine.
  (function initStickyShrink() {
    var siteHeader = document.querySelector('.site-header');
    if (!siteHeader) {
      return;
    }

    var SCROLL_ENTER_THRESHOLD = 96;
    var SCROLL_EXIT_THRESHOLD = 48;
    var ticking = false;
    var isScrolled = false;

    function applyState() {
      ticking = false;
      var nextScrolled = isScrolled
        ? window.scrollY > SCROLL_EXIT_THRESHOLD
        : window.scrollY > SCROLL_ENTER_THRESHOLD;

      if (nextScrolled === isScrolled) {
        return;
      }
      isScrolled = nextScrolled;
      siteHeader.classList.toggle('is-scrolled', isScrolled);
    }

    function onScroll() {
      if (ticking) {
        return;
      }
      ticking = true;
      window.requestAnimationFrame(applyState);
    }

    applyState();
    window.addEventListener('scroll', onScroll, { passive: true });
  })();

  var menuToggle = document.querySelector('.site-header__toggle');
  var mobileNavigation = document.getElementById('mobile-navigation');
  var mobileBackdrop = document.querySelector('.site-header__mobile-backdrop');
  var mobileCloseButtons = document.querySelectorAll('[data-mobile-close]');
  var mobileBackButton = document.querySelector('[data-mobile-back]');
  var mobileBody = mobileNavigation ? mobileNavigation.querySelector('.mobile-nav__body') : null;

  var searchToggle = document.querySelector('[data-search-toggle]');
  var searchOverlay = document.getElementById('header-search-overlay');
  var searchPanel = searchOverlay ? searchOverlay.querySelector('.site-header__search-panel') : null;
  var searchCloseButtons = document.querySelectorAll('[data-search-close]');
  var searchTitle = document.getElementById('header-search-title');

  var cartToggle = document.querySelector('[data-cart-toggle]');
  var cartOverlay = document.getElementById('cart-drawer');
  var cartPanel = cartOverlay ? cartOverlay.querySelector('.site-header__cart-panel') : null;
  var cartCloseButtons = document.querySelectorAll('[data-cart-close]');

  var searchObservers = [];
  var mobileNavPopulated = false;
  var mobileNavStack = [];
  // Drawer se smatra hidriranim ako je PHP (SSR) ili WC fragment sistem
  // već popunio sadržaj — u tom slučaju preskačemo AJAX poziv pri otvaranju.
  var cartDrawerContentEl = document.querySelector('#cart-drawer .widget_shopping_cart_content');
  var cartDrawerHydrated = !!(cartDrawerContentEl && !cartDrawerContentEl.querySelector('[data-cart-loading-message]'));

  var FOCUSABLE_SELECTOR = [
    'a[href]:not([tabindex="-1"])',
    'button:not([disabled]):not([tabindex="-1"])',
    'input:not([disabled]):not([tabindex="-1"])',
    'select:not([disabled]):not([tabindex="-1"])',
    'textarea:not([disabled]):not([tabindex="-1"])',
    '[tabindex]:not([tabindex="-1"])'
  ].join(', ');

  function debounce(fn, wait) {
    var timeout;
    return function () {
      var args = arguments;
      clearTimeout(timeout);
      timeout = setTimeout(function () {
        fn.apply(null, args);
      }, wait);
    };
  }

  function getHeaderText(key, fallback) {
    var i18n = window.tersaHeaderI18n || {};
    return typeof i18n[key] === 'string' && i18n[key] !== '' ? i18n[key] : fallback;
  }

  function appendCartLanguage(body) {
    var cfg = window.tersaCartDrawer || {};
    if (cfg.lang && typeof cfg.lang === 'string') {
      body.append('lang', cfg.lang);
    }
  }

  function getFocusableElements(container) {
    if (!container) {
      return [];
    }

    return Array.from(container.querySelectorAll(FOCUSABLE_SELECTOR)).filter(function (el) {
      var rect = el.getBoundingClientRect();
      return rect.width > 0 || rect.height > 0;
    });
  }

  function createFocusTrap(container) {
    return function (event) {
      if (event.key !== 'Tab') {
        return;
      }

      var focusable = getFocusableElements(container);
      if (!focusable.length) {
        return;
      }

      var first = focusable[0];
      var last = focusable[focusable.length - 1];

      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    };
  }

  var trapSearch = createFocusTrap(searchPanel);
  var trapCart = createFocusTrap(cartPanel);
  var trapMobile = createFocusTrap(mobileNavigation);

  function toSameOriginRelative(candidate) {
    if (typeof candidate !== 'string' || candidate.trim() === '') {
      return '';
    }
    if (candidate.charAt(0) === '/') {
      return candidate;
    }
    try {
      var parsed = new URL(candidate, window.location.href);
      if (parsed.origin !== window.location.origin) {
        return '';
      }
      return parsed.pathname + parsed.search;
    } catch (e) {
      return '';
    }
  }

  // `kind` = 'fragments' | 'qty' | 'admin'
  // Preferiramo wc-ajax (lakša ruta od admin-ajax.php); fallback je admin-ajax.
  function getCartAjaxUrl(kind) {
    var cfg = window.tersaCartDrawer || {};

    if (kind === 'fragments') {
      var wcFragments = toSameOriginRelative(cfg.wcAjaxFragments);
      if (wcFragments) return wcFragments;
    } else if (kind === 'qty') {
      var wcQty = toSameOriginRelative(cfg.wcAjaxQty);
      if (wcQty) return wcQty;
    }

    var relative = cfg.ajaxUrlRelative;
    if (typeof relative === 'string' && relative.charAt(0) === '/') {
      return relative;
    }

    var admin = toSameOriginRelative(cfg.ajaxUrl);
    return admin || '/wp-admin/admin-ajax.php';
  }

  function revealOverlay(overlay) {
    if (!overlay) {
      return;
    }

    overlay.hidden = false;

    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        overlay.classList.add('is-open');
      });
    });
  }

  function hideOverlay(overlay, panel, callback) {
    if (!overlay) {
      return;
    }

    overlay.classList.remove('is-open');

    var finished = false;

    function done() {
      if (finished) {
        return;
      }
      finished = true;
      overlay.hidden = true;
      if (typeof callback === 'function') {
        callback();
      }
    }

    if (panel) {
      panel.addEventListener('transitionend', function onEnd(event) {
        if (event.propertyName !== 'transform') {
          return;
        }
        panel.removeEventListener('transitionend', onEnd);
        done();
      });
    }

    setTimeout(done, 400);
  }

  function closeOtherPanels(except) {
    if (except !== 'search' && searchOverlay && !searchOverlay.hidden) {
      closeSearch(false);
    }

    if (except !== 'cart' && cartOverlay && !cartOverlay.hidden) {
      closeCart(false);
    }

    if (except !== 'mobile' && mobileNavigation && mobileNavigation.classList.contains('is-open')) {
      closeMobileMenu(false);
    }
  }

  function lockScroll() {
    document.body.style.overflow = 'hidden';
  }

  function unlockScroll() {
    if (
      (searchOverlay && !searchOverlay.hidden) ||
      (cartOverlay && !cartOverlay.hidden) ||
      (mobileNavigation && mobileNavigation.classList.contains('is-open'))
    ) {
      return;
    }

    document.body.style.overflow = '';
  }

  function closeAllDesktopSubmenus(exceptItem) {
    document.querySelectorAll('.site-header__nav--desktop .menu-item-has-children.is-submenu-open').forEach(function (item) {
      if (exceptItem && item === exceptItem) {
        return;
      }

      item.classList.remove('is-submenu-open');

      var toggle = item.querySelector(':scope > .submenu-toggle');
      if (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function buildSubmenuToggle(label) {
    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'submenu-toggle';
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('aria-label', label);

    button.innerHTML =
      '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' +
      '<polyline points="6 9 12 15 18 9"></polyline>' +
      '</svg>';

    return button;
  }

  function initDesktopSubmenus() {
    var items = document.querySelectorAll('.site-header__nav--desktop .menu-item-has-children');

    items.forEach(function (item) {
      if (item.querySelector(':scope > .submenu-toggle')) {
        return;
      }

      var link = item.querySelector(':scope > a');
      if (!link) {
        return;
      }

      var nav = item.closest('.site-header__nav');
      var template = nav ? nav.getAttribute('data-submenu-label') : '';
      var fallback = getHeaderText('openSubmenuFor', 'Open submenu for %s');
      var labelTemplate = template || fallback;
      var label = labelTemplate.replace('%s', link.textContent.trim());

      var toggle = buildSubmenuToggle(label);
      link.insertAdjacentElement('afterend', toggle);

      toggle.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var isOpen = toggle.getAttribute('aria-expanded') === 'true';

        closeAllDesktopSubmenus(item);

        toggle.setAttribute('aria-expanded', String(!isOpen));
        item.classList.toggle('is-submenu-open', !isOpen);
      });
    });

    document.addEventListener('click', function (event) {
      if (!event.target.closest('.site-header__nav--desktop .menu-item-has-children')) {
        closeAllDesktopSubmenus();
      }
    });
  }

  function populateMobileNav() {
    if (mobileNavPopulated || !mobileBody) {
      return;
    }

    var desktopNav = document.querySelector('.site-header__nav--desktop');
    if (!desktopNav) {
      return;
    }

    var clone = desktopNav.cloneNode(true);
    clone.className = 'site-header__nav site-header__nav--mobile';
    clone.removeAttribute('data-submenu-label');

    clone.querySelectorAll('.menu-item-has-children').forEach(function (item) {
      var link = item.querySelector(':scope > a');
      if (!link || item.querySelector(':scope > .submenu-toggle')) {
        return;
      }

      var openSubmenuTpl = getHeaderText('openSubmenuFor', 'Open submenu for %s');
      var label = openSubmenuTpl.replace('%s', link.textContent.trim());
      var toggle = buildSubmenuToggle(label);
      link.insertAdjacentElement('afterend', toggle);
    });

    mobileBody.appendChild(clone);
    mobileNavPopulated = true;
  }

  function resetMobileNav() {
    if (!mobileNavigation) {
      return;
    }

    mobileNavigation.querySelectorAll('.menu-item-has-children.is-submenu-open').forEach(function (item) {
      item.classList.remove('is-submenu-open');
    });

    mobileNavigation.querySelectorAll('.submenu-toggle[aria-expanded="true"]').forEach(function (toggle) {
      toggle.setAttribute('aria-expanded', 'false');
    });

    mobileNavStack = [];
  }

  function updateMobileBackButton() {
    if (!mobileBackButton) {
      return;
    }

    if (mobileNavStack.length > 0) {
      mobileBackButton.hidden = false;
    } else {
      mobileBackButton.hidden = true;
    }
  }

  function openMobileSubmenu(item) {
    var submenu = item.querySelector(':scope > .sub-menu');
    var toggle = item.querySelector(':scope > .submenu-toggle');

    if (!submenu || !toggle) {
      return;
    }

    item.classList.add('is-submenu-open');
    toggle.setAttribute('aria-expanded', 'true');
    mobileNavStack.push(item);
    updateMobileBackButton();

    setTimeout(function () {
      var firstLink = submenu.querySelector('a');
      if (firstLink) {
        firstLink.focus();
      }
    }, 320);
  }

  function closeMobileSubmenu() {
    var item = mobileNavStack.pop();
    if (!item) {
      updateMobileBackButton();
      return;
    }

    var toggle = item.querySelector(':scope > .submenu-toggle');
    item.classList.remove('is-submenu-open');

    if (toggle) {
      toggle.setAttribute('aria-expanded', 'false');
      toggle.focus();
    }

    updateMobileBackButton();
  }

  function initMobileSubmenus() {
    if (!mobileNavigation) {
      return;
    }

    mobileNavigation.addEventListener('click', function (event) {
      var toggle = event.target.closest('.submenu-toggle');
      if (!toggle) {
        return;
      }

      var item = toggle.closest('.menu-item-has-children');
      if (!item) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();

      openMobileSubmenu(item);
    });

    if (mobileBackButton) {
      mobileBackButton.addEventListener('click', function () {
        closeMobileSubmenu();
      });
    }
  }

  function openMobileMenu() {
    if (!menuToggle || !mobileNavigation) {
      return;
    }

    closeOtherPanels('mobile');
    populateMobileNav();
    updateMobileBackButton();

    mobileNavigation.removeAttribute('inert');
    mobileNavigation.classList.add('is-open');

    if (mobileBackdrop) {
      mobileBackdrop.classList.add('is-open');
    }

    menuToggle.setAttribute('aria-expanded', 'true');
    menuToggle.classList.add('is-active');

    lockScroll();
    document.addEventListener('keydown', trapMobile);

    var closeButton = mobileNavigation.querySelector('.mobile-nav__close');
    if (closeButton) {
      setTimeout(function () {
        closeButton.focus();
      }, 40);
    }
  }

  function closeMobileMenu(returnFocus) {
    if (returnFocus === undefined) {
      returnFocus = true;
    }

    if (!menuToggle || !mobileNavigation) {
      return;
    }

    mobileNavigation.classList.remove('is-open');
    mobileNavigation.setAttribute('inert', '');

    if (mobileBackdrop) {
      mobileBackdrop.classList.remove('is-open');
    }

    menuToggle.setAttribute('aria-expanded', 'false');
    menuToggle.classList.remove('is-active');

    document.removeEventListener('keydown', trapMobile);

    setTimeout(function () {
      resetMobileNav();
      unlockScroll();

      if (returnFocus) {
        menuToggle.focus();
      }
    }, 350);
  }

  function getAwsResultCount() {
    if (!searchOverlay) {
      return 0;
    }

    var selectors = [
      '.aws-search-result .aws_result_item',
      '.aws-search-results .aws_result_item',
      '.aws-container .aws_result_item',
      '.aws-container .aws-search-result li',
      '.aws-container .aws-search-results li'
    ];

    for (var i = 0; i < selectors.length; i++) {
      var items = searchOverlay.querySelectorAll(selectors[i]);
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

    var baseLabel = searchTitle.getAttribute('data-search-label') || getHeaderText('searchResults', 'Search results');
    var count = getAwsResultCount();
    searchTitle.textContent = count > 0 ? baseLabel + ' (' + count + ')' : baseLabel;
  }

  var debouncedUpdateSearchTitle = debounce(updateSearchTitle, 150);

  function disconnectSearchObservers() {
    searchObservers.forEach(function (observer) {
      observer.disconnect();
    });
    searchObservers = [];
  }

  function attachSearchObservers() {
    if (!searchOverlay) {
      return;
    }

    var input = searchOverlay.querySelector('.aws-search-field, input[type="search"], input[type="text"]');
    if (input && !input.dataset.tersaBound) {
      input.dataset.tersaBound = 'true';
      input.addEventListener('input', debouncedUpdateSearchTitle);
      input.addEventListener('focus', debouncedUpdateSearchTitle);
    }

    var containers = searchOverlay.querySelectorAll('.aws-container, .aws-search-result, .aws-search-results');
    containers.forEach(function (container) {
      if (container.dataset.tersaObserved) {
        return;
      }

      container.dataset.tersaObserved = 'true';

      var observer = new MutationObserver(function () {
        requestAnimationFrame(updateSearchTitle);
      });

      observer.observe(container, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'hidden', 'style']
      });

      searchObservers.push(observer);
    });
  }

  function openSearch() {
    if (!searchToggle || !searchOverlay) {
      return;
    }

    closeOtherPanels('search');
    revealOverlay(searchOverlay);

    searchToggle.setAttribute('aria-expanded', 'true');
    searchToggle.classList.add('is-active');

    document.documentElement.classList.add('is-search-open');
    document.body.classList.add('is-search-open');

    attachSearchObservers();
    updateSearchTitle();

    lockScroll();
    document.addEventListener('keydown', trapSearch);

    var input = searchOverlay.querySelector('.aws-search-field, input[type="search"], input[type="text"]');
    if (input) {
      setTimeout(function () {
        input.focus();
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

    searchToggle.setAttribute('aria-expanded', 'false');
    searchToggle.classList.remove('is-active');

    document.documentElement.classList.remove('is-search-open');
    document.body.classList.remove('is-search-open');

    document.removeEventListener('keydown', trapSearch);
    disconnectSearchObservers();

    hideOverlay(searchOverlay, searchPanel, function () {
      unlockScroll();

      if (returnFocus) {
        searchToggle.focus();
      }
    });
  }

  function toggleSearch(event) {
    if (event) {
      event.preventDefault();
    }

    if (!searchToggle || !searchOverlay) {
      return;
    }

    if (searchToggle.getAttribute('aria-expanded') === 'true') {
      closeSearch();
    } else {
      openSearch();
    }
  }

  function openCart() {
    if (!cartToggle || !cartOverlay) {
      return;
    }

    closeOtherPanels('cart');
    revealOverlay(cartOverlay);

    cartToggle.setAttribute('aria-expanded', 'true');
    cartToggle.classList.add('is-active');

    if (!cartDrawerHydrated) {
      refreshCartDrawerAndBadge();
    }

    lockScroll();
    document.addEventListener('keydown', trapCart);

    var closeButton = cartPanel ? cartPanel.querySelector('.site-header__cart-close') : null;
    if (closeButton) {
      setTimeout(function () {
        closeButton.focus();
      }, 40);
    }
  }

  function closeCart(returnFocus) {
    if (returnFocus === undefined) {
      returnFocus = true;
    }

    if (!cartToggle || !cartOverlay) {
      return;
    }

    cartToggle.setAttribute('aria-expanded', 'false');
    cartToggle.classList.remove('is-active');

    document.removeEventListener('keydown', trapCart);

    hideOverlay(cartOverlay, cartPanel, function () {
      unlockScroll();

      if (returnFocus) {
        cartToggle.focus();
      }
    });
  }

  function toggleCart(event) {
    if (event) {
      event.preventDefault();
    }

    if (!cartToggle || !cartOverlay) {
      return;
    }

    if (cartToggle.getAttribute('aria-expanded') === 'true') {
      closeCart();
    } else {
      openCart();
    }
  }

  function updateCartDrawerContent(miniCartHtml, cartCount) {
    var cartContent = document.querySelector('#cart-drawer .widget_shopping_cart_content');
    if (cartContent && miniCartHtml) {
      cartContent.innerHTML = miniCartHtml;
    }

    // Ažuriraj badge via textContent (backup za naš custom AJAX refresh).
    // Primarni update ide kroz WooCommerce fragment sistem (data-cart-badge outer-HTML replace).
    var countStr = String(cartCount || 0);
    document.querySelectorAll('span[data-cart-badge]').forEach(function (badge) {
      badge.textContent = countStr;
    });
  }

  var isRefreshingCartDrawer = false;
  var pendingCartDrawerRefresh = false;
  var cartDrawerRefreshVersion = 0;
  var cartDrawerSyncTimer = null;

  function invalidateCartDrawerRefreshes() {
    cartDrawerRefreshVersion += 1;
  }

  function syncCartDrawerSoon(delay) {
    invalidateCartDrawerRefreshes();

    if (cartDrawerSyncTimer) {
      clearTimeout(cartDrawerSyncTimer);
    }

    cartDrawerSyncTimer = setTimeout(function () {
      cartDrawerSyncTimer = null;
      refreshCartDrawerAndBadge();
    }, typeof delay === 'number' ? delay : 0);
  }

  function removeWooNotices() {
    // WooCommerce JS client-side ubacuje <a class="added_to_cart wc-forward"> odmah posle dugmeta.
    document.querySelectorAll('a.added_to_cart.wc-forward, a.added_to_cart.wc_forward').forEach(function (el) {
      el.remove();
    });

    // Uklanjamo samo success poruke da ne bismo sakrili greške korisniku.
    document.querySelectorAll([
      '.woocommerce-notices-wrapper .woocommerce-message',
      '.woocommerce-notices-wrapper .is-success',
      // WC Blocks notice banner (React-rendered, WC 8+)
      '.wc-block-components-notice-banner.is-success',
      '.wc-block-store-notice.is-success'
    ].join(', ')).forEach(function (el) {
      el.remove();
    });

    document.querySelectorAll('.woocommerce-notices-wrapper').forEach(function (wrapper) {
      if (!wrapper.querySelector('.woocommerce-message, .woocommerce-info, .woocommerce-success, .woocommerce-error, [role="alert"]')) {
        wrapper.innerHTML = '';
      }
    });
  }

  function refreshCartDrawerAndBadge() {
    if (!window.tersaCartDrawer) {
      if (!cartDrawerHydrated) {
        var missingConfigContent = document.querySelector('#cart-drawer .widget_shopping_cart_content');
        if (missingConfigContent) {
          missingConfigContent.textContent = getHeaderText('cartLoadError', 'Cart could not be loaded.');
        }
      }
      return;
    }

    if (isRefreshingCartDrawer) {
      pendingCartDrawerRefresh = true;
      return;
    }

    isRefreshingCartDrawer = true;
    pendingCartDrawerRefresh = false;
    removeWooNotices();
    var refreshVersion = cartDrawerRefreshVersion;

    var body = new URLSearchParams();
    var fragmentsUrl = getCartAjaxUrl('fragments');
    // wc-ajax route već nosi action u URL-u; admin-ajax traži `action` u body-ju.
    if (fragmentsUrl.indexOf('wc-ajax=') === -1) {
      body.append('action', 'tersa_get_cart_drawer_fragments');
    }
    body.append('nonce', window.tersaCartDrawer.nonce);
    appendCartLanguage(body);

    fetch(fragmentsUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: body.toString()
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Cart drawer request failed: ' + response.status);
        }
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.success || !data.data || typeof data.data.mini_cart_html !== 'string') {
          var invalidContent = document.querySelector('#cart-drawer .widget_shopping_cart_content');
          if (invalidContent && !cartDrawerHydrated) {
            invalidContent.textContent = getHeaderText('cartLoadError', 'Cart could not be loaded.');
          }
          return;
        }

        if (refreshVersion !== cartDrawerRefreshVersion) {
          return;
        }

        updateCartDrawerContent(data.data.mini_cart_html, data.data.cart_count);
        cartDrawerHydrated = true;

        // Ukloni eventualni notice koji se pojavi kasnije u istom tick-u.
        setTimeout(removeWooNotices, 50);
      })
      .catch(function (error) {
        console.error(error);
        var cartContent = document.querySelector('#cart-drawer .widget_shopping_cart_content');
        if (cartContent && !cartDrawerHydrated) {
          cartContent.textContent = getHeaderText('cartLoadError', 'Cart could not be loaded.');
        }
      })
      .finally(function () {
        isRefreshingCartDrawer = false;
        if (pendingCartDrawerRefresh) {
          refreshCartDrawerAndBadge();
        }
      });
  }

  function getBlocksCartSignature(cartData) {
    if (!cartData || !Array.isArray(cartData.items)) {
      return '';
    }

    return cartData.items.map(function (item) {
      return [
        item.key || item.id || '',
        typeof item.quantity === 'number' ? item.quantity : ''
      ].join(':');
    }).join('|');
  }

  function initCartPageDrawerSync() {
    var hasCartPage = document.body.classList.contains('woocommerce-cart') ||
      document.querySelector('.woocommerce-cart-form, .wp-block-woocommerce-cart, .wc-block-cart');

    if (!hasCartPage || !window.tersaCartDrawer) {
      return;
    }

    document.addEventListener('click', function (event) {
      if (
        event.target.closest('.woocommerce-cart-form .product-remove > a') ||
        event.target.closest('.wc-block-cart-item__remove-link')
      ) {
        syncCartDrawerSoon(350);
      }
    });

    document.addEventListener('change', function (event) {
      if (
        event.target.closest('.woocommerce-cart-form input.qty') ||
        event.target.closest('.wc-block-components-quantity-selector input')
      ) {
        syncCartDrawerSoon(650);
      }
    });

    function initBlocksCartStoreSync(attempt) {
      if (!window.wp || !window.wp.data || typeof window.wp.data.subscribe !== 'function') {
        if (attempt < 20) {
          setTimeout(function () {
            initBlocksCartStoreSync(attempt + 1);
          }, 250);
        }
        return;
      }

      var selectCartStore = function () {
        try {
          if (window.wc && window.wc.wcBlocksData && window.wc.wcBlocksData.cartStore) {
            return window.wp.data.select(window.wc.wcBlocksData.cartStore);
          }

          return window.wp.data.select('wc/store/cart');
        } catch (error) {
          return null;
        }
      };

      var cartStore = selectCartStore();
      if (!cartStore || typeof cartStore.getCartData !== 'function') {
        if (attempt < 20) {
          setTimeout(function () {
            initBlocksCartStoreSync(attempt + 1);
          }, 250);
        }
        return;
      }

      var lastSettledSignature = getBlocksCartSignature(cartStore.getCartData());
      var wasPending = typeof cartStore.hasPendingItemsOperations === 'function'
        ? cartStore.hasPendingItemsOperations()
        : false;

      window.wp.data.subscribe(function () {
        var store = selectCartStore();
        if (!store || typeof store.getCartData !== 'function') {
          return;
        }

        var isPending = typeof store.hasPendingItemsOperations === 'function'
          ? store.hasPendingItemsOperations()
          : false;
        var signature = getBlocksCartSignature(store.getCartData());

        if (isPending) {
          wasPending = true;
          return;
        }

        if (signature !== lastSettledSignature) {
          syncCartDrawerSoon(wasPending ? 0 : 150);
          lastSettledSignature = signature;
        }

        wasPending = false;
      });
    }

    initBlocksCartStoreSync(0);
  }

  function bindMiniCartQtyActions() {
    document.addEventListener('click', function (event) {
      var removeLink = event.target.closest('#cart-drawer .remove_from_cart_button');
      if (removeLink) {
        invalidateCartDrawerRefreshes();
        return;
      }

      var button = event.target.closest('.tersa-mini-cart__qty-btn');
      if (!button || !window.tersaCartDrawer) {
        return;
      }

      var qtyWrap = button.closest('.tersa-mini-cart__qty');
      if (!qtyWrap) {
        return;
      }

      var cartItemKey = qtyWrap.getAttribute('data-cart-item-key');
      var valueEl = qtyWrap.querySelector('.tersa-mini-cart__qty-value');
      var currentQty = valueEl ? parseInt(valueEl.textContent, 10) || 1 : 1;
      var action = button.getAttribute('data-qty-action');
      var nextQty = action === 'increase' ? currentQty + 1 : currentQty - 1;

      if (!cartItemKey) {
        return;
      }

      if (nextQty < 1) {
        nextQty = 1;
      }

      qtyWrap.classList.add('is-loading');
      invalidateCartDrawerRefreshes();

      var body = new URLSearchParams();
      var qtyUrl = getCartAjaxUrl('qty');
      if (qtyUrl.indexOf('wc-ajax=') === -1) {
        body.append('action', 'tersa_update_mini_cart_qty');
      }
      body.append('nonce', window.tersaCartDrawer.nonce);
      appendCartLanguage(body);
      body.append('cart_item_key', cartItemKey);
      body.append('quantity', String(nextQty));

      fetch(qtyUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: body.toString()
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          if (!data || !data.success) {
            throw new Error('Cart update failed');
          }

          updateCartDrawerContent(data.data.mini_cart_html, data.data.cart_count);
        })
        .catch(function (error) {
          console.error(error);
        })
        .finally(function () {
          qtyWrap.classList.remove('is-loading');
        });
    });

    // Promena količine preko input/select u cart drawer-u (ako imate selector varijantu):
    // Samo osvežimo fragmente + badge kad WooCommerce završi sa ažuriranjem.
    document.addEventListener('change', function (event) {
      if (!window.tersaCartDrawer) {
        return;
      }

      var target = event.target;
      if (!target) {
        return;
      }

      var qtyWrap = target.closest('#cart-drawer .tersa-mini-cart__qty');
      if (!qtyWrap) {
        return;
      }

      if (!qtyWrap.getAttribute('data-cart-item-key')) {
        return;
      }

      // Osvežavanje posle promene da bi se badge count uskladio sa pravim stanjem košarice.
      setTimeout(refreshCartDrawerAndBadge, 250);
    });
  }

  // ─── PHASE 1: critical bindings (run immediately on DOMContentLoaded) ────────

  if (menuToggle && mobileNavigation) {
    menuToggle.addEventListener('click', function () {
      if (menuToggle.getAttribute('aria-expanded') === 'true') {
        closeMobileMenu();
      } else {
        openMobileMenu();
      }
    });
  }

  if (mobileBackdrop) {
    mobileBackdrop.addEventListener('click', function () {
      closeMobileMenu(false);
    });
  }

  mobileCloseButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      closeMobileMenu();
    });
  });

  if (searchToggle && searchOverlay) {
    searchToggle.addEventListener('click', toggleSearch);
  }

  searchCloseButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      closeSearch();
    });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
      return;
    }

    if (mobileNavStack.length > 0) {
      closeMobileSubmenu();
      return;
    }

    var openDesktopSubmenu = document.querySelector('.site-header__nav--desktop .menu-item-has-children.is-submenu-open');
    if (openDesktopSubmenu) {
      closeAllDesktopSubmenus();
      return;
    }

    if (cartOverlay && !cartOverlay.hidden) {
      closeCart();
      return;
    }

    if (searchOverlay && !searchOverlay.hidden) {
      closeSearch();
      return;
    }

    if (mobileNavigation && mobileNavigation.classList.contains('is-open')) {
      closeMobileMenu();
    }
  });

  initDesktopSubmenus();
  initMobileSubmenus();

  if (cartToggle && cartOverlay) {
    cartToggle.addEventListener('click', toggleCart);
  }

  cartCloseButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      closeCart();
    });
  });

  // WooCommerce fireuje `added_to_cart` event posle AJAX add-to-cart klika.
  if (window.jQuery) {
    window.jQuery(document.body).on('added_to_cart', function () {
      // Odmah ukloni šta god je već tu, ali WC JS dodaje link malo kasnije pa ponovi čišćenje.
      invalidateCartDrawerRefreshes();
      removeWooNotices();
      setTimeout(removeWooNotices, 0);
      setTimeout(removeWooNotices, 200);
      refreshCartDrawerAndBadge();
    });

    // WC event koji se okida kada WC JS završi ažuriranje dugmeta (posle inserted 'added_to_cart' anchor).
    window.jQuery(document.body).on('wc_cart_button_updated', function () {
      removeWooNotices();
    });

    // WooCommerce already refreshes mini-cart fragments after remove/add.
    // Running our own refresh at the same time can overwrite the fresh WC fragment
    // with an older response, which makes a removed item appear again in the drawer.
    window.jQuery(document.body).on('removed_from_cart', function () {
      invalidateCartDrawerRefreshes();
      removeWooNotices();
    });

    window.jQuery(document.body).on('wc_fragments_loaded wc_fragments_refreshed', function () {
      removeWooNotices();
    });

    // Osveži drawer + badge posle ažuriranja cart stranice.
    // Classic cart okida ove evente posle brisanja artikla i promene količine.
    window.jQuery(document.body).on('updated_cart_totals updated_wc_div item_removed_from_classic_cart wc_cart_emptied', function () {
      removeWooNotices();
      syncCartDrawerSoon(0);
    });
  } else {
    document.body.addEventListener('added_to_cart', function () {
      removeWooNotices();
      setTimeout(removeWooNotices, 0);
      setTimeout(removeWooNotices, 200);
      refreshCartDrawerAndBadge();
    });
  }

  bindMiniCartQtyActions();
  initCartPageDrawerSync();

  // Hover/focus prefetch na cart ikonicu:
  // AJAX fetch kreće čim korisnik pomjeri miš prema dugmetu (tipično 100-200ms
  // prije klika), pa je sadržaj spreman do trenutka otvaranja drawera.
  if (cartToggle && window.tersaCartDrawer) {
    function prefetchCart() {
      if (!cartDrawerHydrated) {
        refreshCartDrawerAndBadge();
      }
    }
    cartToggle.addEventListener('mouseenter', prefetchCart, { once: true });
    cartToggle.addEventListener('focusin',    prefetchCart, { once: true });
    cartToggle.addEventListener('touchstart', prefetchCart, { once: true, passive: true });
  }
});
