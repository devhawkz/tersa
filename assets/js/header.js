document.addEventListener('DOMContentLoaded', function () {
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
      var fallback = 'Open submenu for %s';
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

      var label = 'Open submenu for ' + link.textContent.trim();
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

    var baseLabel = searchTitle.getAttribute('data-search-label') || 'Rezultati pretrage';
    searchTitle.textContent = baseLabel + ' (' + getAwsResultCount() + ')';
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

    document.querySelectorAll('.site-header__icon-link--cart .site-header__badge').forEach(function (badge) {
      badge.textContent = String(cartCount || 0);
    });
  }

  function bindMiniCartQtyActions() {
    document.addEventListener('click', function (event) {
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

      var body = new URLSearchParams();
      body.append('action', 'tersa_update_mini_cart_qty');
      body.append('nonce', window.tersaCartDrawer.nonce);
      body.append('cart_item_key', cartItemKey);
      body.append('quantity', String(nextQty));

      fetch(window.tersaCartDrawer.ajaxUrl, {
        method: 'POST',
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
  }

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

  if (cartToggle && cartOverlay) {
    cartToggle.addEventListener('click', toggleCart);
  }

  cartCloseButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      closeCart();
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
  bindMiniCartQtyActions();
});