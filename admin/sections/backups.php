<?php
/** Резервные копии контента. */
declare(strict_types=1);
$list = list_backups();
?>
<p class="a-muted">Копия создаётся автоматически при каждом сохранении. Хранятся 30 последних. Если что-то испортили — вернитесь к нужной версии.</p>
<?php if (!$list): ?>
<p class="a-empty">Копий пока нет — они появятся после первого сохранения.</p>
<?php else: ?>
<table class="a-table">
  <thead><tr><th>Когда</th><th>Размер</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($list as $bk): ?>
  <tr>
    <td><?= e($bk['time']) ?></td>
    <td><?= e((string)$bk['size']) ?> КБ</td>
    <td style="text-align:right">
      <form method="post" class="a-inline" onsubmit="return confirm('Вернуть сайт к версии от <?= e($bk['time']) ?>? Текущая версия тоже сохранится в копию.')">
        <?= csrf_field() ?>
        <input type="hidden" name="_section" value="backups"><input type="hidden" name="_action" value="restore">
        <input type="hidden" name="file" value="<?= e($bk['file']) ?>">
        <input type="hidden" name="_back" value="<?= e(base_path().'/admin/?s=backups') ?>">
        <button class="a-btn a-btn--ghost a-btn--sm" type="submit">Восстановить</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
