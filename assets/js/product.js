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

  var variationGalleryIndex = [];
  var variationGalleryScript = galleryRoot.querySelector('script.tersa-product-variation-galleries');
  if (variationGalleryScript) {
    try {
      var parsedVariationGalleries = JSON.parse(variationGalleryScript.textContent || '[]');
      if (Array.isArray(parsedVariationGalleries)) {
        variationGalleryIndex = parsedVariationGalleries;
      }
    } catch (err) {
      variationGalleryIndex = [];
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

  function tersaProductText(key, fallback) {
    var i18n = window.tersaProductI18n || {};
    return typeof i18n[key] === 'string' && i18n[key] !== '' ? i18n[key] : fallback;
  }

  function tersaGalleryItemKey(item) {
    if (!item) {
      return '';
    }

    return String(item.id || item.full || item.src || '');
  }

  function tersaMergeGalleries(galleries) {
    var merged = [];
    var seen = {};

    galleries.forEach(function (gallery) {
      if (!Array.isArray(gallery)) {
        return;
      }

      gallery.forEach(function (item) {
        if (!item || !item.src) {
          return;
        }

        var key = tersaGalleryItemKey(item);
        if (key && seen[key]) {
          return;
        }

        if (key) {
          seen[key] = true;
        }
        merged.push(item);
      });
    });

    return merged;
  }

  function tersaBuildThumbsWrap(gallery) {
    if (!Array.isArray(gallery) || gallery.length < 2) {
      return '';
    }

    var sliderMode = gallery.length >= 4;
    var wrapClass = 'product-single__thumbs-wrap' + (sliderMode ? ' product-single__thumbs-wrap--slider' : '');
    var html = '<div class="' + wrapClass + '"' + (sliderMode ? ' data-tersa-thumbs-slider="1"' : '') + '>';

    if (sliderMode) {
      html += '<button type="button" class="product-single__thumbs-nav product-single__thumbs-nav--prev" data-tersa-thumbs-prev aria-label="' + tersaEscapeAttr(tersaProductText('previousImages', 'Previous images')) + '" disabled><span aria-hidden="true">&#8249;</span></button>';
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
      html += '<button type="button" class="product-single__thumbs-nav product-single__thumbs-nav--next" data-tersa-thumbs-next aria-label="' + tersaEscapeAttr(tersaProductText('nextImages', 'Next images')) + '"><span aria-hidden="true">&#8250;</span></button>';
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

  function tersaBuildGalleryItemFromVariationImage(variation) {
    var img = variation && variation.image ? variation.image : null;
    if (!img || !img.src) {
      return null;
    }

    return {
      id: variation.image_id || img.image_id || img.id || img.src,
      src: img.src,
      full: img.full_src || img.url || img.src,
      thumb: img.gallery_thumbnail_src || img.thumb_src || img.src,
      srcset: img.srcset || '',
      sizes: img.sizes || '',
      alt: img.alt || ''
    };
  }

  function tersaGalleryFromVariationImage(variation) {
    var item = tersaBuildGalleryItemFromVariationImage(variation);
    return item ? [item] : [];
  }

  function tersaFindIndexedGalleryByVariationId(variationId) {
    if (!variationId || !variationGalleryIndex.length) {
      return [];
    }

    var id = String(variationId);
    for (var i = 0; i < variationGalleryIndex.length; i += 1) {
      if (String(variationGalleryIndex[i].variation_id || '') === id) {
        return Array.isArray(variationGalleryIndex[i].gallery) ? variationGalleryIndex[i].gallery : [];
      }
    }

    return [];
  }

  function tersaGetVariationGallery(variation) {
    if (!variation) {
      return [];
    }

    if (Array.isArray(variation.tersa_gallery) && variation.tersa_gallery.length > 0) {
      return variation.tersa_gallery;
    }

    var indexedGallery = tersaFindIndexedGalleryByVariationId(variation.variation_id);
    if (indexedGallery.length > 0) {
      return indexedGallery;
    }

    return tersaGalleryFromVariationImage(variation);
  }

  function tersaGetSelectedAttributes($form) {
    var selected = {};

    $form.find('[name^="attribute_"]').each(function () {
      var field = this;
      var name = field.name || '';
      if (!name) {
        return;
      }

      if ((field.type === 'radio' || field.type === 'checkbox') && !field.checked) {
        return;
      }

      var value = jQuery(field).val();
      if (Array.isArray(value)) {
        value = value[0] || '';
      }
      value = value == null ? '' : String(value);

      if (value !== '') {
        selected[name] = value;
      }
    });

    return selected;
  }

  function tersaVariationMatchesSelectedAttributes(variationEntry, selectedAttributes) {
    var attrs = variationEntry && variationEntry.attributes ? variationEntry.attributes : {};
    var selectedKeys = Object.keys(selectedAttributes);

    if (!selectedKeys.length) {
      return false;
    }

    return selectedKeys.every(function (name) {
      var selectedValue = String(selectedAttributes[name] || '');
      var variationValue = attrs[name] == null ? '' : String(attrs[name]);

      return selectedValue === '' || variationValue === '' || variationValue === selectedValue;
    });
  }

  function tersaGalleryForSelectedAttributes(selectedAttributes) {
    if (!variationGalleryIndex.length || !Object.keys(selectedAttributes).length) {
      return [];
    }

    var galleries = [];
    variationGalleryIndex.forEach(function (variationEntry) {
      if (!tersaVariationMatchesSelectedAttributes(variationEntry, selectedAttributes)) {
        return;
      }

      if (Array.isArray(variationEntry.gallery) && variationEntry.gallery.length > 0) {
        galleries.push(variationEntry.gallery);
      }
    });

    return tersaMergeGalleries(galleries);
  }

  function tersaApplyGalleryForCurrentSelection($form) {
    var selectedAttributes = tersaGetSelectedAttributes($form);
    var gallery = tersaGalleryForSelectedAttributes(selectedAttributes);

    if (gallery.length > 0) {
      tersaApplyGallery(gallery);
      return;
    }

    if (!Object.keys(selectedAttributes).length && parentGallery.length > 0) {
      tersaApplyGallery(parentGallery);
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
      var scheduleCurrentSelectionGallery = function () {
        window.setTimeout(function () {
          tersaApplyGalleryForCurrentSelection($form);
        }, 0);
      };

      $form.on('found_variation.tersa', function (event, variation) {
        if (!variation) {
          tersaApplyGallery(parentGallery);
          return;
        }

        var variationGallery = tersaGetVariationGallery(variation);

        if (variationGallery.length > 0) {
          tersaApplyGallery(variationGallery, {
            wcImage: variation.image && variation.image.src ? variation.image : null,
            mainImageId: variation.image_id || (variationGallery[0] && variationGallery[0].id) || null
          });
          return;
        }

        // Bez vlastite galerije i bez variation image-a — fallback na parent.
        tersaApplyGallery(parentGallery, {
          wcImage: variation.image && variation.image.src ? variation.image : null,
          mainImageId: variation.image_id || null
        });
      });

      $form.on('change.tersaGallery', '[name^="attribute_"]', function () {
        scheduleCurrentSelectionGallery();
      });

      $form.on('woocommerce_variation_select_change.tersaGallery', function () {
        scheduleCurrentSelectionGallery();
      });

      $form.on('reset_data.tersa reset_image.tersa', function () {
        if (parentGallery.length > 0) {
          tersaApplyGallery(parentGallery);
        }
      });
    }
  }
});
