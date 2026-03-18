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
  });