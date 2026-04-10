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

        var section = toggle.closest('.product-single__accordion');
        var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
  
        if (isExpanded) {
          if (section) {
            section.classList.remove('is-open');
          }
          panel.setAttribute('aria-hidden', 'true');
          toggle.setAttribute('aria-expanded', 'false');
        } else {
          if (section) {
            section.classList.add('is-open');
          }
          panel.setAttribute('aria-hidden', 'false');
          toggle.setAttribute('aria-expanded', 'true');
        }
      });
    });

    /**
     * Varijabilni proizvod: glavna slika u custom galeriji.
     * WooCommerce wc_variations_image_update cilja .woocommerce-product-gallery — ova tema koristi .product-single__main-image.
     */
    if (typeof jQuery !== 'undefined') {
      var $form = jQuery('form.variations_form.cart');
      var mainMedia = galleryRoot.querySelector('.product-single__main-media[data-tersa-variation-gallery]');
      var vMainImage = galleryRoot.querySelector('.product-single__main-image');
      var vMainLink = galleryRoot.querySelector('.product-single__main-image-link');

      if ($form.length && mainMedia && vMainImage) {
        var vThumbs = galleryRoot.querySelectorAll('.product-single__thumb');

        function tersaGetDefaultGalleryAttrs() {
          return {
            large: mainMedia.getAttribute('data-tersa-default-large') || '',
            full: mainMedia.getAttribute('data-tersa-default-full') || '',
            srcset: mainMedia.getAttribute('data-tersa-default-srcset') || '',
            sizes: mainMedia.getAttribute('data-tersa-default-sizes') || '',
            alt: mainMedia.getAttribute('data-tersa-default-alt') || ''
          };
        }

        function tersaApplyVariationMainImage(img, imageId) {
          if (!img || !img.src || String(img.src).length < 2) {
            tersaRestoreDefaultMainImage();
            return;
          }

          vMainImage.setAttribute('src', img.src);
          if (img.srcset) {
            vMainImage.setAttribute('srcset', img.srcset);
          } else {
            vMainImage.removeAttribute('srcset');
          }
          if (img.sizes) {
            vMainImage.setAttribute('sizes', img.sizes);
          } else {
            vMainImage.removeAttribute('sizes');
          }
          if (img.alt) {
            vMainImage.setAttribute('alt', img.alt);
          }

          if (vMainLink) {
            var fullHref = img.full_src || img.url || img.src;
            if (fullHref) {
              vMainLink.setAttribute('href', fullHref);
            }
          }

          vThumbs.forEach(function (t) {
            t.classList.remove('is-active');
            t.setAttribute('aria-pressed', 'false');
          });

          if (imageId) {
            var match = galleryRoot.querySelector(
              '.product-single__thumb[data-image-id="' + imageId + '"]'
            );
            if (match) {
              match.classList.add('is-active');
              match.setAttribute('aria-pressed', 'true');
            }
          }
        }

        function tersaRestoreDefaultMainImage() {
          var d = tersaGetDefaultGalleryAttrs();
          if (!d.large) {
            return;
          }
          vMainImage.setAttribute('src', d.large);
          if (d.srcset) {
            vMainImage.setAttribute('srcset', d.srcset);
          } else {
            vMainImage.removeAttribute('srcset');
          }
          if (d.sizes) {
            vMainImage.setAttribute('sizes', d.sizes);
          } else {
            vMainImage.removeAttribute('sizes');
          }
          if (d.alt) {
            vMainImage.setAttribute('alt', d.alt);
          }
          if (vMainLink && d.full) {
            vMainLink.setAttribute('href', d.full);
          }

          vThumbs.forEach(function (t, i) {
            var on = i === 0;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-pressed', on ? 'true' : 'false');
          });
        }

        $form.on('found_variation.tersa', function (event, variation) {
          if (!variation || !variation.image) {
            tersaRestoreDefaultMainImage();
            return;
          }
          tersaApplyVariationMainImage(variation.image, variation.image_id);
        });

        $form.on('reset_data.tersa reset_image.tersa', function () {
          tersaRestoreDefaultMainImage();
        });
      }
    }
  });