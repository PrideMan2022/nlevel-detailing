<?php
/** sitemap.xml из страниц контента. */
declare(strict_types=1);
header('Content-Type: application/xml; charset=utf-8');
$su = site_url();
$today = date('Y-m-d');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
<?php foreach (all_pages() as $p):
    $slug = ($p['slug'] ?? '') === 'index' ? '' : ($p['slug'] ?? ''); ?>
  <url>
    <loc><?= e($su . '/' . ($slug === '' ? '' : $slug . '/')) ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq><?= e($p['changefreq'] ?? 'monthly') ?></changefreq>
    <priority><?= e($p['priority'] ?? '0.7') ?></priority>
<?php if ($slug === ''): foreach (array_slice(gallery(), 0, 10) as $g): ?>
    <image:image>
      <image:loc><?= e($su . '/assets/img/gallery/' . ($g['f'] ?? '') . '.webp') ?></image:loc>
      <image:title><?= e(($g['cat'] ?? '') . ' — NLeveL, Екатеринбург') ?></image:title>
      <image:caption><?= e($g['alt'] ?? '') ?></image:caption>
    </image:image>
<?php endforeach; endif; ?>
  </url>
<?php endforeach; ?>
</urlset>
