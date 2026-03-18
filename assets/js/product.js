document.addEventListener('DOMContentLoaded', function () {
    var galleryRoot = document.querySelector('.product-single');
    if (!galleryRoot) {
      return;
    }
  
    var mainImageLink = galleryRoot.querySelector('.product-single__main-image-link');
    var mainImage = galleryRoot.querySelector('.product-single__main-image');
    var thumbs = galleryRoot.querySelectorAll('.product-single__thumb');
  
    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        if (!mainImage || !mainImageLink) {
          return;
        }
  
        var largeImage = thumb.getAttribute('data-large-image');
        var fullImage = thumb.getAttribute('data-full-image');
  
        if (largeImage) {
          mainImage.setAttribute('src', largeImage);
          mainImage.setAttribute('srcset', '');
        }
  
        if (fullImage) {
          mainImageLink.setAttribute('href', fullImage);
        }
  
        thumbs.forEach(function (item) {
          item.classList.remove('is-active');
          item.setAttribute('aria-pressed', 'false');
        });
  
        thumb.classList.add('is-active');
        thumb.setAttribute('aria-pressed', 'true');
      });
    });
  
    var accordionToggles = document.querySelectorAll('.product-single__accordion-toggle');
  
    accordionToggles.forEach(function (toggle) {
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
        var icon = toggle.querySelector('.product-single__accordion-icon');
  
        toggle.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
  
        if (isExpanded) {
          panel.hidden = true;
          if (icon) {
            icon.textContent = '+';
          }
        } else {
          panel.hidden = false;
          if (icon) {
            icon.textContent = '−';
          }
        }
      });
    });
  });