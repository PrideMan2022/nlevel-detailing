#!/bin/bash
# Обновляет копию приложения онлайн-записи в папке app/.
#
# Зачем копия. Приложение на Flutter + Firebase не поднимается в кросс-доменном
# фрейме: браузер изолирует хранилище стороннего домена, и Flutter падает
# до отрисовки. С того же домена всё работает — поэтому сборка лежит рядом.
#
# Запускать после каждого пересобранного билда приложения:
#   ./sync-app.sh && node build/build.js && git add -A && git commit && git push

set -euo pipefail

SRC="${1:-/Users/mybook/Desktop/Проекты/NLeveL/detailing_app/build/web}"
DST="$(cd "$(dirname "$0")" && pwd)/app"

if [ ! -f "$SRC/index.html" ]; then
  echo "✗ Не нашёл сборку приложения: $SRC"
  echo "  Соберите её (flutter build web) или укажите путь: ./sync-app.sh /путь/к/build/web"
  exit 1
fi

echo "▸ Копирую из $SRC"
rm -rf "$DST"
mkdir -p "$DST"

# downloads/ не берём: там установочные APK на 156 МБ, в репозитории им не место.
# Их раздаёт Firebase Hosting по адресу nlevel-detailing.web.app/downloads/
#
# canvaskit/ тоже не берём — 37 МБ мёртвого груза. Проверено по сетевым запросам:
# приложение тянет движок с CDN Google (www.gstatic.com/flutter-canvaskit/...),
# а локальную папку не открывает ни разу. Если однажды решите хостить движок
# у себя (для России так надёжнее), уберите canvaskit из исключений и пропишите
# canvasKitBaseUrl в index.html приложения.
rsync -a --exclude 'downloads/' --exclude 'canvaskit/' "$SRC/" "$DST/"

# Приложение не должно попадать в поиск как отдельная страница —
# это интерфейс, а не контент, и он дублировал бы страницу записи
if ! grep -q 'name="robots"' "$DST/index.html"; then
  sed -i '' 's|<meta charset="UTF-8">|<meta charset="UTF-8">\
  <meta name="robots" content="noindex, nofollow">|' "$DST/index.html"
  echo "▸ приложению проставлен noindex"
fi

# Сайт лежит в подпапке, поэтому корневой base ломает загрузку ресурсов
if grep -q '<base href="/">' "$DST/index.html"; then
  sed -i '' 's|<base href="/">|<base href="./">|' "$DST/index.html"
  echo "▸ base href → ./"
fi

echo "▸ Готово: $(du -sh "$DST" | cut -f1), файлов: $(find "$DST" -type f | wc -l | tr -d ' ')"
echo "▸ Дальше: node build/build.js"
