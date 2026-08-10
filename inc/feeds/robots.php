<?php
/** robots.txt: ИИ-краулеры пускаем, агрессивные SEO-боты — нет. */
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');
// В файл попадают значения из настроек — вычищаем переводы строк,
// чтобы туда нельзя было дописать посторонние директивы
$su = preg_replace('~[\r\n]+~', '', site_url()) ?? '';
$host = preg_replace('~^https?://~', '', $su);
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
Disallow: /*?
Disallow: /app/
Disallow: /admin/
Disallow: /inc/
Disallow: /data/
Clean-param: <?= $utm ?>

<?php foreach (['Googlebot','GPTBot','OAI-SearchBot','ChatGPT-User','ClaudeBot','Claude-User','Claude-SearchBot','PerplexityBot','Perplexity-User','Google-Extended','Applebot','Applebot-Extended','YandexAdditional','Bingbot','Amazonbot','meta-externalagent'] as $ua): ?>
User-agent: <?= $ua ?>

Allow: /

<?php endforeach; ?>
<?php foreach (['AhrefsBot','SemrushBot','MJ12bot','DotBot','DataForSeoBot'] as $ua): ?>
User-agent: <?= $ua ?>

Disallow: /

<?php endforeach; ?>
Sitemap: <?= $su ?>/sitemap.xml
Host: <?= $host ?>
