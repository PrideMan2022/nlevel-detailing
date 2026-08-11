<?php
/** Мелкие помощники для шаблонов. */
declare(strict_types=1);

require_once __DIR__ . '/icons.php';

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Ссылка внутри сайта: url('price') → /price/ */
function url(string $path = ''): string
{
    $path = trim($path, '/');
    return base_path() . '/' . ($path === '' ? '' : $path . '/');
}

/** Путь к файлу ассетов: asset('assets/css/style.css') */
function asset(string $path): string
{
    return base_path() . '/' . ltrim($path, '/');
}

/** Ассет с версией по хешу файла — чтобы браузер не держал старое. */
function asset_v(string $path): string
{
    $full = ROOT . '/' . ltrim($path, '/');
    $v = is_file($full) ? substr(md5_file($full) ?: '', 0, 8) : (string)time();
    return asset($path) . '?v=' . $v;
}

function money(int|float|string $n): string
{
    return number_format((float)$n, 0, ',', ' ') . ' ₽';
}

function icon(string $name, string $cls = ''): string
{
    $body = ICONS[$name] ?? ICONS['car'];
    $c = $cls !== '' ? ' class="' . e($cls) . '"' : '';
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" '
        . 'stroke-linecap="round" stroke-linejoin="round"' . $c . ' aria-hidden="true" focusable="false">'
        . $body . '</svg>';
}

function stars(int $n = 5): string
{
    $one = '<svg viewBox="0 0 24 24" aria-hidden="true">' . ICONS['star'] . '</svg>';
    return '<span class="stars" role="img" aria-label="Оценка ' . $n . ' из 5">'
        . str_repeat($one, $n) . '</span>';
}

function logo_mark(string $cls = 'logo__mark'): string
{
    return '<img class="' . e($cls) . '" src="' . asset('assets/icons/logo.webp') . '" width="96" height="96"'
        . ' alt="Логотип детейлинг-студии NLeveL" title="NLeveL — детейлинг в Екатеринбурге" decoding="async">';
}

/**
 * Картинка услуги или галереи. Если файла нет — отдаём заглушку,
 * чтобы админ не сломал вёрстку, удалив снимок.
 */
function img_src(string $relPath): string
{
    $full = ROOT . '/' . ltrim($relPath, '/');
    if (!is_file($full)) {
        return asset('assets/icons/logo.webp');
    }
    return asset($relPath);
}

function service_img(?string $name): ?string
{
    if (!$name) {
        return null;
    }
    foreach (['assets/img/services/' . $name . '.webp', 'assets/img/uploads/' . $name] as $p) {
        if (is_file(ROOT . '/' . $p)) {
            return asset($p);
        }
    }
    return null;
}

function gallery_img(string $file, bool $thumb = false): string
{
    $paths = $thumb
        ? ['assets/img/gallery/thumb/' . $file . '.webp', 'assets/img/uploads/' . $file]
        : ['assets/img/gallery/' . $file . '.webp', 'assets/img/uploads/' . $file];
    foreach ($paths as $p) {
        if (is_file(ROOT . '/' . $p)) {
            return asset($p);
        }
    }
    return asset('assets/icons/logo.webp');
}

/** Дата в человеческом виде: 2026-06-25 → 25 июня */
function ru_date(string $iso): string
{
    $months = [1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
    $ts = strtotime($iso);
    if (!$ts) {
        return $iso;
    }
    return (int)date('j', $ts) . ' ' . ($months[(int)date('n', $ts)] ?? '');
}

/** Первая буква имени для аватарки отзыва. */
function initial(string $name): string
{
    return mb_substr(trim($name), 0, 1, 'UTF-8');
}

/** Подставляет год/цифры в шаблонные строки вида {year}. */
function tpl(string $s): string
{
    return strtr($s, [
        '{year}'    => date('Y'),
        '{rating}'  => (string)rating_avg(),
        '{reviews}' => (string)reviews_total(),
        // Число вместе со словом в правильной форме: 1 отзыв, 56 отзывов.
        '{reviewsN}' => nplural(reviews_total(), ['отзыв', 'отзыва', 'отзывов']),
    ]);
}

/**
 * Склонение существительного при числе: 1 отзыв, 2 отзыва, 5 отзывов.
 * Раньше на сайте везде стояло «56 отзыва» — по-русски так не говорят.
 */
function plural(int $n, array $forms): string
{
    $n = abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) {
        return $forms[2];
    }
    if ($n1 > 1 && $n1 < 5) {
        return $forms[1];
    }
    if ($n1 === 1) {
        return $forms[0];
    }
    return $forms[2];
}

/** Число со словом: «56 отзывов». */
function nplural(int $n, array $forms): string
{
    return $n . ' ' . plural($n, $forms);
}
