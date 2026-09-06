<?php
/**
 * Загрузка картинок.
 *
 * Файл никогда не сохраняется «как есть»: он пересобирается через GD.
 * Это отсекает картинки с зашитым внутрь кодом — на выходе всегда
 * чистое изображение, собранное нами из пикселей.
 */
declare(strict_types=1);

/**
 * Принять загруженный файл и положить в assets/img/uploads.
 * Возвращает ['ok'=>true,'name'=>'имя.webp'] или ['ok'=>false,'error'=>'...'].
 */
function handle_upload(array $file, string $prefix = 'img'): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => 'Файл не выбран'];
    }
    if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Ошибка загрузки (код ' . (int)$file['error'] . ')'];
    }
    if (!is_uploaded_file($file['tmp_name'] ?? '')) {
        return ['ok' => false, 'error' => 'Некорректная загрузка'];
    }
    if (($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
        return ['ok' => false, 'error' => 'Файл больше ' . round(MAX_UPLOAD_BYTES / 1048576) . ' МБ'];
    }

    // Тип определяем по содержимому, а не по имени файла
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($file['tmp_name']);
    if (!isset(ALLOWED_IMAGE_MIME[$mime])) {
        return ['ok' => false, 'error' => 'Только JPG, PNG или WebP (получен ' . $mime . ')'];
    }

    $info = @getimagesize($file['tmp_name']);
    if (!$info || ($info[0] ?? 0) < 10 || ($info[1] ?? 0) < 10) {
        return ['ok' => false, 'error' => 'Файл не похож на картинку'];
    }

    // Снимок с телефона разворачивается в памяти в сырые пиксели: 4 байта на
    // точку. Кадр 16 Мп — это 61 МБ при лимите хостинга в 64 МБ, и PHP умирает
    // ДО того, как успеет что-либо сообщить: браузер получает пустой ответ,
    // а в админке выглядит так, будто кнопку не нажимали. Поэтому лимит
    // поднимаем заранее и по факту, а не надеемся на настройки сервера.
    if (!ensure_memory_for($info[0], $info[1])) {
        return ['ok' => false, 'error' => sprintf(
            'Фотография слишком большая: %d×%d (%.0f Мп). Хостинг не даёт столько памяти на обработку. '
            . 'Уменьшите кадр или пришлите другой.',
            $info[0], $info[1], $info[0] * $info[1] / 1e6
        )];
    }
    @set_time_limit(120);

    // Пересобираем изображение — так внутрь ничего постороннего не пролезет
    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
        'image/png'  => @imagecreatefrompng($file['tmp_name']),
        'image/webp' => @imagecreatefromwebp($file['tmp_name']),
        default      => false,
    };
    if (!$src) {
        return ['ok' => false, 'error' => 'Не удалось прочитать изображение'];
    }

    if (!is_dir(UPLOAD_DIR)) {
        @mkdir(UPLOAD_DIR, 0775, true);
    }

    $base = $prefix . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3));
    $full = UPLOAD_DIR . '/' . $base . '.webp';
    $thumb = UPLOAD_DIR . '/' . $base . '-thumb.webp';

    // Порядок здесь выбран ради памяти.
    //
    // Сначала уменьшаем до 1600 и только потом поворачиваем: imagerotate делает
    // ВТОРУЮ полную копию, и на исходных 36 Мп это лишние 146 МБ в пике. Для
    // поворотов, кратных 90°, порядок на результат не влияет — уменьшение
    // идёт по большей стороне, а она при повороте просто меняется местами
    // с меньшей.
    //
    // imagedestroy() с PHP 8.0 ничего не освобождает: GdImage — обычный объект,
    // память отдаётся, когда пропадает последняя ссылка. Поэтому именно unset,
    // а не imagedestroy, иначе оригинал продолжает висеть в памяти.
    $big = scale_to($src, 1600);
    if ($big !== $src) {
        unset($src);
    }

    // Телефон часто пишет кадр «лёжа», а поворот держит отдельной пометкой EXIF.
    // GD её не читает и сохраняет как есть — портретный снимок ложится набок.
    // Расширения exif на хостинге нет, поэтому пометку достаём сами.
    $big = apply_orientation($big, exif_orientation($file['tmp_name'], $mime));

    // PNG-8 и другие палитровые картинки imagewebp() сохранять не умеет:
    // он пишет предупреждение, создаёт файл НУЛЕВОЙ длины и возвращает true.
    // Без этой строки скриншот или логотип «загружался» бы успешно, а на сайте
    // висела бы битая картинка.
    if (!imageistruecolor($big)) {
        imagepalettetotruecolor($big);
    }

    $ok = imagewebp($big, $full, 82) && is_file($full) && filesize($full) > 0;
    if ($ok) {
        $small = scale_to($big, 600);
        $ok = imagewebp($small, $thumb, 74) && is_file($thumb) && filesize($thumb) > 0;
        if ($small !== $big) {
            unset($small);
        }
    }
    unset($big);

    if (!$ok) {
        // Убираем за собой: иначе полноразмерный файл остался бы в папке
        // навсегда — в content.json записи нет, из админки его не видно
        @unlink($full);
        @unlink($thumb);
        return ['ok' => false, 'error' => 'Не удалось сохранить картинку в WebP'];
    }
    @chmod($full, 0644);
    @chmod($thumb, 0644);

    return ['ok' => true, 'name' => $base . '.webp', 'base' => $base];
}

