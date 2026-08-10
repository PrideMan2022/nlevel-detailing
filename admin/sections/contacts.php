<?php
/** Контакты и реквизиты. */
declare(strict_types=1);
$b = biz(); $r = content()['route'] ?? [];
a_form_open('contacts');
?>
<?php a_group('Основное'); ?>
<div class="a-cols">
  <?php a_text('name', 'Название студии', $b['name'] ?? ''); ?>
  <?php a_text('hours', 'Часы работы', $b['hours'] ?? ''); ?>
</div>
<div class="a-cols">
  <?php a_text('city', 'Город', $b['city'] ?? ''); ?>
  <?php a_text('district', 'Район', $b['district'] ?? ''); ?>
  <?php a_text('region', 'Область', $b['region'] ?? ''); ?>
</div>
<?php a_text('street', 'Улица и дом', $b['street'] ?? ''); ?>
<?php a_text('addressNote', 'Как заехать', $b['addressNote'] ?? ''); ?>
<div class="a-cols">
  <?php a_num('lat', 'Широта', $b['lat'] ?? ''); ?>
  <?php a_num('lon', 'Долгота', $b['lon'] ?? ''); ?>
</div>

<?php a_group('Связь'); ?>
<div class="a-cols">
  <?php a_text('phone', 'Телефон (как показывать)', $b['phone'] ?? '', 'Например: +7 (932) 707-40-77'); ?>
  <?php a_text('phoneRaw', 'Телефон для ссылки', $b['phoneRaw'] ?? '', 'Только цифры с плюсом: +79327074077'); ?>
</div>
<div class="a-cols">
  <?php a_text('whatsapp', 'Номер WhatsApp', $b['whatsapp'] ?? ''); ?>
  <?php a_text('whatsappUrl', 'Ссылка WhatsApp', $b['whatsappUrl'] ?? ''); ?>
</div>
<div class="a-cols">
  <?php a_text('telegram', 'Telegram без @', $b['telegram'] ?? ''); ?>
  <?php a_text('telegramUrl', 'Ссылка Telegram', $b['telegramUrl'] ?? ''); ?>
</div>
<div class="a-cols">
  <?php a_text('vkUrl', 'ВКонтакте', $b['vkUrl'] ?? ''); ?>
  <?php a_text('appUrl', 'Приложение записи', $b['appUrl'] ?? ''); ?>
</div>

<?php a_group('Карточки и рейтинги', 'Оценки показываются на сайте и уходят в микроразметку. Обновляйте, когда меняются на картах.'); ?>
<div class="a-cols a-cols--4">
  <?php a_num('ratingYandex', 'Оценка Яндекс', $b['ratingYandex'] ?? ''); ?>
  <?php a_num('reviewsYandex', 'Отзывов Яндекс', $b['reviewsYandex'] ?? ''); ?>
  <?php a_num('rating2gis', 'Оценка 2ГИС', $b['rating2gis'] ?? ''); ?>
  <?php a_num('reviews2gis', 'Отзывов 2ГИС', $b['reviews2gis'] ?? ''); ?>
</div>
<?php a_text('yandexMapUrl', 'Карточка на Яндекс.Картах', $b['yandexMapUrl'] ?? ''); ?>
<?php a_text('yandexRouteUrl', 'Ссылка «построить маршрут»', $b['yandexRouteUrl'] ?? ''); ?>
<?php a_text('gis2Url', 'Карточка 2ГИС', $b['gis2Url'] ?? ''); ?>
<?php a_text('gis2ReviewsUrl', 'Отзывы 2ГИС', $b['gis2ReviewsUrl'] ?? ''); ?>
<?php a_text('zoonUrl', 'Карточка Zoon', $b['zoonUrl'] ?? ''); ?>

<?php a_group('Блок «Как доехать»'); ?>
<?php a_text('route_title', 'Заголовок', $r['title'] ?? ''); ?>
<?php a_lines('route_paragraphs', 'Абзацы', $r['paragraphs'] ?? [], 'По одному абзацу на строку.', 5); ?>
<?php a_text('route_payment', 'Оплата', $r['payment'] ?? ''); ?>
<?php a_text('route_schedule', 'Про запись', $r['schedule'] ?? ''); ?>
<?php a_form_close(); ?>
