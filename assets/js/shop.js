document.addEventListener('DOMContentLoaded', function () {
    var filterToggles = document.querySelectorAll('.shop-archive__filter-toggle');
  
    filterToggles.forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        var panelId = toggle.getAttribute('aria-controls');
        if (!panelId) {
          return;
        }
  
        var panel = document.getElementById(panelId);
        if (!panel) {
          return;
        }
  
        var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        var icon = toggle.querySelector('.shop-archive__filter-icon');
  
        toggle.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
  
        if (isExpanded) {
          panel.hidden = true;
          panel.classList.remove('is-open');
          if (icon) {
            icon.textContent = '+';
          }
        } else {
          panel.hidden = false;
          panel.classList.add('is-open');
          if (icon) {
            icon.textContent = '−';
          }
        }
      });
    });

    var descriptionToggles = document.querySelectorAll('.shop-archive__description-toggle');

    descriptionToggles.forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        var wrap = toggle.closest('.shop-archive__description-wrap');
        if (!wrap) {
          return;
        }

        var isCollapsed = wrap.classList.contains('is-collapsed');
        var label = toggle.querySelector('.shop-archive__description-toggle-text');
        var moreLabel = toggle.getAttribute('data-label-more') || 'Pročitaj više';
        var lessLabel = toggle.getAttribute('data-label-less') || 'Sažmi opis';

        wrap.classList.toggle('is-collapsed');
        toggle.setAttribute('aria-expanded', isCollapsed ? 'true' : 'false');

        if (label) {
          label.textContent = isCollapsed ? lessLabel : moreLabel;
        }
      });
    });
  });