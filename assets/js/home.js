document.addEventListener('DOMContentLoaded', () => {

  // ── Hero slider ──────────────────────────────────────────────────────────

  const slider = document.querySelector('.js-home-hero-slider');

  if (slider) {
    const slides = Array.from(slider.querySelectorAll('.home-hero__slide'));
    const dots = Array.from(slider.querySelectorAll('.home-hero__dot'));

    if (!slides.length || !dots.length) {
      const firstSlide = slides[0];
      if (firstSlide) {
        firstSlide.classList.add('is-active', 'is-animating');
      }
    } else {
      let currentIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));

      if (currentIndex < 0) {
        currentIndex = 0;
      }

      const animateSlide = (slide) => {
        slide.classList.remove('is-animating');
        void slide.offsetWidth;
        slide.classList.add('is-animating');
      };

      const setActiveSlide = (nextIndex) => {
        if (nextIndex === currentIndex || !slides[nextIndex]) {
          return;
        }

        slides.forEach((slide, index) => {
          const isActive = index === nextIndex;
          slide.classList.toggle('is-active', isActive);
          if (isActive) {
            slide.hidden = false;
            animateSlide(slide);
          } else {
            slide.hidden = true;
            slide.classList.remove('is-animating');
          }
        });

        dots.forEach((dot, index) => {
          const isActive = index === nextIndex;
          dot.classList.toggle('is-active', isActive);
          dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        currentIndex = nextIndex;
      };

      slides.forEach((slide, index) => {
        if (index === currentIndex) {
          slide.hidden = false;
          slide.classList.add('is-active');
          animateSlide(slide);
        } else {
          slide.hidden = true;
          slide.classList.remove('is-active', 'is-animating');
        }
      });

      const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

      const goToNextSlide = () => {
        const nextIndex = (currentIndex + 1) % slides.length;
        setActiveSlide(nextIndex);
      };

      let autoplayTimerId = null;

      const parseAutoplayMs = () => {
        const raw = slider.getAttribute('data-autoplay-ms');
        const n = raw ? Number.parseInt(raw, 10) : NaN;
        return Number.isFinite(n) && n >= 3000 ? n : 3000;
      };

      const autoplayMs = parseAutoplayMs();

      const stopAutoplay = () => {
        if (autoplayTimerId !== null) {
          window.clearInterval(autoplayTimerId);
          autoplayTimerId = null;
        }
      };

      const startAutoplay = () => {
        stopAutoplay();
        if (prefersReducedMotion.matches || slides.length < 2) {
          return;
        }
        autoplayTimerId = window.setInterval(goToNextSlide, autoplayMs);
      };

      const restartAutoplay = () => {
        stopAutoplay();
        startAutoplay();
      };

      dots.forEach((dot) => {
        dot.addEventListener('click', () => {
          const nextIndex = Number(dot.getAttribute('data-slide-to'));
          setActiveSlide(nextIndex);
          restartAutoplay();
        });
      });

      slider.addEventListener('mouseenter', stopAutoplay);
      slider.addEventListener('mouseleave', startAutoplay);

      slider.addEventListener('focusin', stopAutoplay);
      slider.addEventListener('focusout', (event) => {
        if (!slider.contains(event.relatedTarget)) {
          startAutoplay();
        }
      });

      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          stopAutoplay();
        } else {
          startAutoplay();
        }
      });

      prefersReducedMotion.addEventListener('change', () => {
        if (prefersReducedMotion.matches) {
          stopAutoplay();
        } else {
          startAutoplay();
        }
      });

      startAutoplay();
    }
  }

  // ── Promo countdown ──────────────────────────────────────────────────────
  // Inicijalizira se neovisno od hero slidera — countdown radi čak i kada
  // je slider isključen u ACF podešavanjima.

  const countdowns = document.querySelectorAll('.js-home-promo-countdown');

  if (countdowns.length) {
    const countdownTimers = [];

    const updateCountdown = (container, stopTimer) => {
      const endValue = container.getAttribute('data-end');

      if (!endValue) {
        stopTimer();
        return;
      }

      const endDate = new Date(endValue).getTime();

      if (Number.isNaN(endDate)) {
        stopTimer();
        return;
      }

      const now = Date.now();
      const diff = Math.max(0, endDate - now);

      const days = Math.floor(diff / (1000 * 60 * 60 * 24));
      const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
      const minutes = Math.floor((diff / (1000 * 60)) % 60);
      const seconds = Math.floor((diff / 1000) % 60);

      const daysEl = container.querySelector('[data-unit="days"]');
      const hoursEl = container.querySelector('[data-unit="hours"]');
      const minutesEl = container.querySelector('[data-unit="minutes"]');
      const secondsEl = container.querySelector('[data-unit="seconds"]');

      if (daysEl) daysEl.textContent = String(days);
      if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
      if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
      if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');

      if (diff === 0) {
        stopTimer();
      }
    };

    countdowns.forEach((countdown) => {
      let timerId = null;

      const stop = () => {
        if (timerId !== null) {
          window.clearInterval(timerId);
          timerId = null;
        }
      };

      const start = () => {
        stop();
        timerId = window.setInterval(() => updateCountdown(countdown, stop), 1000);
      };

      updateCountdown(countdown, stop);
      start();

      countdownTimers.push({ start, stop });
    });

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        countdownTimers.forEach(({ stop }) => stop());
      } else {
        countdownTimers.forEach(({ start }) => start());
      }
    });
  }
});