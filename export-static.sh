#!/bin/bash
# Снимает статический слепок PHP-сайта в папку docs/.
#
# Зачем. На GitHub Pages PHP не выполняется, там может жить только статика.
# Поэтому мы поднимаем сайт локально, обходим все страницы и сохраняем HTML.
# Шаблон при этом один — тот же, что на боевом хостинге, так что версии
# не разъезжаются.
#
# Запуск:  ./export-static.sh
# Результат: docs/ — готово к публикации на Pages.

set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
OUT="$ROOT/docs"
PORT=8765
# Адрес, который попадёт в canonical, sitemap и Open Graph статической версии
SITE_URL="${SITE_URL:-https://prideman2022.github.io/nlevel-detailing}"

cd "$ROOT"

command -v php >/dev/null || { echo "✗ Нужен PHP"; exit 1; }

echo "▸ Поднимаю сайт на порту $PORT"
php -S "localhost:$PORT" index.php >/tmp/nlevel-export.log 2>&1 &
SRV=$!
trap 'kill $SRV 2>/dev/null || true' EXIT
sleep 2

if ! curl -sf -o /dev/null "http://localhost:$PORT/"; then
  echo "✗ Сайт не поднялся. Лог: /tmp/nlevel-export.log"
  exit 1
fi

echo "▸ Чищу docs/"
rm -rf "$OUT"
mkdir -p "$OUT"

# Список страниц берём из самого контента — новые страницы попадут автоматически
PAGES=$(php -r '
require "inc/config.php"; require "inc/data.php";
foreach (all_pages() as $p) {
    $s = $p["slug"] ?? "";
    echo ($s === "index" ? "/" : "/$s/"), "\n";
}')

save() { # $1 — адрес, $2 — файл назначения
  mkdir -p "$(dirname "$2")"
  if curl -sf "http://localhost:$PORT$1" -o "$2"; then
    printf '  ✓ %-28s %s\n' "$1" "$(du -h "$2" | cut -f1)"
  else
    printf '  ✗ %s\n' "$1"
  fi
}

echo "▸ Сохраняю страницы"
while read -r p; do
  [ -z "$p" ] && continue
  if [ "$p" = "/" ]; then save "/" "$OUT/index.html"; else save "$p" "$OUT${p}index.html"; fi
done <<< "$PAGES"

echo "▸ Служебные файлы"
save "/sitemap.xml" "$OUT/sitemap.xml"
save "/robots.txt" "$OUT/robots.txt"
save "/llms.txt" "$OUT/llms.txt"
save "/llms-full.txt" "$OUT/llms-full.txt"
save "/manifest.webmanifest" "$OUT/manifest.webmanifest"
# Страница 404 отдаётся с кодом 404, поэтому -f здесь не годится
curl -s "http://localhost:$PORT/nesushchestvuyushchaya-stranica/" -o "$OUT/404.html" \
  && printf '  ✓ %-28s %s\n' "/404.html" "$(du -h "$OUT/404.html" | cut -f1)"

echo "▸ Копирую ассеты и приложение"
rsync -a --exclude '.htaccess' "$ROOT/assets/" "$OUT/assets/"
rsync -a "$ROOT/app/" "$OUT/app/"
touch "$OUT/.nojekyll"

# В статике адреса должны вести на боевой домен Pages, а не на localhost
echo "▸ Правлю адреса на $SITE_URL"
find "$OUT" -type f \( -name '*.html' -o -name '*.xml' -o -name '*.txt' -o -name '*.webmanifest' \) \
  -exec sed -i '' "s|http://localhost:$PORT|$SITE_URL|g" {} +

# На Pages сайт живёт в подпапке, поэтому ссылки от корня домена нужно
# дополнить её именем — иначе они уведут на чужой корень.
SUBPATH="/$(basename "$SITE_URL")"
if [ "$SUBPATH" != "/" ]; then
  echo "▸ Добавляю подпапку $SUBPATH к внутренним ссылкам"
  OUT="$OUT" SUBPATH="$SUBPATH" python3 - <<'PYEOF'
import os, re
out = os.environ['OUT']; sub = os.environ['SUBPATH'].rstrip('/')
pat = re.compile(r'(href|src)="/(?!/)')
n = 0
for dp, _, fn in os.walk(out):
    for f in fn:
        if not f.endswith('.html'):
            continue
        p = os.path.join(dp, f)
        s = open(p, encoding='utf-8').read()
        s2 = pat.sub(lambda m: f'{m.group(1)}="{sub}/', s)
        # на случай повторного запуска — убираем задвоение
        s2 = s2.replace(f'{sub}{sub}/', f'{sub}/')
        if s2 != s:
            open(p, 'w', encoding='utf-8').write(s2); n += 1
print(f'  ✓ поправлено файлов: {n}')
PYEOF
fi

echo
echo "▸ Готово: $(find "$OUT" -name '*.html' | wc -l | tr -d ' ') страниц, $(du -sh "$OUT" | cut -f1)"
echo "  Публикация: git add docs && git commit && git push"
