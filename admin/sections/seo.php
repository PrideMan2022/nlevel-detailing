<?php
/** SEO: заголовки, описания, домен. */
declare(strict_types=1);
$c = content(); $b = biz();
a_form_open('seo');
?>
<?php a_group('Домен и подтверждение прав', 'Адрес влияет на canonical, карту сайта и ссылки в соцсетях. Меняйте после переезда на свой домен.'); ?>
<?php a_text('site_url', 'Адрес сайта', $c['site']['url'] ?? '', 'Без слэша в конце: https://nlevel-detailing.ru'); ?>
<div class="a-cols">
  <?php a_text('yandexVerification', 'Код Яндекс.Вебмастера', $b['yandexVerification'] ?? '', 'Только значение content, без тега'); ?>
  <?php a_text('googleVerification', 'Код Google Search Console', $b['googleVerification'] ?? ''); ?>
</div>

<?php a_group('IndexNow', 'После каждого сохранения сайт сам сообщает Яндексу и Bing, что страницы изменились — не дожидаясь, пока робот заглянет сам. Ручной переобход в Вебмастере при этом не нужен.'); ?>
<p class="a-muted">Состояние: <?= e(indexnow_status_text()) ?></p>
<?php $inKey = trim((string)($c['seoFiles']['indexnowKey'] ?? '')); ?>
<?php if ($inKey !== ''): ?>
<p class="a-muted">Файл подтверждения: <code><?= e(site_url() . '/' . $inKey . '.txt') ?></code> — он должен открываться, иначе поисковик отклонит уведомления.</p>
<?php else: ?>
<p class="a-muted">Ключ не задан — уведомления не отправляются.</p>
<?php endif; ?>

<?php a_group('Страницы', 'Заголовок — до 60 знаков, описание — до 160. Дальше поисковик обрежет.'); ?>
<?php foreach (all_pages() as $i => $p): ?>
<div class="a-card">
  <div class="a-card__num"><?= e($p['nav'] ?? $p['slug']) ?> <code>/<?= e($p['slug'] === 'index' ? '' : $p['slug'] . '/') ?></code></div>
  <input type="hidden" name="p_slug[<?= $i ?>]" value="<?= e($p['slug'] ?? '') ?>">
  <label class="a-field"><span class="a-field__label">Заголовок H1 на странице</span>
    <input type="text" name="p_h1[<?= $i ?>]" value="<?= e($p['h1'] ?? '') ?>"></label>
  <label class="a-field"><span class="a-field__label">Title (вкладка браузера и выдача)</span>
    <input type="text" name="p_title[<?= $i ?>]" value="<?= e($p['title'] ?? '') ?>" data-count="60"></label>
  <label class="a-field"><span class="a-field__label">Description (текст под ссылкой в выдаче)</span>
    <textarea name="p_desc[<?= $i ?>]" rows="2" data-count="160"><?= e($p['description'] ?? '') ?></textarea></label>
  <label class="a-field"><span class="a-field__label">Ключевые слова</span>
    <input type="text" name="p_keys[<?= $i ?>]" value="<?= e($p['keywords'] ?? '') ?>"></label>
</div>
<?php endforeach; ?>
<?php a_form_close('Сохранить SEO'); ?>
