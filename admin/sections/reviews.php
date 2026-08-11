<?php
/** Тексты страницы отзывов. Сами отзывы приходят из Яндекс.Карт и не редактируются. */
declare(strict_types=1);
$c = content();
$h = $c['reviewsHonesty'] ?? [];
$b = biz();
?>
<div class="a-card">
  <h3>Отзывы обновляются сами</h3>
  <p class="a-muted">На сайте стоит официальный виджет Яндекс.Карт: новый отзыв появляется там же, где и на карточке организации, без вашего участия. Вручную отзывы не добавляются и не удаляются — так честнее и так их нельзя подделать.</p>
  <p class="a-muted">Номер организации на Яндекс.Картах: <code><?= e($b['yandexOrgId'] ?? 'не указан') ?></code> — меняется в разделе «Контакты». Там же задаются оценки и количество отзывов, которые показываются в плашке рейтинга и уходят в микроразметку.</p>
  <div class="a-row">
    <a class="a-btn a-btn--ghost a-btn--sm" href="<?= e($b['yandexMapUrl'] ?? '') ?>" target="_blank" rel="noopener">Карточка на Яндексе</a>
    <a class="a-btn a-btn--ghost a-btn--sm" href="<?= e($b['gis2ReviewsUrl'] ?? '') ?>" target="_blank" rel="noopener">Отзывы в 2ГИС</a>
  </div>
</div>

<?php a_form_open('reviews'); ?>
<?php a_area('reviewsLede', 'Вводный текст страницы отзывов', $c['reviewsLede'] ?? '', '', 3); ?>

<?php a_group('Блок «О наших минусах»', 'Честный разбор негатива внизу страницы. Оставьте заголовок пустым, чтобы убрать блок совсем.'); ?>
<?php a_text('honesty_title', 'Заголовок', $h['title'] ?? ''); ?>
<?php a_lines('honesty_paragraphs', 'Абзацы', $h['paragraphs'] ?? [], 'По одному абзацу на строку. Можно <strong>жирный</strong>.', 5); ?>
<?php a_form_close(); ?>
