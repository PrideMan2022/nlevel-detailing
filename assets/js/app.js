/* ==========================================================================
   NLeveL — клиентские сценарии.
   Без зависимостей, без внешних запросов. ~7 КБ.
   ========================================================================== */
(function () {
  'use strict';

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* ---------- 1. Тень у шапки при скролле ---------- */
  var appbar = $('#appbar');
  var fab = $('.fab');
  if (appbar || fab) {
    var onScroll = function () {
      if (appbar) appbar.classList.toggle('is-stuck', window.scrollY > 8);
      // Плавающие кнопки появляются после первого экрана, чтобы не закрывать текст
      if (fab) fab.classList.toggle('is-on', window.scrollY > window.innerHeight * 0.55);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ---------- 2. Бегунок в десктоп-меню ---------- */
  var nav = $('#nav');
  var pill = $('#navPill');
  if (nav && pill) {
    var moveTo = function (el) {
      if (!el) { pill.classList.remove('is-on'); return; }
      // offsetLeft врёт для пункта «Услуги» (он внутри .nav__group с position:relative),
      // а статическая позиция самой пилюли смещена на паддинг и рамку меню.
      // Поэтому двигаем от текущего положения на разницу — так смещение не накапливается.
      var target = el.getBoundingClientRect();
      var now = pill.getBoundingClientRect();
      var x = parseFloat(pill.dataset.x || '0');
      pill.dataset.x = x + (target.left - now.left);
      pill.style.width = target.width + 'px';
      pill.style.transform = 'translateX(' + pill.dataset.x + 'px)';
      pill.classList.add('is-on');
    };
    var active = $('.nav__link[aria-current="page"]', nav);
    // Ждём раскладку, чтобы offsetLeft был верным
    requestAnimationFrame(function () { moveTo(active); });

    $$('.nav__link', nav).forEach(function (l) {
      l.addEventListener('mouseenter', function () { moveTo(l); });
      l.addEventListener('focus', function () { moveTo(l); });
    });
    nav.addEventListener('mouseleave', function () { moveTo(active); });
    window.addEventListener('resize', function () { moveTo(active); });
  }

  /* ---------- 3. Мобильная шторка меню ---------- */
  var sheet = $('#sheet');
  var burger = $('#burger');
  if (sheet && burger) {
    var lastFocus = null;

    var openSheet = function () {
      lastFocus = document.activeElement;
      sheet.dataset.open = 'true';
      burger.setAttribute('aria-expanded', 'true');
      document.documentElement.style.overflow = 'hidden';
      var first = $('a, button', sheet);
      if (first) first.focus();
    };

    var closeSheet = function () {
      sheet.dataset.open = 'false';
      burger.setAttribute('aria-expanded', 'false');
      document.documentElement.style.overflow = '';
      if (lastFocus) lastFocus.focus();
    };

    burger.addEventListener('click', function () {
      sheet.dataset.open === 'true' ? closeSheet() : openSheet();
    });

    sheet.addEventListener('click', function (e) {
      if (e.target.hasAttribute('data-close') || e.target.closest('a')) closeSheet();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sheet.dataset.open === 'true') closeSheet();
    });

    // Свайп вниз закрывает шторку
    var panel = $('.sheet__panel', sheet);
    var startY = 0;
    if (panel) {
      panel.addEventListener('touchstart', function (e) { startY = e.touches[0].clientY; }, { passive: true });
      panel.addEventListener('touchend', function (e) {
        if (panel.scrollTop <= 0 && e.changedTouches[0].clientY - startY > 70) closeSheet();
      }, { passive: true });
    }
  }

  /* ---------- 4. Появление блоков при скролле ---------- */
  var reveals = $$('.reveal');
  if (reveals.length) {
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) {
            en.target.classList.add('is-in');
            io.unobserve(en.target);
          }
        });
      }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
      reveals.forEach(function (el) { io.observe(el); });
    } else {
      reveals.forEach(function (el) { el.classList.add('is-in'); });
    }
  }

  /* ---------- 5. Фильтр галереи ---------- */
  var filterBtns = $$('.filters [data-filter]');
  if (filterBtns.length) {
    filterBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var f = btn.dataset.filter;
        filterBtns.forEach(function (b) { b.setAttribute('aria-pressed', String(b === btn)); });
        $$('#galleryGrid .gallery__item').forEach(function (item) {
          item.style.display = f === 'all' || item.dataset.cat === f ? '' : 'none';
        });
      });
    });
  }

  /* ---------- 6. Лайтбокс ---------- */
  var items = $$('.gallery__item');
  if (items.length) {
    var lb = document.createElement('div');
    lb.className = 'lightbox';
    lb.dataset.open = 'false';
    lb.setAttribute('role', 'dialog');
    lb.setAttribute('aria-modal', 'true');
    lb.setAttribute('aria-label', 'Просмотр фотографии работы');
    lb.innerHTML =
      '<button class="lightbox__close" type="button" aria-label="Закрыть"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg></button>' +
      '<button class="lightbox__nav lightbox__nav--prev" type="button" aria-label="Предыдущее фото"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m14.5 5-7 7 7 7"/></svg></button>' +
      '<button class="lightbox__nav lightbox__nav--next" type="button" aria-label="Следующее фото"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9.5 5 7 7-7 7"/></svg></button>' +
      '<div><img alt=""><p class="lightbox__cap"></p></div>';
    document.body.appendChild(lb);

    var lbImg = $('img', lb);
    var lbCap = $('.lightbox__cap', lb);
    var idx = 0;
    var visible = function () { return items.filter(function (i) { return i.style.display !== 'none'; }); };

    var show = function (i) {
      var list = visible();
      if (!list.length) return;
      idx = (i + list.length) % list.length;
      var el = list[idx];
      lbImg.src = el.dataset.full;
      lbImg.alt = el.dataset.alt || '';
      lbCap.textContent = el.dataset.alt || '';
    };

    var openLb = function (i) {
      show(i);
      lb.dataset.open = 'true';
      document.documentElement.style.overflow = 'hidden';
      $('.lightbox__close', lb).focus();
    };

    var closeLb = function () {
      lb.dataset.open = 'false';
      document.documentElement.style.overflow = '';
      lbImg.src = '';
    };

    items.forEach(function (item) {
      item.setAttribute('tabindex', '0');
      item.setAttribute('role', 'button');
      var go = function () { openLb(visible().indexOf(item)); };
      item.addEventListener('click', go);
      item.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); go(); }
      });
    });

    $('.lightbox__close', lb).addEventListener('click', closeLb);
    $('.lightbox__nav--prev', lb).addEventListener('click', function () { show(idx - 1); });
    $('.lightbox__nav--next', lb).addEventListener('click', function () { show(idx + 1); });
    lb.addEventListener('click', function (e) { if (e.target === lb) closeLb(); });

    document.addEventListener('keydown', function (e) {
      if (lb.dataset.open !== 'true') return;
      if (e.key === 'Escape') closeLb();
      if (e.key === 'ArrowLeft') show(idx - 1);
      if (e.key === 'ArrowRight') show(idx + 1);
    });
  }

  /* ---------- 7. Карта грузится по клику (не тормозим первую загрузку) ---------- */
  var stub = $('#mapStub');
  if (stub) {
    var loadMap = function () {
      var wrap = stub.parentNode;
      var f = document.createElement('iframe');
      f.src =
        'https://yandex.ru/map-widget/v1/?ll=60.693001%2C56.836195&z=17&mode=search&text=NLeveL%20%D0%B4%D0%B5%D1%82%D0%B5%D0%B9%D0%BB%D0%B8%D0%BD%D0%B3%20%D0%95%D0%BA%D0%B0%D1%82%D0%B5%D1%80%D0%B8%D0%BD%D0%B1%D1%83%D1%80%D0%B3';
      f.title = 'Карта проезда к детейлинг-студии NLeveL, Екатеринбург, 40-летия Комсомола, 4Д';
      f.loading = 'lazy';
      f.setAttribute('allowfullscreen', '');
      f.referrerPolicy = 'no-referrer-when-downgrade';
      wrap.appendChild(f);
      stub.remove();
    };
    stub.addEventListener('click', loadMap);
    stub.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); loadMap(); }
    });
  }

  /* ---------- 7b. Приложение онлайн-записи грузим по клику ---------- */
  var appStub = $('#appStub');
  if (appStub) {
    var loadApp = function () {
      var host = appStub.parentNode;
      var f = document.createElement('iframe');
      f.src = appStub.dataset.src;
      f.title = 'Приложение онлайн-записи в детейлинг-студию NLeveL';
      f.setAttribute('allow', 'clipboard-write; web-share');
      f.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
      host.appendChild(f);
      appStub.remove();

      // Если приложение запретит фрейминг, страница останется пустой —
      // через 4 с предлагаем открыть в новой вкладке
      setTimeout(function () {
        var doc = null;
        try { doc = f.contentDocument; } catch (e) { /* кросс-домен — значит загрузилось */ }
        if (doc && doc.body && doc.body.innerHTML.trim() === '') {
          var note = document.createElement('div');
          note.className = 'appframe__stub';
          note.innerHTML =
            '<h3>Приложение не открылось во фрейме</h3>' +
            '<p>Откройте его в отдельной вкладке — там всё работает как обычно.</p>' +
            '<a class="btn btn--primary btn--lg" href="' + appStub.dataset.src + '" target="_blank" rel="noopener">Открыть приложение</a>';
          host.appendChild(note);
        }
      }, 4000);
    };
    appStub.addEventListener('click', loadApp);
    appStub.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); loadApp(); }
    });

    // Приложение подгружается само, как только окно попадает в поле зрения:
    // клик не нужен, никуда не перекидывает. Сборка тяжёлая, поэтому
    // не тянем её тем, кто до этого блока не долистал.
    if ('IntersectionObserver' in window) {
      var appIo = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) {
          appIo.disconnect();
          loadApp();
        }
      }, { rootMargin: '200px' });
      appIo.observe(appStub);
    } else {
      loadApp();
    }
  }

  /* ---------- 8. Маска телефона ---------- */
  var tel = $('#tel');
  if (tel) {
    tel.addEventListener('input', function () {
      var d = tel.value.replace(/\D/g, '');
      if (d[0] === '8') d = '7' + d.slice(1);
      if (d[0] !== '7') d = '7' + d;
      d = d.slice(0, 11);
      var out = '+7';
      if (d.length > 1) out += ' ' + d.slice(1, 4);
      if (d.length >= 5) out += ' ' + d.slice(4, 7);
      if (d.length >= 8) out += '-' + d.slice(7, 9);
      if (d.length >= 10) out += '-' + d.slice(9, 11);
      tel.value = out;
    });
  }

  /* ---------- 9. Форма записи ---------- */
  var form = $('#bookingForm');
  if (form) {
    // Минимальная дата — сегодня
    var dateInput = $('#date', form);
    if (dateInput) {
      var t = new Date();
      dateInput.min = t.toISOString().slice(0, 10);
      if (!dateInput.value) {
        t.setDate(t.getDate() + 1);
        dateInput.value = t.toISOString().slice(0, 10);
      }
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!form.reportValidity()) return;

      var fd = new FormData(form);
      var d = fd.get('date');
      var human = d
        ? new Date(d).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', weekday: 'short' })
        : '';

      var lines = [
        'Заявка на запись — NLeveL',
        'Услуга: ' + (fd.get('service') || '—'),
        'Дата: ' + human + ', время ' + (fd.get('time') || '—'),
        'Имя: ' + (fd.get('name') || '—'),
        'Телефон: ' + (fd.get('phone') || '—'),
        'Связь: ' + (fd.get('channel') || '—'),
      ];
      var car = fd.get('car');
      if (car) lines.push('Авто: ' + car);
      var comment = fd.get('comment');
      if (comment) lines.push('Комментарий: ' + comment);

      var text = lines.join('\n');

      var result = $('#formResult');
      $('#resultText').textContent = text;
      $('#waSend').href = 'https://wa.me/79221022211?text=' + encodeURIComponent(text);
      $('#tgSend').href = 'https://t.me/nlevel_studio';

      // Кладём текст в буфер, чтобы в Telegram осталось только вставить
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).catch(function () {});
      }

      form.style.display = 'none';
      result.dataset.show = 'true';
      result.scrollIntoView({ behavior: 'smooth', block: 'center' });

      // Событие для Яндекс.Метрики, если она подключена
      if (typeof window.ym === 'function' && window.__ymId) {
        window.ym(window.__ymId, 'reachGoal', 'booking_submit');
      }
    });
  }

  /* ---------- 10. Плавная прокрутка к якорям прайса ---------- */
  $$('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var id = a.getAttribute('href');
      if (id.length < 2) return;
      var target = document.getElementById(id.slice(1));
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      history.replaceState(null, '', id);
    });
  });
})();
