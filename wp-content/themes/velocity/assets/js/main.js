/**
 * Velocity Theme Main JavaScript
 *
 * @package Velocity
 * @since 1.0.0
 */

(function() {
  'use strict';

  /**
   * Mobile Menu Toggle
   */
  function initMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNavigation = document.querySelector('.main-navigation');

    if (!menuToggle || !mainNavigation) return;

    menuToggle.addEventListener('click', function() {
      const isExpanded = this.getAttribute('aria-expanded') === 'true';

      this.setAttribute('aria-expanded', !isExpanded);
      mainNavigation.classList.toggle('active');

      // Animate hamburger icon
      const spans = this.querySelectorAll('span');
      if (spans.length === 3) {
        if (!isExpanded) {
          spans[0].style.transform = 'rotate(45deg) translate(6px, 6px)';
          spans[1].style.opacity = '0';
          spans[2].style.transform = 'rotate(-45deg) translate(6px, -6px)';
        } else {
          spans[0].style.transform = 'none';
          spans[1].style.opacity = '1';
          spans[2].style.transform = 'none';
        }
      }
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
      if (!menuToggle.contains(event.target) && !mainNavigation.contains(event.target)) {
        mainNavigation.classList.remove('active');
        menuToggle.setAttribute('aria-expanded', 'false');

        const spans = menuToggle.querySelectorAll('span');
        if (spans.length === 3) {
          spans[0].style.transform = 'none';
          spans[1].style.opacity = '1';
          spans[2].style.transform = 'none';
        }
      }
    });

    // Close menu on escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape' && mainNavigation.classList.contains('active')) {
        mainNavigation.classList.remove('active');
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.focus();
      }
    });
  }

  /**
   * Smooth Scroll for Anchor Links
   */
  function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(function(link) {
      link.addEventListener('click', function(event) {
        const href = this.getAttribute('href');

        // Skip if it's just "#"
        if (href === '#' || href === '#0') {
          event.preventDefault();
          return;
        }

        const target = document.querySelector(href);

        if (target) {
          event.preventDefault();

          const headerOffset = 80; // Account for sticky header
          const elementPosition = target.getBoundingClientRect().top;
          const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

          window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
          });

          // Update URL without jumping
          history.pushState(null, null, href);

          // Set focus for accessibility
          target.setAttribute('tabindex', '-1');
          target.focus();
        }
      });
    });
  }

  /**
   * Sticky Header on Scroll
   */
  function initStickyHeader() {
    const header = document.querySelector('.site-header');
    if (!header) return;

    let lastScroll = 0;
    const scrollThreshold = 100;

    window.addEventListener('scroll', function() {
      const currentScroll = window.pageYOffset;

      // Add shadow when scrolled
      if (currentScroll > 10) {
        header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
      } else {
        header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.05)';
      }

      // Hide/show header on scroll (optional - uncomment to enable)
      /*
      if (currentScroll > scrollThreshold) {
        if (currentScroll > lastScroll) {
          // Scrolling down
          header.style.transform = 'translateY(-100%)';
        } else {
          // Scrolling up
          header.style.transform = 'translateY(0)';
        }
      }
      */

      lastScroll = currentScroll;
    });
  }

  /**
   * Lazy Load Images with Intersection Observer
   */
  function initLazyLoad() {
    // Check if browser supports IntersectionObserver
    if (!('IntersectionObserver' in window)) {
      // Fallback: load all images immediately
      document.querySelectorAll('img[loading="lazy"]').forEach(function(img) {
        if (img.dataset.src) {
          img.src = img.dataset.src;
        }
      });
      return;
    }

    const imageObserver = new IntersectionObserver(function(entries, observer) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          const img = entry.target;

          // If image has data-src, use it
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
          }

          img.classList.add('loaded');
          imageObserver.unobserve(img);
        }
      });
    }, {
      rootMargin: '50px 0px', // Start loading 50px before image enters viewport
      threshold: 0.01
    });

    // Observe all images with data-src or loading="lazy"
    document.querySelectorAll('img[data-src], img[loading="lazy"]').forEach(function(img) {
      imageObserver.observe(img);
    });
  }

  /**
   * Fade-in Animation on Scroll
   */
  function initScrollAnimations() {
    // Check if browser supports IntersectionObserver
    if (!('IntersectionObserver' in window)) {
      return;
    }

    const animationObserver = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('fade-in-up');
          animationObserver.unobserve(entry.target);
        }
      });
    }, {
      rootMargin: '0px 0px -100px 0px',
      threshold: 0.1
    });

    // Observe elements with animation class
    document.querySelectorAll('.card, .grid > div').forEach(function(element) {
      animationObserver.observe(element);
    });
  }

  /**
   * Form Enhancement
   */
  function initFormEnhancements() {
    const forms = document.querySelectorAll('form');

    forms.forEach(function(form) {
      // Add loading state on submit
      form.addEventListener('submit', function() {
        const submitButton = this.querySelector('button[type="submit"], input[type="submit"]');

        if (submitButton) {
          submitButton.disabled = true;
          submitButton.style.opacity = '0.6';
          submitButton.style.cursor = 'not-allowed';

          const originalText = submitButton.textContent || submitButton.value;
          if (submitButton.tagName === 'BUTTON') {
            submitButton.textContent = 'Sending...';
          }

          // Re-enable after 5 seconds (fallback)
          setTimeout(function() {
            submitButton.disabled = false;
            submitButton.style.opacity = '1';
            submitButton.style.cursor = 'pointer';
            if (submitButton.tagName === 'BUTTON') {
              submitButton.textContent = originalText;
            }
          }, 5000);
        }
      });

      // Add focus styles to form inputs
      const inputs = form.querySelectorAll('input, textarea, select');
      inputs.forEach(function(input) {
        input.addEventListener('focus', function() {
          this.style.outline = '2px solid var(--color-primary)';
          this.style.outlineOffset = '2px';
        });

        input.addEventListener('blur', function() {
          this.style.outline = 'none';
        });
      });
    });
  }

  /**
   * Back to Top Button
   */
  function initBackToTop() {
    // Create back to top button
    const backToTop = document.createElement('button');
    backToTop.innerHTML = '↑';
    backToTop.setAttribute('aria-label', 'Back to top');
    backToTop.style.cssText = `
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: var(--color-primary);
      color: white;
      border: none;
      font-size: 24px;
      cursor: pointer;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
      z-index: 999;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    `;

    document.body.appendChild(backToTop);

    // Show/hide button on scroll
    window.addEventListener('scroll', function() {
      if (window.pageYOffset > 300) {
        backToTop.style.opacity = '1';
        backToTop.style.visibility = 'visible';
      } else {
        backToTop.style.opacity = '0';
        backToTop.style.visibility = 'hidden';
      }
    });

    // Scroll to top on click
    backToTop.addEventListener('click', function() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });

    // Hover effect
    backToTop.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-5px)';
      this.style.boxShadow = '0 6px 16px rgba(0, 0, 0, 0.2)';
    });

    backToTop.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0)';
      this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
    });
  }

  /**
   * Initialize External Links
   */
  function initExternalLinks() {
    const links = document.querySelectorAll('a[href^="http"]');

    links.forEach(function(link) {
      // Skip if it's an internal link
      if (link.hostname === window.location.hostname) {
        return;
      }

      // Add external link attributes
      link.setAttribute('target', '_blank');
      link.setAttribute('rel', 'noopener noreferrer');

      // Add visual indicator
      if (!link.querySelector('.external-icon')) {
        const icon = document.createElement('span');
        icon.className = 'external-icon';
        icon.innerHTML = ' ↗';
        icon.style.fontSize = '0.8em';
        link.appendChild(icon);
      }
    });
  }

  /**
   * Initialize Accessibility Features
   */
  function initAccessibility() {
    // Skip to main content link functionality
    const skipLink = document.querySelector('.skip-link');
    if (skipLink) {
      skipLink.addEventListener('click', function(event) {
        event.preventDefault();
        const main = document.querySelector('#main');
        if (main) {
          main.setAttribute('tabindex', '-1');
          main.focus();
        }
      });
    }

    // Keyboard navigation for dropdowns/menus
    const menuItems = document.querySelectorAll('.main-navigation a');
    menuItems.forEach(function(item) {
      item.addEventListener('keydown', function(event) {
        // Add keyboard navigation if needed for submenus
      });
    });
  }

  /**
   * Performance: Debounce Function
   */
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = function() {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  /**
   * Initialize All Functions
   */
  function init() {
    initMobileMenu();
    initSmoothScroll();
    initStickyHeader();
    initLazyLoad();
    initScrollAnimations();
    initFormEnhancements();
    initBackToTop();
    initExternalLinks();
    initAccessibility();
  }

  // Run when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
