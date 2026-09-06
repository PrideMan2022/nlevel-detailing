#!/bin/bash
# Заливка PHP-версии с админкой на хостинг Руцентра по FTP.
#
# Пароль скрипт не спрашивает и нигде не хранит: его берёт curl из ~/.netrc.
# Перед первым запуском добавьте туда строку (файл лежит вне репозитория):
#
#   machine ftp.ЛОГИН.nichost.ru login ИМЯ_FTP_ПОЛЬЗОВАТЕЛЯ password ПАРОЛЬ
#
# и закройте его от чужих глаз:  chmod 600 ~/.netrc
#
# Хост и корень сайта — в deploy/ftp.env (тоже вне репозитория, см. .gitignore):
#
#   FTP_HOST=ftp.ЛОГИН.nichost.ru
#   FTP_ROOT=/nlevel.pro/docs
#
# Запуск:
#   ./deploy/upload-php.sh            — показать, что будет сделано, ничего не менять
#   ./deploy/upload-php.sh --go       — залить
#   ./deploy/upload-php.sh --go --clean-static  — залить и снести статику, которая
#                                        затеняет маршруты PHP
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$ROOT/deploy/ftp.env"

if [ ! -f "$ENV_FILE" ]; then
  echo "Нет $ENV_FILE. Создайте его с двумя строками:"
  echo "  FTP_HOST=ftp.ЛОГИН.nichost.ru"
  echo "  FTP_ROOT=/nlevel.pro/docs"
  exit 1
fi
# shellcheck disable=SC1090
. "$ENV_FILE"
: "${FTP_HOST:?не задан FTP_HOST в deploy/ftp.env}"
# FTP-пользователь заперт в корне сайта, поэтому путь обычно пустой.
# Хвостовой слэш убираем: иначе в адресе получится двойной.
FTP_ROOT="${FTP_ROOT-}"
FTP_ROOT="${FTP_ROOT%/}"

GO=0
CLEAN=0
for a in "$@"; do
  case "$a" in
    --go) GO=1 ;;
    --clean-static) CLEAN=1 ;;
    *) echo "Неизвестный аргумент: $a"; exit 1 ;;
  esac
done

STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT

# Что едет на сервер. Исключаем всё, что нужно только для разработки,
# и всё, что должно жить только на сервере (пароль админа, загруженные фото,
# резервные копии — их перезапись затёрла бы боевые данные).
rsync -a \
  --exclude='.git/' --exclude='.github/' --exclude='docs/' --exclude='build-*/' \
  --exclude='research/' --exclude='tools/' --exclude='deploy/' \
  --exclude='.DS_Store' --exclude='node_modules/' \
  --exclude='*.md' --exclude='*.sh' --exclude='.gitignore' --exclude='.gitattributes' \
  --exclude='data/admin.php' --exclude='data/backups/' --exclude='data/.login_attempts' \
  --exclude='assets/img/uploads/' \
  "$ROOT/" "$STAGE/"

FILES=$(cd "$STAGE" && find . -type f | sed 's|^\./||' | sort)
COUNT=$(echo "$FILES" | wc -l | tr -d ' ')

# Статика прошлой сборки: её файлы существуют на диске, а .htaccess отдаёт
# существующие файлы как есть — значит /price/index.html перехватит /price/
# у роутера. Каталог app/ не трогаем: там живёт приложение записи.
STATIC_KILL="
index.html
404.html
llms.txt
llms-full.txt
robots.txt
sitemap.xml
manifest.webmanifest
antigraviynaya-plenka/index.html
booking/index.html
consent/index.html
contacts/index.html
cookies/index.html
himchistka/index.html
keramika/index.html
mojka/index.html
oklejka-plenkoj/index.html
polirovka/index.html
price/index.html
privacy/index.html
reviews/index.html
shumoizolyaciya/index.html
terms/index.html
tonirovka/index.html
works/index.html
"

if [ "$GO" -eq 0 ]; then
  echo "Пробный прогон, ничего не меняется."
  echo
  echo "Сервер:  $FTP_HOST"
  echo "Корень:  $FTP_ROOT"
  echo "Файлов к заливке: $COUNT"
  echo
  echo "Первые двадцать:"
  echo "$FILES" | head -20 | sed 's/^/  /'
  echo "  …"
  echo
  echo "Статика под снос (только с --clean-static):"
  echo "$STATIC_KILL" | sed '/^$/d;s/^/  /'
  echo
  echo "Залить: $0 --go --clean-static"
  exit 0
fi

echo "Заливаю $COUNT файлов на $FTP_HOST:$FTP_ROOT"
n=0
while IFS= read -r f; do
  n=$((n + 1))
  printf "\r[%d/%d] %-60.60s" "$n" "$COUNT" "$f"
  curl --netrc --ftp-create-dirs --silent --show-error --fail \
       -T "$STAGE/$f" "ftp://$FTP_HOST$FTP_ROOT/$f" \
    || { echo; echo "Не залился: $f"; exit 1; }
done <<< "$FILES"
echo
echo "Готово."

if [ "$CLEAN" -eq 1 ]; then
  echo "Убираю статику, затеняющую маршруты PHP."
  while IFS= read -r f; do
    [ -z "$f" ] && continue
    # Файла может не быть — это не ошибка, поэтому без --fail
    curl --netrc --silent --show-error \
         -Q "-DELE $FTP_ROOT/$f" "ftp://$FTP_HOST$FTP_ROOT/" >/dev/null 2>&1 \
      && echo "  удалён $f" || echo "  пропущен $f (нет на сервере)"
  done <<< "$STATIC_KILL"
fi

echo
echo "Дальше руками:"
echo "  1. Права на запись для data/ и assets/img/uploads/ (755 или 775)"
echo "  2. Открыть https://nlevel.pro/admin/ и задать логин с длинным паролем"
echo "  3. Проверить, что https://nlevel.pro/data/content.json не открывается"