/**
 * Поднять лимит памяти под конкретный снимок.
 * Сырые пиксели — 4 байта на точку, плюс место под уменьшенную копию и запас
 * на сам PHP. Возвращает false, если хостинг столько не отдал.
 */
function ensure_memory_for(int $w, int $h): bool
{
    $need = $w * $h * 4 + 28 * 1048576; // исходник + копия 1600px + запас
    $cur  = memory_limit_bytes();
    if ($cur < 0 || $cur >= $need) {
        return true; // без ограничения либо и так хватает
    }
    // Просим с запасом, но не больше разумного потолка. max() обязателен:
    // если текущий лимит и так выше потолка, min() опустил бы его — и мы бы
    // своими руками сделали хуже на весь оставшийся запрос.
    $ask = max($cur, min((int)($need * 1.15), 512 * 1048576));
    @ini_set('memory_limit', $ask);
    $now = memory_limit_bytes();
    return $now < 0 || $now >= $need;
}

/** Текущий memory_limit в байтах. -1 означает «без ограничения». */
function memory_limit_bytes(): int
{
    $v = trim((string)ini_get('memory_limit'));
    if ($v === '' || $v === '-1') {
        return -1;
    }
    $n = (int)$v;
    return match (strtolower(substr($v, -1))) {
        'g' => $n * 1073741824,
        'm' => $n * 1048576,
        'k' => $n * 1024,
        default => $n,
    };
}

/**
 * Пометка поворота из EXIF (1–8), без расширения exif — его на хостинге нет.
 * Разбираем начало JPEG руками: APP1 → TIFF → IFD0 → тег 0x0112.
 */
function exif_orientation(string $path, string $mime): int
{
    if ($mime !== 'image/jpeg') {
        return 1;
    }
    $fh = @fopen($path, 'rb');
    if (!$fh) {
        return 1;
    }
    try {
        if (fread($fh, 2) !== "\xFF\xD8") {
            return 1; // не JPEG
        }
        // Идём по сегментам до APP1, дальше начала кадра искать бессмысленно
        while (!feof($fh)) {
            $marker = fread($fh, 2);
            if (strlen($marker) < 2 || $marker[0] !== "\xFF") {
                return 1;
            }
            $type = ord($marker[1]);
            if ($type === 0xDA || $type === 0xD9) {
                return 1; // начались данные картинки
            }
            $lenRaw = fread($fh, 2);
            if (strlen($lenRaw) < 2) {
                return 1;
            }
            $len = unpack('n', $lenRaw)[1] - 2;
            if ($len < 0) {
                return 1;
            }
            if ($type !== 0xE1) {
                fseek($fh, $len, SEEK_CUR);
                continue;
            }
            $seg = fread($fh, min($len, 65536));
            if (substr($seg, 0, 6) !== "Exif\x00\x00") {
                // Это APP1, но не Exif — обычно XMP, его пишут редакторы
                // и облачные галереи, и он часто идёт ПЕРЕД Exif. Раньше здесь
                // стоял выход, из-за чего поворот у таких файлов терялся.
                fseek($fh, $len - strlen($seg), SEEK_CUR);
                continue;
            }
            $tiff = substr($seg, 6);
            // Заголовок TIFF — 8 байт: порядок, магия 42, смещение IFD0.
            // На обрезанном файле без этой проверки unpack() вернёт false,
            // а [1] — null, и следующий substr() падает с TypeError.
            if (strlen($tiff) < 8) {
                return 1;
            }
            $le = substr($tiff, 0, 2) === 'II';       // порядок байтов
            if (unpack($le ? 'v' : 'n', substr($tiff, 2, 2))[1] !== 42) {
                return 1;
            }
            $ifd = unpack($le ? 'V' : 'N', substr($tiff, 4, 4))[1];
            if ($ifd < 8 || $ifd + 2 > strlen($tiff)) {
                return 1;
            }
            $count = unpack($le ? 'v' : 'n', substr($tiff, $ifd, 2))[1];
            for ($i = 0; $i < $count; $i++) {
                $entry = substr($tiff, $ifd + 2 + $i * 12, 12);
                if (strlen($entry) < 12) {
                    return 1;
                }
                if (unpack($le ? 'v' : 'n', substr($entry, 0, 2))[1] !== 0x0112) {
                    continue;
                }
                // Обычно SHORT (тип 3), но встречается и LONG (тип 4).
                // У SHORT значение лежит в первых двух байтах поля, у LONG —
                // во всех четырёх.
                $fieldType = unpack($le ? 'v' : 'n', substr($entry, 2, 2))[1];
                $o = match ($fieldType) {
                    3 => unpack($le ? 'v' : 'n', substr($entry, 8, 2))[1],
                    4 => unpack($le ? 'V' : 'N', substr($entry, 8, 4))[1],
                    default => 1,
                };
                return ($o >= 1 && $o <= 8) ? $o : 1;
            }
            return 1;
        }
    } finally {
        fclose($fh);
    }
    return 1;
}

