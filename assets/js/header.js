document.addEventListener('DOMContentLoaded', function () {
  var menuToggle = document.querySelector('.site-header__toggle');
  var mobileNavigation = document.getElementById('mobile-navigation');
  var mobileBackdrop = document.querySelector('.site-header__mobile-backdrop');
  var mobileCloseButtons = document.querySelectorAll('[data-mobile-close]');

  var searchToggle = document.querySelector('[data-search-toggle]');
  var searchOverlay = document.getElementById('header-search-overlay');
  var searchCloseButtons = document.querySelectorAll('[data-search-close]');
  var searchTitle = document.getElementById('header-search-title');
  var searchPanel = searchOverlay ? searchOverlay.querySelector('.site-header__search-panel') : null;

  var cartToggle = document.querySelector('[data-cart-toggle]');
  var cartOverlay = document.getElementById('cart-drawer');
  var cartCloseButtons = document.querySelectorAll('[data-cart-close]');
  var cartPanel = cartOverlay ? cartOverlay.querySelector('.site-header__cart-panel') : null;

  var searchLabel = searchTitle ? (searchTitle.dataset.searchLabel || 'Rezultati pretrage') : 'Rezultati pretrage';

  var searchObservers = [];
  var mobileNavStack = [];
  var mobileNavPopulated = false;

  function debounce(fn, wait) {
    var timer;
    return function () {
      clearTimeout(timer);
      timer = setTimeout(fn, wait);
    };
  }

  function revealPanel(overlay, onOpen) {
    overlay.hidden = false;
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        overlay.classList.add('is-open');
        if (onOpen) {
          onOpen();
        }
      });
    });
  }

  function withTransitionCleanup(transitionEl, onCleanup) {
    var done = false;

    function finish() {
      if (done) {
        return;
      }
      done = true;
      onCleanup();
    }

    if (transitionEl) {
      transitionEl.addEventListener('transitionend', function onEnd(e) {
        if (e.propertyName !== 'transform') {
          return;
        }
        transitionEl.removeEventListener('transitionend', onEnd);
        finish();
      });
    }

    setTimeout(finish, 400);
  }

  function closeOtherPanels(openingPanel) {
    if (openingPanel !== 'search' && searchOverlay && !searchOverlay.hidden) {
      searchOverlay.classList.remove('is-open');
      searchOverlay.hidden = true;
      if (searchToggle) {
        searchToggle.setAttribute('aria-expanded', 'false');
        searchToggle.classList.remove('is-active');
      }
      document.documentElement.classList.remove('is-search-open');
      document.body.classList.remove('is-search-open');
      disconnectSearchObservers();
    }

    if (openingPanel !== 'cart' && cartOverlay && !cartOverlay.hidden) {
      cartOverlay.classList.remove('is-open');
      cartOverlay.hidden = true;
      if (cartToggle) {
        cartToggle.setAttribute('aria-expanded', 'false');
        cartToggle.classList.remove('is-active');
      }
    }

    if (openingPanel !== 'mobile' && mobileNavigation && mobileNavigation.classList.contains('is-open')) {
      mobileNavigation.classList.remove('is-open');
      mobileNavigation.setAttribute('inert', '');
      if (mobileBackdrop) {
        mobileBackdrop.classList.remove('is-open');
      }
      if (menuToggle) {
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.classList.remove('is-active');
      }
      resetMobileNav();
    }

    document.body.style.overflow = '';
  }

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

  function initSubmenus() {
    var submenuItems = document.querySelectorAll('.site-header__nav--desktop .menu-item-has-children');

    submenuItems.forEach(function (item) {
      var parentLink = item.querySelector(':scope > a');
      if (!parentLink) {
        return;
      }

      var nav = parentLink.closest('.site-header__nav');
      var labelTemplate = nav ? (nav.dataset.submenuLabel || 'Open submenu for %s') : 'Open submenu for %s';
      var ariaLabel = labelTemplate.replace('%s', parentLink.textContent.trim());

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

    if (submenuItems.length) {
      document.addEventListener('click', function (event) {
        if (!event.target.closest('.site-header__nav--desktop .menu-item-has-children')) {
          closeAllSubmenus();
        }
      });
    }
  }

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

    var mobileNavLabel = mobileNavigation.dataset.mobileNavLabel || 'Mobile navigation';
    mobileNav.setAttribute('aria-label', mobileNavLabel);
    mobileNav.removeAttribute('data-submenu-label');

    mobileNav.querySelectorAll('.submenu-toggle').forEach(function (btn) {
      btn.setAttribute('aria-expanded', 'false');
    });
    mobileNav.querySelectorAll('.menu-item-has-children.is-submenu-open').forEach(function (li) {
      li.classList.remove('is-submenu-open');
    });

    mobileNavBody.appendChild(mobileNav);
    mobileNavPopulated = true;
  }

  function openMobileMenu() {
    if (!menuToggle || !mobileNavigation) {
      return;
    }

    closeOtherPanels('mobile');
    populateMobileNav();

    mobileNavigation.removeAttribute('inert');

    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        mobileNavigation.classList.add('is-open');
        if (mobileBackdrop) {
          mobileBackdrop.classList.add('is-open');
        }
      });
    });

    menuToggle.setAttribute('aria-expanded', 'true');
    menuToggle.classList.add('is-active');
    document.body.style.overflow = 'hidden';
  }

  function closeMobileMenu() {
    if (!menuToggle || !mobileNavigation) {
      return;
    }

    mobileNavigation.classList.remove('is-open');
    if (mobileBackdrop) {
      mobileBackdrop.classList.remove('is-open');
    }

    menuToggle.setAttribute('aria-expanded', 'false');
    menuToggle.classList.remove('is-active');

    withTransitionCleanup(mobileNavigation, function () {
      mobileNavigation.setAttribute('inert', '');
      resetMobileNav();
      document.body.style.overflow = '';
      menuToggle.focus();
    });
  }

  function openMobileSubmenu(item) {
    var subMenu = item.querySelector(':scope > .sub-menu');
    if (!subMenu) {
      return;
    }

    subMenu.style.zIndex = 401 + mobileNavStack.length;
    item.classList.add('is-submenu-open');
    mobileNavStack.push({ item: item });
  }

  function closeMobileSubmenu() {
    if (mobileNavStack.length === 0) {
      return;
    }

    var top = mobileNavStack.pop();
    top.item.classList.remove('is-submenu-open');
  }

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

  function getAwsResultCount() {
    if (!searchOverlay) {
      return 0;
    }

    var possibleSelectors = [
      '.aws-search-result .aws_result_item',
      '.aws-search-results .aws_result_item',
      '.aws-container .aws_result_item',
      '.aws-container .aws-search-result li',
      '.aws-container .aws-search-results li'
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

  function disconnectSearchObservers() {
    searchObservers.forEach(function (obs) {
      obs.disconnect();
    });
    searchObservers.length = 0;
  }

  function attachSearchObservers() {
    if (!searchOverlay) {
      return;
    }

    var awsInput = searchOverlay.querySelector('.aws-search-field, input[type="search"], input[type="text"]');

    if (awsInput && !awsInput.dataset.tersaBound) {
      awsInput.dataset.tersaBound = 'true';
      awsInput.addEventListener('input', debouncedUpdateSearchTitle);
      awsInput.addEventListener('focus', debouncedUpdateSearchTitle);
    }

    var awsContainers = searchOverlay.querySelectorAll('.aws-container, .aws-search-result, .aws-search-results');

    awsContainers.forEach(function (container) {
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
    revealPanel(searchOverlay);

    searchToggle.setAttribute('aria-expanded', 'true');
    searchToggle.classList.add('is-active');

    document.documentElement.classList.add('is-search-open');
    document.body.classList.add('is-search-open');

    attachSearchObservers();
    updateSearchTitle();

    var searchInput = searchOverlay.querySelector('.aws-search-field, input[type="search"], input[type="text"]');
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

    searchOverlay.classList.remove('is-open');
    searchToggle.setAttribute('aria-expanded', 'false');
    searchToggle.classList.remove('is-active');

    document.documentElement.classList.remove('is-search-open');
    document.body.classList.remove('is-search-open');

    disconnectSearchObservers();

    if (searchTitle) {
      searchTitle.textContent = searchLabel + ' (0)';
    }

    withTransitionCleanup(searchPanel, function () {
      searchOverlay.hidden = true;
      if (returnFocus) {
        searchToggle.focus();
      }
    });
  }

  function toggleSearch(event) {
    event.preventDefault();

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
    revealPanel(cartOverlay);

    cartToggle.setAttribute('aria-expanded', 'true');
    cartToggle.classList.add('is-active');

    document.body.style.overflow = 'hidden';

    var closeBtn = cartPanel ? cartPanel.querySelector('.site-header__cart-close') : null;
    if (closeBtn) {
      setTimeout(function () {
        closeBtn.focus();
      }, 50);
    }
  }

  function closeCart(returnFocus) {
    if (returnFocus === undefined) {
      returnFocus = true;
    }

    if (!cartToggle || !cartOverlay) {
      return;
    }

    cartOverlay.classList.remove('is-open');
    cartToggle.setAttribute('aria-expanded', 'false');
    cartToggle.classList.remove('is-active');

    document.body.style.overflow = '';

    withTransitionCleanup(cartPanel, function () {
      cartOverlay.hidden = true;
      if (returnFocus) {
        cartToggle.focus();
      }
    });
  }

  function toggleCart(event) {
    event.preventDefault();

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

    var cartBadges = document.querySelectorAll('.site-header__icon-link--cart .site-header__badge');
    cartBadges.forEach(function (badge) {
      badge.textContent = String(cartCount || 0);
    });
  }

  function bindMiniCartQtyActions() {
    document.addEventListener('click', function (event) {
      var btn = event.target.closest('.tersa-mini-cart__qty-btn');
      if (!btn || !window.tersaCartDrawer) {
        return;
      }

      var qtyWrap = btn.closest('.tersa-mini-cart__qty');
      if (!qtyWrap) {
        return;
      }

      var cartItemKey = qtyWrap.getAttribute('data-cart-item-key');
      var valueEl = qtyWrap.querySelector('.tersa-mini-cart__qty-value');
      var currentQty = valueEl ? parseInt(valueEl.textContent, 10) || 1 : 1;
      var action = btn.getAttribute('data-qty-action');
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

  mobileCloseButtons.forEach(function (btn) {
    btn.addEventListener('click', closeMobileMenu);
  });

  if (searchToggle && searchOverlay) {
    searchToggle.addEventListener('click', toggleSearch);

    searchCloseButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        closeSearch();
      });
    });
  }

  if (cartToggle && cartOverlay) {
    cartToggle.addEventListener('click', toggleCart);

    cartCloseButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        closeCart();
      });
    });
  }

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
      return;
    }

    if (mobileNavStack.length > 0) {
      closeMobileSubmenu();
      return;
    }

    var openSubmenuItem = document.querySelector('.site-header__nav--desktop .menu-item-has-children.is-submenu-open');
    if (openSubmenuItem) {
      closeAllSubmenus();
      return;
    }

    if (cartOverlay && !cartOverlay.hidden) {
      closeCart();
    } else if (searchOverlay && !searchOverlay.hidden) {
      closeSearch();
    } else if (mobileNavigation && mobileNavigation.classList.contains('is-open')) {
      closeMobileMenu();
    }
  });

  initSubmenus();
  initMobileSubmenus();
  bindMiniCartQtyActions();
});