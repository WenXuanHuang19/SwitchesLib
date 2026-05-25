/* Switches Lib — app.js
   Minimal JS: only for interactive behaviours that CSS alone cannot handle.
*/

(function () {
  'use strict';

  /* ── Mobile nav toggle ──────────────────────────────────── */
  var hamburger = document.getElementById('nav-hamburger');
  var mobileMenu = document.getElementById('nav-mobile-menu');

  if (hamburger && mobileMenu) {
    hamburger.addEventListener('click', function () {
      var isOpen = mobileMenu.classList.toggle('is-open');
      hamburger.setAttribute('aria-expanded', String(isOpen));
      // Animate the three bars
      hamburger.classList.toggle('is-active', isOpen);
    });

    // Close mobile menu when a nav link is tapped
    mobileMenu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        mobileMenu.classList.remove('is-open');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.classList.remove('is-active');
      });
    });
  }

})();
