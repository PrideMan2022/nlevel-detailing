<?php
/**
 * Онлайн-запись.
 *
 * Приложение больше не висит прямо на странице: страница грузится мгновенно,
 * а приложение открывается по кнопке во всплывающем окне поверх сайта.
 * Так человек не уходит с сайта и не ждёт лишнего при заходе.
 */
declare(strict_types=1);

$b = biz();

$schema = [[
    '@context' => 'https://schema.org',
    '@type'    => 'ReserveAction',
    'name'     => 'Онлайн-запись в детейлинг-студию NLeveL',
    'target'   => [
        '@type'          => 'EntryPoint',
        'urlTemplate'    => site_url() . '/booking/',
        'actionPlatform' => ['https://schema.org/DesktopWebPlatform', 'https://schema.org/MobileWebPlatform'],
    ],
    'result'   => ['@type' => 'Reservation', 'name' => 'Запись на услугу детейлинга'],
    'provider' => ['@id' => site_url() . '/#org'],
]];

$bookingFaq = [];
foreach (faq_items() as $f) {
    $k = mb_strtolower(($f['q'] ?? '') . ($f['a'] ?? ''), 'UTF-8');
    if (str_contains($k, 'записи') || str_contains($k, 'находитесь') || str_contains($k, 'по времени')) {
        $bookingFaq[] = $f;
    }
}

render_page($page, function () use ($b, $bookingFaq) { ?>

<section class="section--tight shell">
  <span class="eyebrow">Онлайн-запись</span>
  <h1><?= e($page['h1'] ?? 'Онлайн-запись') ?></h1>
  <p class="lede" style="margin-top:.7rem"><?= e(content()['bookingLede'] ?? '') ?></p>
</section>

<!-- Главное действие страницы -->
<section class="section--tight shell">
  <div class="book-hero">
    <div class="book-hero__icon"><?= icon('calendar') ?></div>
    <div class="book-hero__text">
      <h2>Записаться за минуту</h2>
      <p><?php if (($b['appMode'] ?? 'window') === 'modal'): ?>Выберите услугу, свободный день и время. Приложение откроется прямо здесь, поверх сайта — уходить никуда не нужно.<?php else: ?>Выберите услугу, свободный день и время. Приложение откроется в отдельном окне — там же работает вход через Telegram.<?php endif; ?></p>
    </div>
    <div class="book-hero__actions">
      <?php $mode = $b['appMode'] ?? 'window'; ?>
      <button class="btn btn--primary btn--lg" type="button" id="openApp"
              data-mode="<?= e($mode) ?>"
              data-src="<?= $mode === 'modal' ? asset('app/') : e($b['appUrl'] ?? '') ?>"
              data-window="<?= e($b['appUrl'] ?? '') ?>">
        <?= icon('calendar') ?>Записаться онлайн
      </button>
      <a class="btn btn--ghost" href="<?= e($b['appUrl'] ?? '') ?>" target="_blank" rel="noopener">
        <?= icon('external') ?>Открыть в новой вкладке
      </a>
    </div>
  </div>

  <div class="btn-row" style="margin-top:var(--gap-sm);justify-content:center">
    <a class="btn btn--ghost" href="tel:<?= e($b['phoneRaw'] ?? '') ?>"><?= icon('phone') ?><?= e($b['phone'] ?? '') ?></a>
    <a class="btn btn--wa" href="<?= e($b['whatsappUrl'] ?? '') ?>" target="_blank" rel="noopener"><?= icon('wa') ?>WhatsApp</a>
    <a class="btn btn--tg" href="<?= e($b['telegramUrl'] ?? '') ?>" target="_blank" rel="noopener"><?= icon('tg') ?>Telegram</a>
  </div>
</section>

<!-- Установка приложения на телефон -->
<section class="section--tight shell">
  <div class="section-head">
    <div>
      <span class="eyebrow">Приложение</span>
      <h2>Поставьте на телефон — и записывайтесь в два касания</h2>
      <p class="lede">Приложение ставится как обычное: иконка на рабочем столе, открывается на весь экран, без App Store и Google Play. Внутри — ваша история визитов и статус текущих работ.</p>
    </div>
  </div>

  <div class="split">
    <div class="panel">
      <h3 style="font-size:var(--step-1);margin-bottom:.8rem">Как установить</h3>
      <ol class="steps" style="grid-template-columns:1fr">
        <li><b>Откройте приложение</b>Кнопкой выше или по прямой ссылке.</li>
        <li><b>На iPhone</b>Нажмите «Поделиться» внизу браузера, затем «На экран Домой».</li>
        <li><b>На Android</b>Меню браузера с тремя точками, затем «Установить приложение».</li>
        <li><b>Готово</b>Иконка появится на рабочем столе рядом с остальными приложениями.</li>
      </ol>
      <div class="store-row" style="margin-top:var(--gap-sm)">
        <a class="store-btn" href="<?= e($b['appUrl'] ?? '') ?>" target="_blank" rel="noopener">
          <?= icon('apple') ?><span><b>Для iPhone</b><span>Safari → Поделиться</span></span>
        </a>
        <a class="store-btn" href="<?= e($b['appUrl'] ?? '') ?>" target="_blank" rel="noopener">
          <?= icon('android') ?><span><b>Для Android</b><span>Chrome → Установить</span></span>
        </a>
      </div>
    </div>

    <div class="panel">
      <h3 style="font-size:var(--step-1);margin-bottom:.7rem">Что внутри</h3>
      <ul class="tick-list">
        <li>Выбор услуги из полного прайса с ценами</li>
        <li>Свободные дни и время — видно сразу, без звонка</li>
        <li>Вход по номеру телефона или через Telegram</li>
        <li>История визитов и статус текущих работ</li>
        <li>Напоминание перед визитом</li>
        <li>Отдельные кабинеты для администратора и мастера</li>
      </ul>
    </div>
  </div>
</section>

<section class="section--tight shell">
  <div class="split">
    <div class="panel">
      <h2 style="font-size:var(--step-2);margin-bottom:.8rem">Как проходит запись</h2>
      <ol class="steps" style="grid-template-columns:1fr">
        <li><b>Записываетесь в приложении</b>Выбираете услугу, свободный день и время.</li>
        <li><b>Мы подтверждаем</b>Пишем или звоним, называем точную цену и срок работ.</li>
        <li><b>Напоминаем перед визитом</b>Чтобы вы не приехали зря — и чтобы слот не пропал.</li>
        <li><b>Пишем, когда готово</b>Не нужно звонить и спрашивать.</li>
      </ol>
    </div>
    <div class="panel">
      <h2 style="font-size:var(--step-2);margin-bottom:.8rem">Не хотите через приложение?</h2>
      <p class="muted" style="margin-bottom:.9rem">Позвоните или напишите в мессенджер — запишем вручную и подтвердим время.</p>
      <?php block_contacts(); ?>
    </div>
  </div>
</section>

<?php block_faq($bookingFaq, 'Вопросы про запись'); ?>

<?php }, $schema);
