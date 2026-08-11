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
<?php
/* Если в админке задан свой текст — отдаём его и ничего не подставляем */
$custom = trim((string)(content()['seoFiles']['robots'] ?? ''));
if ($custom !== '') {
    // Подстановки, чтобы при смене домена не переписывать файл руками
    echo strtr($custom, ['{site}' => $su, '{host}' => $host]);
    return;
}
?>
<?php require __DIR__ . '/robots_default.php';
