document.addEventListener('DOMContentLoaded', () => {
    const slider = document.querySelector('.js-home-hero-slider');
  
    if (!slider) {
      return;
    }
  
    const slides = Array.from(slider.querySelectorAll('.home-hero__slide'));
    const dots = Array.from(slider.querySelectorAll('.home-hero__dot'));
  
    if (!slides.length || !dots.length) {
      const firstSlide = slides[0];
      if (firstSlide) {
        firstSlide.classList.add('is-active', 'is-animating');
      }
      return;
    }
  
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
  
    dots.forEach((dot) => {
      dot.addEventListener('click', () => {
        const nextIndex = Number(dot.getAttribute('data-slide-to'));
        setActiveSlide(nextIndex);
      });
    });



    const countdowns = document.querySelectorAll('.js-home-promo-countdown');

    if (countdowns.length) {
      const updateCountdown = (container) => {
        const endValue = container.getAttribute('data-end');

        if (!endValue) {
          return;
        }

        const endDate = new Date(endValue).getTime();

        if (Number.isNaN(endDate)) {
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
      };

      countdowns.forEach((countdown) => {
        updateCountdown(countdown);
        window.setInterval(() => updateCountdown(countdown), 1000);
      });
    }
  });