<?php
/** Обработка форм админки. Возвращает [сообщение, ошибка]. */
declare(strict_types=1);

require_once ROOT . '/inc/sanitize.php';

/** Разбить textarea на массив непустых строк. */
function lines(string $key): array
{
    $raw = (string)($_POST[$key] ?? '');
    $out = [];
    foreach (preg_split('~\r\n|\r|\n~', $raw) as $l) {
        $l = trim($l);
        if ($l !== '') {
            $out[] = $l;
        }
    }
    return $out;
}

function post_s(string $key, string $default = ''): string
{
    return trim((string)($_POST[$key] ?? $default));
}

function post_i(string $key, int $default = 0): int
{
    return (int)($_POST[$key] ?? $default);
}

function post_f(string $key, float $default = 0): float
{
    return (float)str_replace(',', '.', (string)($_POST[$key] ?? $default));
}

function handle_action(): array
{
    $action = post_s('_action', 'save');
    $section = post_s('_section', 'main');
    $c = content();

    try {
        switch ($action) {
            /* ---------- Главная ---------- */
            case 'save':
                switch ($section) {
                    case 'main':
                        $c['heroSub'] = post_s('heroSub');
                        $c['servicesLede'] = post_s('servicesLede');
                        $c['promise'] = [
                            'eyebrow' => post_s('promise_eyebrow'),
                            'title'   => post_s('promise_title'),
                            'text'    => post_s('promise_text'),
                            'points'  => lines('promise_points'),
                        ];
                        $c['comparison'] = [
                            'title' => post_s('cmp_title'),
                            'lede'  => post_s('cmp_lede'),
                            'note'  => post_s('cmp_note'),
                            'rows'  => [],
                        ];
                        foreach ((array)($_POST['cmp_name'] ?? []) as $i => $n) {
                            $n = trim((string)$n);
                            if ($n === '') {
                                continue;
                            }
                            $c['comparison']['rows'][] = [
                                'name'   => $n,
                                'our'    => trim((string)($_POST['cmp_our'][$i] ?? '')),
                                'market' => trim((string)($_POST['cmp_market'][$i] ?? '')),
                            ];
                        }
                        $c['about'] = [
                            'title'      => post_s('about_title'),
                            // Здесь разрешена разметка, поэтому чистим от лишнего
                            'paragraphs' => clean_html_lines(lines('about_paragraphs')),
                            'stepsTitle' => post_s('about_steps_title'),
                            'steps'      => clean_html_lines(lines('about_steps')),
                        ];
                        // Карточки услуг на главной
                        $cards = [];
                        foreach ((array)($_POST['card_title'] ?? []) as $i => $t) {
                            $t = trim((string)$t);
                            if ($t === '') {
                                continue;
                            }
                            $old = $c['homeCards'][$i] ?? [];
                            $cards[] = [
                                'group'      => trim((string)($_POST['card_group'][$i] ?? '')),
                                'url'        => trim((string)($_POST['card_url'][$i] ?? '')),
                                'title'      => $t,
                                'text'       => trim((string)($_POST['card_text'][$i] ?? '')),
                                'serviceImg' => $old['serviceImg'] ?? null,
                                'galleryImg' => $old['galleryImg'] ?? null,
                            ];
                        }
                        $c['homeCards'] = $cards;
                        $c['heroPhotos'] = lines('heroPhotos');
                        break;

                    /* ---------- Услуги и прайс ---------- */
                    case 'services':
                        $groups = [];
                        foreach ((array)($_POST['g_id'] ?? []) as $gi => $gid) {
                            $gid = trim((string)$gid);
                            if ($gid === '') {
                                continue;
                            }
                            $items = [];
                            foreach ((array)($_POST['i_name'][$gi] ?? []) as $ii => $nm) {
                                $nm = trim((string)$nm);
                                if ($nm === '') {
                                    continue;
                                }
                                $items[] = [
                                    'name'    => $nm,
                                    'price'   => (int)($_POST['i_price'][$gi][$ii] ?? 0),
                                    'desc'    => trim((string)($_POST['i_desc'][$gi][$ii] ?? '')),
                                    'time'    => trim((string)($_POST['i_time'][$gi][$ii] ?? '')),
                                    'img'     => trim((string)($_POST['i_img'][$gi][$ii] ?? '')) ?: null,
                                    'popular' => !empty($_POST['i_popular'][$gi][$ii]),
                                    'best'    => !empty($_POST['i_best'][$gi][$ii]),
                                ];
                            }
                            $groups[] = [
                                'id'    => $gid,
                                'title' => trim((string)($_POST['g_title'][$gi] ?? '')),
                                'note'  => trim((string)($_POST['g_note'][$gi] ?? '')),
                                'items' => $items,
                            ];
                        }
                        $c['priceGroups'] = $groups;
                        $c['priceLede'] = post_s('priceLede');
                        $c['priceNotes'] = clean_html_lines(lines('priceNotes'));
                        break;

                    /* ---------- Тексты услуговых страниц ---------- */
                    case 'texts':
                        $slug = post_s('slug');
                        if ($slug === '' || !isset($c['serviceContent'][$slug])) {
                            return [null, 'Неизвестная страница'];
                        }
                        $blocks = [];
                        foreach ((array)($_POST['b_h'] ?? []) as $i => $h) {
                            $h = trim((string)$h);
                            if ($h === '') {
                                continue;
                            }
                            $bl = ['h' => $h];
                            $p = trim((string)($_POST['b_p'][$i] ?? ''));
                            if ($p !== '') {
                                $bl['p'] = $p;
                            }
                            $li = [];
                            foreach (preg_split('~\r\n|\r|\n~', (string)($_POST['b_list'][$i] ?? '')) as $l) {
                                $l = trim($l);
                                if ($l !== '') {
                                    $li[] = $l;
                                }
                            }
                            if ($li) {
                                $bl['list'] = $li;
                            }
                            $af = trim((string)($_POST['b_after'][$i] ?? ''));
                            if ($af !== '') {
                                $bl['after'] = $af;
                            }
                            $blocks[] = $bl;
                        }
                        $c['serviceContent'][$slug] = ['intro' => post_s('intro'), 'blocks' => $blocks];
                        break;

                    /* ---------- Фотографии ---------- */
                    case 'gallery':
                        $gal = [];
                        foreach ((array)($_POST['ph_f'] ?? []) as $i => $f) {
                            $f = trim((string)$f);
                            if ($f === '') {
                                continue;
                            }
                            $gal[] = [
                                'f'   => $f,
                                'alt' => trim((string)($_POST['ph_alt'][$i] ?? '')),
                                'cat' => trim((string)($_POST['ph_cat'][$i] ?? '')),
                            ];
                        }
                        $c['gallery'] = $gal;
                        $c['worksLede'] = post_s('worksLede');
                        break;

                    /* ---------- Тексты страницы отзывов ---------- */
                    case 'reviews':
                        $c['reviewsLede'] = post_s('reviewsLede');
                        $c['reviewsHonesty'] = [
                            'title'      => post_s('honesty_title'),
                            'paragraphs' => clean_html_lines(lines('honesty_paragraphs')),
                        ];
                        break;

                    /* ---------- Вопросы и преимущества ---------- */
                    case 'faq':
                        $faq = [];
                        foreach ((array)($_POST['q'] ?? []) as $i => $q) {
                            $q = trim((string)$q);
                            if ($q === '') {
                                continue;
                            }
                            $faq[] = ['q' => $q, 'a' => trim((string)($_POST['a'][$i] ?? ''))];
                        }
                        $c['faq'] = $faq;

                        $adv = [];
                        foreach ((array)($_POST['adv_title'] ?? []) as $i => $t) {
                            $t = trim((string)$t);
                            if ($t === '') {
                                continue;
                            }
                            $adv[] = [
                                'icon'  => trim((string)($_POST['adv_icon'][$i] ?? 'shield')),
                                'title' => $t,
                                'text'  => trim((string)($_POST['adv_text'][$i] ?? '')),
                            ];
                        }
                        $c['advantages'] = $adv;
                        break;

                    /* ---------- Контакты ---------- */
                    case 'contacts':
                        foreach ([
                            'name', 'city', 'district', 'street', 'addressNote', 'region',
                            'phone', 'phoneRaw', 'whatsapp', 'whatsappUrl', 'telegram', 'telegramUrl',
                            'vkUrl', 'appUrl', 'appMode', 'hours', 'yandexMapUrl', 'yandexRouteUrl',
                            'gis2Url', 'gis2ReviewsUrl', 'zoonUrl',
                        ] as $k) {
                            if (isset($_POST[$k])) {
                                $c['biz'][$k] = post_s($k);
                            }
                        }
                        $c['biz']['lat'] = post_f('lat');
                        $c['biz']['lon'] = post_f('lon');
                        $c['biz']['ratingYandex'] = post_f('ratingYandex');
                        $c['biz']['reviewsYandex'] = post_i('reviewsYandex');
                        $c['biz']['rating2gis'] = post_f('rating2gis');
                        $c['biz']['reviews2gis'] = post_i('reviews2gis');
                        $c['route'] = [
                            'title'      => post_s('route_title'),
                            'paragraphs' => clean_html_lines(lines('route_paragraphs')),
                            'payment'    => post_s('route_payment'),
                            'schedule'   => post_s('route_schedule'),
                        ];
                        break;

                    /* ---------- SEO ---------- */
                    case 'seo':
                        $c['site']['url'] = rtrim(post_s('site_url'), '/');
                        $c['biz']['yandexVerification'] = post_s('yandexVerification');
                        $c['biz']['googleVerification'] = post_s('googleVerification');
                        foreach ((array)($_POST['p_slug'] ?? []) as $i => $slug) {
                            $slug = trim((string)$slug);
                            foreach ($c['pages'] as $pi => $p) {
                                if (($p['slug'] ?? '') !== $slug) {
                                    continue;
                                }
                                $c['pages'][$pi]['title'] = trim((string)($_POST['p_title'][$i] ?? ''));
                                $c['pages'][$pi]['description'] = trim((string)($_POST['p_desc'][$i] ?? ''));
                                $c['pages'][$pi]['keywords'] = trim((string)($_POST['p_keys'][$i] ?? ''));
                                $c['pages'][$pi]['h1'] = trim((string)($_POST['p_h1'][$i] ?? ''));
                            }
                        }
                        break;

                    /* ---------- Документы и реквизиты ---------- */
                    case 'legal':
                        $c['legal']['operator'] = [
                            'name'      => post_s('op_name'),
                            'shortName' => post_s('op_shortName'),
                            'inn'       => post_s('op_inn'),
                            'ogrn'      => post_s('op_ogrn'),
                            'status'    => post_s('op_status'),
                            'address'   => post_s('op_address'),
                            'email'     => post_s('op_email'),
                            'updated'   => post_s('op_updated') ?: date('Y-m-d'),
                        ];
                        $c['legal']['banner'] = [
                            'enabled' => !empty($_POST['bn_enabled']),
                            'title'   => post_s('bn_title'),
                            'text'    => post_s('bn_text'),
                            'accept'  => post_s('bn_accept'),
                            'decline' => post_s('bn_decline'),
                            'more'    => post_s('bn_more'),
                        ];
                        // В счётчике Метрики бывают только цифры
                        $c['legal']['metrika'] = preg_replace('~\D~', '', post_s('metrika')) ?: '';
                        break;

                    /* ---------- robots.txt и карта сайта ---------- */
                    case 'files':
                        $c['seoFiles'] = [
                            'robots'    => (string)($_POST['robots'] ?? ''),
                            'extraUrls' => lines('extraUrls'),
                        ];
                        foreach ((array)($_POST['sm_slug'] ?? []) as $i => $slug) {
                            $slug = trim((string)$slug);
                            foreach ($c['pages'] as $pi => $pg) {
                                if (($pg['slug'] ?? '') !== $slug) {
                                    continue;
                                }
                                $pr = (string)($_POST['sm_priority'][$i] ?? '0.7');
                                $fr = (string)($_POST['sm_freq'][$i] ?? 'monthly');
                                $c['pages'][$pi]['priority'] = in_array($pr, ['1.0','0.9','0.8','0.7','0.5','0.3'], true) ? $pr : '0.7';
                                $c['pages'][$pi]['changefreq'] = in_array($fr, ['daily','weekly','monthly','yearly'], true) ? $fr : 'monthly';
                                // Галочка снята — страница выпадает из карты
                                if (empty($_POST['sm_in'][$i])) {
                                    $c['pages'][$pi]['noSitemap'] = true;
                                } else {
                                    unset($c['pages'][$pi]['noSitemap']);
                                }
                            }
                        }
                        break;

                    default:
                        return [null, 'Неизвестный раздел'];
                }
                return save_content($c)
                    ? ['Сохранено', null]
                    : [null, 'Не удалось записать файл. Проверьте права на папку data/'];

            /* ---------- Загрузка фото в галерею ---------- */
            case 'upload_gallery':
                $res = handle_upload($_FILES['photo'] ?? [], 'gal');
                if (!$res['ok']) {
                    return [null, $res['error']];
                }
                $c['gallery'][] = [
                    'f'   => $res['name'],
                    'alt' => post_s('alt') ?: 'Фотография работы детейлинг-студии NLeveL, Екатеринбург',
                    'cat' => post_s('cat') ?: 'Студия',
                ];
                return save_content($c) ? ['Фото добавлено', null] : [null, 'Фото сохранено, но список не обновился'];

            /* ---------- Замена картинки услуги ---------- */
            case 'upload_service':
                $res = handle_upload($_FILES['photo'] ?? [], 'svc');
                if (!$res['ok']) {
                    return [null, $res['error']];
                }
                $slug = post_s('slug');
                foreach ($c['pages'] as $i => $p) {
                    if (($p['slug'] ?? '') === $slug) {
                        $c['pages'][$i]['hero'] = $res['name'];
                    }
                }
                return save_content($c) ? ['Картинка страницы обновлена', null] : [null, 'Не удалось сохранить'];

            /* ---------- Удаление ---------- */
            case 'del_photo':
                $i = post_i('idx', -1);
                if (!isset($c['gallery'][$i])) {
                    return [null, 'Фото не найдено'];
                }
                $name = $c['gallery'][$i]['f'] ?? '';
                array_splice($c['gallery'], $i, 1);
                if (str_ends_with($name, '.webp')) {
                    delete_upload($name);
                }
                return save_content($c) ? ['Фото удалено', null] : [null, 'Не удалось сохранить'];

            case 'del_faq':
                $i = post_i('idx', -1);
                if (!isset($c['faq'][$i])) {
                    return [null, 'Вопрос не найден'];
                }
                array_splice($c['faq'], $i, 1);
                return save_content($c) ? ['Вопрос удалён', null] : [null, 'Не удалось сохранить'];

            case 'del_adv':
                $i = post_i('idx', -1);
                if (!isset($c['advantages'][$i])) {
                    return [null, 'Блок не найден'];
                }
                array_splice($c['advantages'], $i, 1);
                return save_content($c) ? ['Блок удалён', null] : [null, 'Не удалось сохранить'];

            case 'del_item':
                $g = post_i('gidx', -1);
                $i = post_i('idx', -1);
                if (!isset($c['priceGroups'][$g]['items'][$i])) {
                    return [null, 'Услуга не найдена'];
                }
                array_splice($c['priceGroups'][$g]['items'], $i, 1);
                return save_content($c) ? ['Услуга удалена', null] : [null, 'Не удалось сохранить'];

            case 'del_group':
                $g = post_i('idx', -1);
                if (!isset($c['priceGroups'][$g])) {
                    return [null, 'Раздел не найден'];
                }
                array_splice($c['priceGroups'], $g, 1);
                return save_content($c) ? ['Раздел удалён', null] : [null, 'Не удалось сохранить'];

            /* ---------- Добавление ---------- */
            case 'add_item':
                $g = post_i('gidx', -1);
                if (!isset($c['priceGroups'][$g])) {
                    return [null, 'Раздел не найден'];
                }
                $c['priceGroups'][$g]['items'][] = [
                    'name' => 'Новая услуга', 'price' => 0, 'desc' => '', 'time' => '', 'img' => null,
                ];
                return save_content($c) ? ['Услуга добавлена — заполните её', null] : [null, 'Не удалось сохранить'];

            case 'add_group':
                $c['priceGroups'][] = [
                    'id' => 'group-' . substr(bin2hex(random_bytes(3)), 0, 5),
                    'title' => 'Новый раздел', 'note' => '', 'items' => [],
                ];
                return save_content($c) ? ['Раздел добавлен', null] : [null, 'Не удалось сохранить'];

            case 'add_faq':
                $c['faq'][] = ['q' => 'Новый вопрос', 'a' => ''];
                return save_content($c) ? ['Вопрос добавлен', null] : [null, 'Не удалось сохранить'];

            case 'add_adv':
                $c['advantages'][] = ['icon' => 'shield', 'title' => 'Новое преимущество', 'text' => ''];
                return save_content($c) ? ['Блок добавлен', null] : [null, 'Не удалось сохранить'];

            /* ---------- Текст документа ---------- */
            case 'save_doc':
                $slug = post_s('slug');
                $found = false;
                foreach ($c['legal']['docs'] as $di => $d) {
                    if (($d['slug'] ?? '') !== $slug) {
                        continue;
                    }
                    $found = true;
                    $secs = [];
                    foreach ((array)($_POST['s_h'] ?? []) as $i => $h) {
                        $h = trim((string)$h);
                        if ($h === '') {
                            continue;
                        }
                        $sec = ['h' => $h];
                        $ps = [];
                        foreach (preg_split('~\r\n|\r|\n~', (string)($_POST['s_p'][$i] ?? '')) as $l) {
                            $l = trim($l);
                            if ($l !== '') {
                                $ps[] = $l;
                            }
                        }
                        if ($ps) {
                            $sec['p'] = $ps;
                        }
                        $li = [];
                        foreach (preg_split('~\r\n|\r|\n~', (string)($_POST['s_list'][$i] ?? '')) as $l) {
                            $l = trim($l);
                            if ($l !== '') {
                                $li[] = $l;
                            }
                        }
                        if ($li) {
                            $sec['list'] = $li;
                        }
                        $af = trim((string)($_POST['s_after'][$i] ?? ''));
                        if ($af !== '') {
                            $sec['after'] = $af;
                        }
                        $secs[] = $sec;
                    }
                    $c['legal']['docs'][$di]['title'] = post_s('doc_title');
                    $c['legal']['docs'][$di]['nav'] = post_s('doc_nav');
                    $c['legal']['docs'][$di]['lede'] = post_s('doc_lede');
                    $c['legal']['docs'][$di]['sections'] = $secs;
                    // Название страницы в общем списке держим в согласии с документом
                    foreach ($c['pages'] as $pi => $pg) {
                        if (($pg['slug'] ?? '') === $slug) {
                            $c['pages'][$pi]['nav'] = post_s('doc_nav');
                            $c['pages'][$pi]['h1'] = post_s('doc_title');
                        }
                    }
                }
                if (!$found) {
                    return [null, 'Документ не найден'];
                }
                return save_content($c) ? ['Документ сохранён', null] : [null, 'Не удалось сохранить'];

            /* ---------- Резервные копии ---------- */
            case 'restore':
                $f = basename(post_s('file'));
                if (!preg_match('~^content_[\d_-]+\.json$~', $f)) {
                    return [null, 'Некорректное имя копии'];
                }
                $path = BACKUP_DIR . '/' . $f;
                if (!is_file($path)) {
                    return [null, 'Копия не найдена'];
                }
                $restored = json_decode((string)file_get_contents($path), true);
                if (!is_array($restored)) {
                    return [null, 'Копия повреждена'];
                }
                return save_content($restored) ? ['Восстановлено из копии от ' . $f, null] : [null, 'Не удалось восстановить'];

            /* ---------- Пароль ---------- */
            case 'password':
                $cur = (string)($_POST['current'] ?? '');
                $p1 = (string)($_POST['password'] ?? '');
                $p2 = (string)($_POST['password2'] ?? '');
                $acc = admin_account();
                if (!password_verify($cur, (string)($acc['hash'] ?? ''))) {
                    return [null, 'Текущий пароль указан неверно'];
                }
                if (mb_strlen($p1) < 8) {
                    return [null, 'Новый пароль — минимум 8 символов'];
                }
                if ($p1 !== $p2) {
                    return [null, 'Новые пароли не совпадают'];
                }
                return change_password($p1) ? ['Пароль изменён', null] : [null, 'Не удалось сохранить пароль'];

            default:
                return [null, 'Неизвестное действие'];
        }
    } catch (\Throwable $ex) {
        return [null, 'Ошибка: ' . $ex->getMessage()];
    }
}
