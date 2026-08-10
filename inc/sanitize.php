<?php
/**
 * Очистка HTML в полях, где админу разрешено немного разметки.
 *
 * Зачем. В нескольких местах (абзацы «О студии», примечания к прайсу,
 * блок про маршрут) удобно писать <strong>жирным</strong>. Значит, эти поля
 * выводятся без экранирования — и туда теоретически может попасть скрипт,
 * если текст скопировали с заражённой страницы. Поэтому на сохранении
 * оставляем только безопасные теги, всё остальное превращаем в текст.
 */
declare(strict_types=1);

const ALLOWED_TAGS = '<strong><b><em><i><br><a><span>';

function clean_html(string $html): string
{
    // 1. Оставляем только разрешённые теги
    $out = strip_tags($html, ALLOWED_TAGS);

    // 2. У ссылок оставляем только href, и только на http/https/tel/mailto
    $out = preg_replace_callback('~<a\b([^>]*)>~i', function ($m) {
        if (!preg_match('~href\s*=\s*["\']([^"\']*)["\']~i', $m[1], $h)) {
            return '<a>';
        }
        $href = trim($h[1]);
        if (!preg_match('~^(https?://|/|tel:|mailto:|#)~i', $href)) {
            return '<a>';
        }
        $safe = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
        $ext = str_starts_with($href, 'http') ? ' target="_blank" rel="noopener"' : '';
        return '<a href="' . $safe . '"' . $ext . '>';
    }, $out) ?? $out;

    // 3. У остальных тегов срезаем все атрибуты — там могли быть onclick и style
    $out = preg_replace('~<(?!a\b)(/?)([a-z0-9]+)[^>]*>~i', '<$1$2>', $out) ?? $out;

    return trim($out);
}

/** Очистить массив строк. */
function clean_html_lines(array $lines): array
{
    return array_values(array_filter(array_map('clean_html', $lines), fn($s) => $s !== ''));
}
