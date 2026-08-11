<?php
/** robots.txt и карта сайта. */
declare(strict_types=1);
$f = content()['seoFiles'] ?? [];
$su = site_url();
?>
<p class="a-muted">Оба файла собираются на лету. Пока поле robots пустое, отдаётся стандартный вариант — он уже настроен правильно: пускает поисковики и ИИ-ассистентов, закрывает служебные папки и агрессивных SEO-роботов. Трогайте его, только если точно знаете, что меняете: ошибка здесь может убрать сайт из поиска.</p>

<div class="a-row" style="margin-bottom:1rem">
  <a class="a-btn a-btn--ghost a-btn--sm" href="<?= e($su) ?>/robots.txt" target="_blank" rel="noopener">Посмотреть robots.txt</a>
  <a class="a-btn a-btn--ghost a-btn--sm" href="<?= e($su) ?>/sitemap.xml" target="_blank" rel="noopener">Посмотреть sitemap.xml</a>
</div>

<?php a_form_open('files'); ?>

<?php a_group('robots.txt', 'Пусто — работает стандартный. {site} подставит адрес сайта, {host} — домен без протокола.'); ?>
<label class="a-field">
  <span class="a-field__label">Свой текст robots.txt</span>
  <textarea name="robots" rows="16" spellcheck="false" style="font-family:ui-monospace,Menlo,monospace;font-size:.85rem"><?= e($f['robots'] ?? '') ?></textarea>
  <span class="a-field__hint">Обязательно оставьте строку <code>Sitemap: {site}/sitemap.xml</code> — по ней поисковики находят карту.</span>
</label>

<details style="margin:.4rem 0 1rem">
  <summary style="cursor:pointer;color:var(--text-2);font-size:.88rem">Показать стандартный robots.txt — можно скопировать за основу</summary>
  <pre style="background:var(--bg);border:1px solid var(--line);border-radius:9px;padding:.9rem;overflow:auto;font-size:.78rem;margin-top:.5rem"><?php
    ob_start();
    $keep = $f['robots'] ?? '';
    $tmp = content();
    // временно убираем свой текст, чтобы увидеть стандартный
    require ROOT . '/inc/feeds/robots_default.php';
    echo e(ob_get_clean());
  ?></pre>
</details>

<?php a_group('Карта сайта', 'Собирается из страниц автоматически. Здесь можно исключить лишние и добавить свои адреса.'); ?>
<table class="a-table" style="margin-bottom:1rem">
  <thead><tr><th>Страница</th><th>Приоритет</th><th>Частота</th><th style="text-align:center">В карте</th></tr></thead>
  <tbody>
  <?php foreach (all_pages() as $i => $p): ?>
  <tr>
    <td>
      <?= e($p['nav'] ?? $p['slug']) ?><br>
      <code>/<?= e(($p['slug'] ?? '') === 'index' ? '' : $p['slug'] . '/') ?></code>
      <input type="hidden" name="sm_slug[<?= $i ?>]" value="<?= e($p['slug'] ?? '') ?>">
    </td>
    <td>
      <select name="sm_priority[<?= $i ?>]">
        <?php foreach (['1.0','0.9','0.8','0.7','0.5','0.3'] as $v): ?>
        <option value="<?= $v ?>"<?= ($p['priority'] ?? '0.7') === $v ? ' selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
    </td>
    <td>
      <select name="sm_freq[<?= $i ?>]">
        <?php foreach (['daily'=>'каждый день','weekly'=>'раз в неделю','monthly'=>'раз в месяц','yearly'=>'раз в год'] as $k=>$v): ?>
        <option value="<?= $k ?>"<?= ($p['changefreq'] ?? 'monthly') === $k ? ' selected' : '' ?>><?= e($v) ?></option>
        <?php endforeach; ?>
      </select>
    </td>
    <td style="text-align:center">
      <input type="checkbox" name="sm_in[<?= $i ?>]" value="1" style="width:20px;height:20px;accent-color:var(--accent)"<?= empty($p['noSitemap']) ? ' checked' : '' ?>>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php a_lines('extraUrls', 'Дополнительные адреса в карте', $f['extraUrls'] ?? [], 'По одному на строку. Можно относительный путь или полную ссылку.', 4); ?>
<?php a_form_close('Сохранить файлы'); ?>
