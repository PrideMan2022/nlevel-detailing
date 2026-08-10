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

    $ok = imagewebp(scale_to($src, 1600), $full, 82)
        && imagewebp(scale_to($src, 600), $thumb, 74);
    imagedestroy($src);

    if (!$ok) {
        return ['ok' => false, 'error' => 'Не удалось сохранить картинку'];
    }
    @chmod($full, 0644);
    @chmod($thumb, 0644);

    return ['ok' => true, 'name' => $base . '.webp', 'base' => $base];
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
    if (!preg_match('~^[A-Za-z0-9._-]+\.webp$~', $name)) {
        return false;
    }
    $full = UPLOAD_DIR . '/' . $name;
    $thumb = UPLOAD_DIR . '/' . preg_replace('~\.webp$~', '-thumb.webp', $name);
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
