<?php
/** Онлайн-запись: встроенное приложение студии. */
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
  <p class="lede" style="margin-top:.7rem"><?= e(content()['bookingLede'] ?? 'Записывайтесь прямо здесь — в нашем приложении. Выберите услугу, свободный слот и мастера, а мы подтвердим визит. Вы точно не приедете к закрытым воротам.') ?></p>
</section>

<section class="section--tight shell" id="app">
  <div class="appframe">
    <div class="appframe__bar">
      <span class="appframe__dots" aria-hidden="true"><i></i><i></i><i></i></span>
      <span class="appframe__url">Онлайн-запись NLeveL — выбор услуги, дня и времени</span>
      <a class="chip" href="<?= e($b['appUrl'] ?? '') ?>" target="_blank" rel="noopener" aria-label="Открыть приложение в новой вкладке"><?= icon('external') ?></a>
    </div>
    <div class="appframe__body">
      <div class="appframe__stub" id="appStub" role="button" tabindex="0"
           data-src="<?= asset('app/') ?>" aria-label="Приложение онлайн-записи NLeveL">
        <?= icon('app') ?>
        <h3>Приложение онлайн-записи</h3>
        <p>Вход по номеру телефона или через Telegram. Внутри — выбор услуги, свободные слоты, история визитов и статус работ. Отдельные входы для администратора и мастера.</p>
        <span class="btn btn--primary btn--lg"><?= icon('calendar') ?>Загружаем приложение…</span>
        <span class="small">Не загрузилось? Нажмите, чтобы открыть</span>
      </div>
    </div>
  </div>

  <div class="split" style="margin-top:var(--gap-md);gap:var(--gap-md)">
    <div>
      <h2 style="font-size:var(--step-2);margin-bottom:.6rem">Установите на телефон</h2>
      <p class="muted" style="margin-bottom:.9rem">Приложение ставится как обычное — с иконкой на рабочем столе, без App Store и Google Play.</p>
      <div class="store-row">
        <a class="store-btn" href="<?= e($b['appUrl'] ?? '') ?>" target="_blank" rel="noopener">
          <?= icon('apple') ?><span><b>Для iPhone</b><span>Поделиться → «На экран Домой»</span></span>
        </a>
        <a class="store-btn" href="<?= e($b['appUrl'] ?? '') ?>" target="_blank" rel="noopener">
          <?= icon('android') ?><span><b>Для Android</b><span>Меню → «Установить приложение»</span></span>
        </a>
      </div>
    </div>
    <div class="panel">
      <h3 style="font-size:var(--step-1);margin-bottom:.7rem">Что умеет приложение</h3>
      <ul class="tick-list">
        <li>Выбор услуги из полного прайса</li>
        <li>Свободные слоты в реальном времени</li>
        <li>Вход по номеру телефона или через Telegram</li>
        <li>История визитов и статус текущих работ</li>
        <li>Кабинеты администратора и мастера</li>
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
