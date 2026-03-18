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
  });