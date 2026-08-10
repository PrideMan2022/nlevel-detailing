/* Админка: боковое меню на телефоне, счётчики длины, защита от потери правок. */
(function () {
  'use strict';

  /* Меню на узком экране */
  var burger = document.querySelector('.a-burger');
  var side = document.querySelector('.a-side');
  if (burger && side) {
    burger.addEventListener('click', function () {
      side.classList.toggle('is-open');
    });
  }

  /* Счётчик символов там, где длина важна для выдачи */
  document.querySelectorAll('[data-count]').forEach(function (el) {
    var max = parseInt(el.dataset.count, 10);
    var out = document.createElement('span');
    out.className = 'a-counter';
    el.parentNode.appendChild(out);
    var upd = function () {
      var n = el.value.length;
      out.textContent = n + ' из ' + max + (n > max ? ' — длиннее, поисковик обрежет' : '');
      out.classList.toggle('is-over', n > max);
    };
    el.addEventListener('input', upd);
    upd();
  });

  /* Не дать уйти со страницы с несохранёнными правками */
  var dirty = false;
  document.querySelectorAll('form.a-form').forEach(function (f) {
    f.addEventListener('input', function () { dirty = true; });
    f.addEventListener('submit', function () { dirty = false; });
  });
  window.addEventListener('beforeunload', function (e) {
    if (!dirty) return;
    e.preventDefault();
    e.returnValue = '';
  });

  /* Отправка формы по Ctrl/Cmd+S */
  document.addEventListener('keydown', function (e) {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's') {
      var f = document.querySelector('form.a-form');
      if (f) { e.preventDefault(); dirty = false; f.submit(); }
    }
  });
})();