/** Развернуть картинку по пометке EXIF. Оригинал освобождаем сами. */
function apply_orientation(\GdImage $im, int $orientation): \GdImage
{
    if ($orientation <= 1 || $orientation > 8) {
        return $im;
    }
    // imagerotate крутит против часовой стрелки, отсюда минусы.
    // Ориентация 4 — это зеркало по вертикали БЕЗ поворота. Если добавить
    // сюда ещё и 180°, получится зеркало по горизонтали, то есть кадр
    // вверх ногами.
    $angle = match ($orientation) {
        3 => 180,
        5, 6 => -90,
        7, 8 => 90,
        default => 0,
    };
    $out = $im;
    if ($angle !== 0) {
        $rot = @imagerotate($im, $angle, 0);
        if ($rot) {
            imagedestroy($im);
            $out = $rot;
        }
    }
    if (in_array($orientation, [2, 5, 7], true)) {
        @imageflip($out, IMG_FLIP_HORIZONTAL);
    } elseif ($orientation === 4) {
        @imageflip($out, IMG_FLIP_VERTICAL);
    }
    return $out;
}

/** Уменьшить картинку по большей стороне, сохранив пропорции. */
function scale_to(\GdImage $src, int $max): \GdImage
{
    $w = imagesx($src);
    $h = imagesy($src);
    if ($w <= $max && $h <= $max) {
        return $src;
    }
    $ratio = $w >= $h ? $max / $w : $max / $h;
    $nw = max(1, (int)round($w * $ratio));
    $nh = max(1, (int)round($h * $ratio));
    $dst = imagecreatetruecolor($nw, $nh);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    return $dst;
}

/** Удалить загруженную картинку вместе с миниатюрой. */
function delete_upload(string $name): bool
{
    // Никаких путей — только имя файла из нашей папки
    $name = basename($name);
    // \z, а не $: иначе имя с переводом строки на конце проходит проверку
    if (!preg_match('~^[A-Za-z0-9._-]+\.webp\z~', $name)) {
        return false;
    }
    $full = UPLOAD_DIR . '/' . $name;
    $thumb = str_ends_with($name, '-thumb.webp')
        ? $full
        : UPLOAD_DIR . '/' . preg_replace('~\.webp\z~', '-thumb.webp', $name);
    $done = false;
    if (is_file($full)) {
        $done = @unlink($full);
    }
    if (is_file($thumb)) {
        @unlink($thumb);
    }
    return $done;
}

/** Все картинки, доступные для выбора: и штатные, и загруженные. */
function available_images(string $kind = 'gallery'): array
{
    $out = [];
    if ($kind === 'services') {
        foreach (glob(ROOT . '/assets/img/services/*.webp') ?: [] as $f) {
            $out[] = ['name' => basename($f, '.webp'), 'src' => 'assets/img/services/' . basename($f), 'own' => false];
        }
    } else {
        foreach (glob(ROOT . '/assets/img/gallery/*.webp') ?: [] as $f) {
            $out[] = ['name' => basename($f, '.webp'), 'src' => 'assets/img/gallery/' . basename($f), 'own' => false];
        }
    }
    foreach (glob(UPLOAD_DIR . '/*.webp') ?: [] as $f) {
        $b = basename($f);
        if (str_ends_with($b, '-thumb.webp')) {
            continue;
        }
        $out[] = ['name' => $b, 'src' => 'assets/img/uploads/' . $b, 'own' => true];
    }
    return $out;
}
