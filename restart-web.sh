#!/usr/bin/env bash
# Pull code mới, chép web sang XAMPP rồi restart Apache. Chạy bằng Git Bash.
set -e
cd "$(dirname "$0")"

XAMPP=/c/xampp
WEB_SRC=sourceweb/srcnrofree.online16/Website
WEB_DST=$XAMPP/htdocs/cat.io.vn

echo "=== Pull code ==="
git pull --ff-only

echo "=== Chep web sang $WEB_DST ==="
# -u: chỉ chép file mới hơn, không đè log đang ghi trên web chạy thật.
cp -ru "$WEB_SRC/." "$WEB_DST/"

echo "=== Restart Apache ==="
taskkill //F //IM httpd.exe >/dev/null 2>&1 || true
cmd //c start "Apache" //min "C:\\xampp\\apache_start.bat"

echo "=== Xong ==="
