<?php
/** robots.txt: ИИ-краулеры пускаем, агрессивные SEO-боты — нет. */
declare(strict_types=1);
// В файл попадают значения из настроек — вычищаем переводы строк,
// чтобы туда нельзя было дописать посторонние директивы
$su = preg_replace('~[\r\n]+~', '', site_url()) ?? '';
$utm = 'utm_source&utm_medium&utm_campaign&utm_term&utm_content&yclid&gclid&ymclid&_openstat&from&roistat&fbclid';
?>


# robots.txt — NLeveL, детейлинг-студия, Екатеринбург

User-agent: *
Allow: /
Disallow: /*?
Disallow: /*&
# Приложение онлайн-записи — интерфейс, а не контент
Disallow: /app/
# Служебное
Disallow: /admin/
Disallow: /inc/
Disallow: /data/
Allow: /assets/
Clean-param: <?= $utm ?>


User-agent: Yandex
Allow: /
Disallow: /app/
Disallow: /admin/
Disallow: /inc/
Disallow: /data/
Clean-param: <?= $utm ?>


<?php
/*
 * Именных групп с «Allow: /» здесь намеренно нет.
 * Робот подчиняется только одной группе — самой конкретной для себя.
 * Отдельная группа «User-agent: Googlebot / Allow: /» отменяла бы для него
 * все Disallow из группы «*», и Google полез бы в /app/ и /admin/.
 * Группа «*» и так всех пускает, а ИИ-краулеры не закрыты — этого достаточно.
 */
?>
<?php foreach (['AhrefsBot','SemrushBot','MJ12bot','DotBot','DataForSeoBot'] as $ua): ?>
User-agent: <?= $ua ?>

Disallow: /

<?php endforeach; ?>
Sitemap: <?= $su ?>/sitemap.xml
