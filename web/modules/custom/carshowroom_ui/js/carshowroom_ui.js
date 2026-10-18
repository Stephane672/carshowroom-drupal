(function (Drupal, once) {
  'use strict';

  function applyTheme(t) {
    if (t === 'dark') { document.documentElement.setAttribute('data-cs-theme', 'dark'); }
    else if (t === 'light') { document.documentElement.setAttribute('data-cs-theme', 'light'); }
    else { document.documentElement.removeAttribute('data-cs-theme'); }
  }
  Drupal.behaviors.csBackButton = {
    attach: function (context) {
      once('cs-back', '.cs-back-button', context).forEach(function (btn) {
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          if (window.history.length > 1) { window.history.back(); }
          else { window.location.href = '/vehicles'; }
        });
      });
    }
  };

  Drupal.behaviors.csDarkMode = {
    attach: function (context) {
      once('cs-dark', 'body', context).forEach(function (body) {
        // restore last choice so it survives navigation
        try {
          var saved = localStorage.getItem('cs-theme');
          if (saved === 'dark' || saved === 'light') { applyTheme(saved); }
        } catch (e) {}

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'cs-theme-toggle';
        function refresh() {
          var dark = document.documentElement.getAttribute('data-cs-theme') === 'dark';
          btn.textContent = dark ? '☀' : '☾';
          btn.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
          btn.title = btn.getAttribute('aria-label');
        }
        btn.addEventListener('click', function () {
          var next = document.documentElement.getAttribute('data-cs-theme') === 'dark' ? 'light' : 'dark';
          applyTheme(next);
          try { localStorage.setItem('cs-theme', next); } catch (e) {}
          refresh();
        });
        refresh();
        body.appendChild(btn);
      });
    }
  };

  Drupal.behaviors.csSuperHighlight = {
    attach: function (context) {
      once('cs-sh', '.superhighlight', context).forEach(function (root) {
        var slides = root.querySelectorAll('.sh-slide');
        var dots = root.querySelectorAll('.sh-dot');
        var n = slides.length, i = 0, timer = null;
        var secs = parseInt(root.getAttribute('data-interval'), 10) || 0;
        function show(k) {
          i = (k + n) % n;
          slides.forEach(function (s, j) { s.classList.toggle('is-active', j === i); });
          dots.forEach(function (d, j) { d.classList.toggle('is-active', j === i); });
        }
        function play() { stop(); if (secs > 0 && n > 1) { timer = setInterval(function () { show(i + 1); }, secs * 1000); } }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }
        var prev = root.querySelector('.sh-prev'), next = root.querySelector('.sh-next');
        if (n < 2) { [prev, next].forEach(function (b) { if (b) { b.style.display = 'none'; } }); }
        if (prev) { prev.addEventListener('click', function () { show(i - 1); play(); }); }
        if (next) { next.addEventListener('click', function () { show(i + 1); play(); }); }
        dots.forEach(function (d, j) { d.addEventListener('click', function () { show(j); play(); }); });
        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', play);
        var x0 = null;
        root.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
        root.addEventListener('touchend', function (e) {
          if (x0 === null) { return; }
          var dx = e.changedTouches[0].clientX - x0;
          if (Math.abs(dx) > 40) { show(dx < 0 ? i + 1 : i - 1); play(); }
          x0 = null;
        });
        play();
      });
    }
  };

  Drupal.behaviors.csCarCarousel = {
    attach: function (context) {
      once('cs-car-carousel', '.cs-car-carousel', context).forEach(function (root) {
        var track = root.querySelector('.views-view-grid');
        if (!track) { return; }

        // title on the left, nav arrows on the right, cards below
        var blockWrapper = root.closest('.block');
        var titleEl = blockWrapper ? blockWrapper.querySelector('.block__title') : null;
        if (blockWrapper) { blockWrapper.classList.add('cs-car-carousel-block'); }
        if (titleEl) { titleEl.classList.add('cs-car-carousel-title'); }

        var prev = document.createElement('button');
        prev.type = 'button';
        prev.className = 'cs-carousel-nav cs-carousel-prev';
        prev.innerHTML = '&#10094;';
        prev.setAttribute('aria-label', Drupal.t('Scroll left'));

        var next = document.createElement('button');
        next.type = 'button';
        next.className = 'cs-carousel-nav cs-carousel-next';
        next.innerHTML = '&#10095;';
        next.setAttribute('aria-label', Drupal.t('Scroll right'));

        var navGroup = document.createElement('div');
        navGroup.className = 'cs-carousel-nav-group';
        navGroup.appendChild(prev);
        navGroup.appendChild(next);

        if (titleEl) {
          titleEl.insertAdjacentElement('afterend', navGroup);
        }
        else {
          root.appendChild(navGroup);
        }

        function step() {
          var item = track.querySelector('.views-view-grid__item');
          var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || 18) || 18;
          return item ? item.getBoundingClientRect().width + gap : track.clientWidth * 0.8;
        }

        function updateNav() {
          var max = track.scrollWidth - track.clientWidth - 2;
          var overflowing = track.scrollWidth > track.clientWidth + 2;
          prev.hidden = !overflowing || track.scrollLeft <= 2;
          next.hidden = !overflowing || track.scrollLeft >= max;
        }

        prev.addEventListener('click', function () {
          track.scrollBy({ left: -step(), behavior: 'smooth' });
        });
        next.addEventListener('click', function () {
          track.scrollBy({ left: step(), behavior: 'smooth' });
        });
        track.addEventListener('scroll', updateNav, { passive: true });
        window.addEventListener('resize', updateNav);
        updateNav();
      });
    }
  };
})(Drupal, once);
