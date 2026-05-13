document.addEventListener('DOMContentLoaded', function () {
  var galleryRoot = document.querySelector('.product-single');
  if (!galleryRoot) {
    return;
  }

  var mainImageLink = galleryRoot.querySelector('.product-single__main-image-link');
  var mainImage = galleryRoot.querySelector('.product-single__main-image');
  var mainMedia = galleryRoot.querySelector('.product-single__main-media');

  /**
   * Parent (fallback) galerija — emituje se u <script type="application/json"> u template-u.
   * Ova lista se koristi kad varijacija nema vlastitu galeriju i pri reset_data.
   */
  var parentGallery = [];
  var fallbackScript = galleryRoot.querySelector('script.tersa-product-fallback-gallery');
  if (fallbackScript) {
    try {
      var parsed = JSON.parse(fallbackScript.textContent || '[]');
      if (Array.isArray(parsed)) {
        parentGallery = parsed;
      }
    } catch (err) {
      parentGallery = [];
    }
  }

  // ─────────────────────────────────────────────────────────────────
  // Main image setters
  // ─────────────────────────────────────────────────────────────────

  function tersaSetMainImageFromItem(item) {
    if (!mainImage || !item || !item.src) {
      return;
    }
    mainImage.setAttribute('src', item.src);

    if (item.srcset) {
      mainImage.setAttribute('srcset', item.srcset);
    } else {
      mainImage.removeAttribute('srcset');
    }
    if (item.sizes) {
      mainImage.setAttribute('sizes', item.sizes);
    } else {
      mainImage.removeAttribute('sizes');
    }
    if (typeof item.alt === 'string') {
      mainImage.setAttribute('alt', item.alt);
    }
    if (mainImageLink) {
      mainImageLink.setAttribute('href', item.full || item.src);
    }
  }

  function tersaSetMainImageFromWCImage(img) {
    if (!mainImage || !img || !img.src) {
      return;
    }
    tersaSetMainImageFromItem({
      src: img.src,
      srcset: img.srcset || '',
      sizes: img.sizes || '',
      full: img.full_src || img.url || img.src,
      alt: img.alt || ''
    });
  }

  function tersaHighlightThumb(imageId) {
    var thumbs = galleryRoot.querySelectorAll('.product-single__thumb');
    var matched = false;
    thumbs.forEach(function (thumb) {
      var on = imageId && String(thumb.getAttribute('data-image-id')) === String(imageId);
      thumb.classList.toggle('is-active', on);
      thumb.setAttribute('aria-pressed', on ? 'true' : 'false');
      if (on) {
        matched = true;
      }
    });
    // Ako nijedan ne odgovara, aktiviramo prvi.
    if (!matched && thumbs.length > 0) {
      thumbs[0].classList.add('is-active');
      thumbs[0].setAttribute('aria-pressed', 'true');
    }
  }

  // ─────────────────────────────────────────────────────────────────
  // Thumb strip rendering
  // ─────────────────────────────────────────────────────────────────

  function tersaEscapeAttr(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function tersaBuildThumbsWrap(gallery) {
    if (!Array.isArray(gallery) || gallery.length < 2) {
      return '';
    }

    var sliderMode = gallery.length >= 4;
    var wrapClass = 'product-single__thumbs-wrap' + (sliderMode ? ' product-single__thumbs-wrap--slider' : '');
    var html = '<div class="' + wrapClass + '"' + (sliderMode ? ' data-tersa-thumbs-slider="1"' : '') + '>';

    if (sliderMode) {
      html += '<button type="button" class="product-single__thumbs-nav product-single__thumbs-nav--prev" data-tersa-thumbs-prev aria-label="Previous" disabled><span aria-hidden="true">&#8249;</span></button>';
    }

    html += '<div class="product-single__thumbs" role="list">';
    gallery.forEach(function (item, index) {
      var isActive = index === 0;
      html += '<button class="product-single__thumb' + (isActive ? ' is-active' : '') + '" type="button"'
        + ' data-full-image="' + tersaEscapeAttr(item.full || item.src) + '"'
        + ' data-large-image="' + tersaEscapeAttr(item.src) + '"'
        + ' data-large-srcset="' + tersaEscapeAttr(item.srcset || '') + '"'
        + ' data-large-sizes="' + tersaEscapeAttr(item.sizes || '') + '"'
        + ' data-image-id="' + tersaEscapeAttr(item.id) + '"'
        + ' aria-pressed="' + (isActive ? 'true' : 'false') + '">'
        + '<img class="product-single__thumb-image" src="' + tersaEscapeAttr(item.thumb || item.src) + '" alt="" loading="lazy" decoding="async" />'
        + '</button>';
    });
    html += '</div>';

    if (sliderMode) {
      html += '<button type="button" class="product-single__thumbs-nav product-single__thumbs-nav--next" data-tersa-thumbs-next aria-label="Next"><span aria-hidden="true">&#8250;</span></button>';
    }

    html += '</div>';
    return html;
  }

  function tersaRenderThumbsStrip(gallery) {
    var existingWrap = galleryRoot.querySelector('.product-single__thumbs-wrap');
    var newHtml = tersaBuildThumbsWrap(gallery);

    if (!newHtml) {
      if (existingWrap) {
        existingWrap.remove();
      }
      return;
    }

    if (existingWrap) {
      existingWrap.outerHTML = newHtml;
    } else {
      // Append posle main media bloka.
      var gallery_container = galleryRoot.querySelector('.product-single__gallery');
      if (gallery_container) {
        gallery_container.insertAdjacentHTML('beforeend', newHtml);
      }
    }

    tersaUpdateSliderState();
  }

  // ─────────────────────────────────────────────────────────────────
  // Slider state (prev/next disabled) — event-driven, bez cache-a
  // ─────────────────────────────────────────────────────────────────

  function tersaGetSliderStrip() {
    var sliderWrap = galleryRoot.querySelector('.product-single__thumbs-wrap--slider');
    return sliderWrap ? sliderWrap.querySelector('.product-single__thumbs') : null;
  }

  function tersaThumbStep(strip) {
    if (!strip) {
      return 0;
    }
    var first = strip.querySelector('.product-single__thumb');
    if (!first) {
      return 0;
    }
    var rect = first.getBoundingClientRect();
    var styles = window.getComputedStyle(strip);
    var gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
    return Math.round(rect.width + gap);
  }

  function tersaUpdateSliderState() {
    var strip = tersaGetSliderStrip();
    var prev = galleryRoot.querySelector('[data-tersa-thumbs-prev]');
    var next = galleryRoot.querySelector('[data-tersa-thumbs-next]');

    if (!strip) {
      return;
    }
    var maxScroll = strip.scrollWidth - strip.clientWidth;
    if (prev) {
      prev.disabled = strip.scrollLeft <= 1;
    }
    if (next) {
      next.disabled = strip.scrollLeft >= maxScroll - 1;
    }
  }

  // ─────────────────────────────────────────────────────────────────
  // Orchestrator: applyGallery
  // ─────────────────────────────────────────────────────────────────

  function tersaApplyGallery(gallery, opts) {
    opts = opts || {};
    if (!Array.isArray(gallery) || gallery.length === 0) {
      return;
    }

    tersaRenderThumbsStrip(gallery);

    var explicitMainId = opts.mainImageId || null;
    var explicitImg = opts.wcImage || null;

    if (explicitImg && explicitImg.src) {
      tersaSetMainImageFromWCImage(explicitImg);
      tersaHighlightThumb(explicitMainId);
    } else {
      tersaSetMainImageFromItem(gallery[0]);
      tersaHighlightThumb(gallery[0].id);
    }
  }

  // ─────────────────────────────────────────────────────────────────
  // Event delegation: thumb click, slider prev/next
  // ─────────────────────────────────────────────────────────────────

  galleryRoot.addEventListener('click', function (event) {
    var thumb = event.target.closest('.product-single__thumb');
    if (thumb && galleryRoot.contains(thumb)) {
      tersaSetMainImageFromItem({
        src: thumb.getAttribute('data-large-image') || '',
        srcset: thumb.getAttribute('data-large-srcset') || '',
        sizes: thumb.getAttribute('data-large-sizes') || '',
        full: thumb.getAttribute('data-full-image') || '',
        alt: ''
      });
      tersaHighlightThumb(thumb.getAttribute('data-image-id'));
      return;
    }

    var prev = event.target.closest('[data-tersa-thumbs-prev]');
    if (prev && galleryRoot.contains(prev)) {
      var stripPrev = tersaGetSliderStrip();
      if (stripPrev) {
        stripPrev.scrollBy({ left: -tersaThumbStep(stripPrev), behavior: 'smooth' });
      }
      return;
    }

    var next = event.target.closest('[data-tersa-thumbs-next]');
    if (next && galleryRoot.contains(next)) {
      var stripNext = tersaGetSliderStrip();
      if (stripNext) {
        stripNext.scrollBy({ left: tersaThumbStep(stripNext), behavior: 'smooth' });
      }
    }
  });

  // Scroll/resize update sliderState — delegirano na document jer se strip može menjati.
  document.addEventListener('scroll', function (event) {
    var target = event.target;
    if (target && target.classList && target.classList.contains('product-single__thumbs')) {
      tersaUpdateSliderState();
    }
  }, true);
  window.addEventListener('resize', tersaUpdateSliderState);

  // Inicijalni state.
  tersaUpdateSliderState();

  // ─────────────────────────────────────────────────────────────────
  // Lightbox
  // ─────────────────────────────────────────────────────────────────

  var lightbox = galleryRoot.querySelector('.product-single__lightbox');
  var lightboxImage = lightbox ? lightbox.querySelector('.product-single__lightbox-image') : null;
  var lastFocusedBeforeLightbox = null;

  function tersaOpenLightbox(src, alt) {
    if (!lightbox || !lightboxImage || !src) {
      return;
    }
    lightboxImage.setAttribute('src', src);
    lightboxImage.setAttribute('alt', alt || '');
    lightbox.removeAttribute('hidden');
    lightbox.classList.add('is-open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.classList.add('tersa-lightbox-open');

    lastFocusedBeforeLightbox = document.activeElement;
    var closeBtn = lightbox.querySelector('.product-single__lightbox-close');
    if (closeBtn) {
      closeBtn.focus();
    }
  }

  function tersaCloseLightbox() {
    if (!lightbox) {
      return;
    }
    lightbox.classList.remove('is-open');
    lightbox.setAttribute('hidden', '');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('tersa-lightbox-open');
    if (lightboxImage) {
      lightboxImage.setAttribute('src', '');
    }
    if (lastFocusedBeforeLightbox && typeof lastFocusedBeforeLightbox.focus === 'function') {
      lastFocusedBeforeLightbox.focus();
    }
  }

  if (lightbox && mainImageLink) {
    mainImageLink.addEventListener('click', function (event) {
      var href = mainImageLink.getAttribute('href');
      if (!href) {
        return;
      }
      event.preventDefault();
      var altText = mainImage ? mainImage.getAttribute('alt') || '' : '';
      tersaOpenLightbox(href, altText);
    });

    lightbox.querySelectorAll('[data-tersa-lightbox-close]').forEach(function (el) {
      el.addEventListener('click', function (event) {
        event.preventDefault();
        tersaCloseLightbox();
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && lightbox.classList.contains('is-open')) {
        tersaCloseLightbox();
      }
    });
  }

  // ─────────────────────────────────────────────────────────────────
  // Accordions
  // ─────────────────────────────────────────────────────────────────

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

  // ─────────────────────────────────────────────────────────────────
  // WooCommerce varijabilni proizvod: found_variation / reset_data
  // ─────────────────────────────────────────────────────────────────

  if (typeof jQuery !== 'undefined') {
    var $form = jQuery('form.variations_form.cart');

    if ($form.length) {
      $form.on('found_variation.tersa', function (event, variation) {
        if (!variation) {
          tersaApplyGallery(parentGallery);
          return;
        }

        var variationGallery = Array.isArray(variation.tersa_gallery) ? variation.tersa_gallery : [];

        if (variationGallery.length > 0) {
          tersaApplyGallery(variationGallery, {
            wcImage: variation.image && variation.image.src ? variation.image : null,
            mainImageId: variation.image_id || (variationGallery[0] && variationGallery[0].id) || null
          });
          return;
        }

        // Bez vlastite galerije — fallback na parent, main image preuzimamo iz WC payload-a.
        tersaApplyGallery(parentGallery, {
          wcImage: variation.image && variation.image.src ? variation.image : null,
          mainImageId: variation.image_id || null
        });
      });

      $form.on('reset_data.tersa reset_image.tersa', function () {
        if (parentGallery.length > 0) {
          tersaApplyGallery(parentGallery);
        }
      });
    }
  }
});
