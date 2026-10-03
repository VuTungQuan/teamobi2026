#!/usr/bin/env bash
# VPS: pull code web mới từ GitHub rồi áp lại patch cho môi trường Docker.
# Web mount volume nên KHÔNG cần restart container (Apache/PHP đọc file mỗi request).
set -e
cd "$(dirname "$0")"

SERVER_IP=110.172.28.179
W=sourceweb/srcnrofree.online16/Website

echo "=== Pull code (reset sạch về bản GitHub) ==="
# reset --hard: vứt mọi sửa tracked trên VPS rồi áp lại patch bên dưới (idempotent).
# docker-compose.yml / web.Dockerfile là untracked nên không bị đụng.
git fetch origin
git reset --hard origin/main

echo "=== Patch DB host: localhost -> db ==="
sed -i 's/\$ip_sv = "localhost"/\$ip_sv = "db"/'   "$W/connect.php" "$W/admin/connect.php" "$W/settings.php"
sed -i 's/\$db_host = "localhost"/\$db_host = "db"/' "$W/cauhinh.php"
sed -i 's/\$host = .localhost./\$host = "db"/'       "$W/app/auth_process.php" "$W/app/register_process.php"

echo "=== Patch domain: cat.io.vn -> $SERVER_IP ==="
grep -rl "cat\.io\.vn" "$W" 2>/dev/null | while read -r f; do
  sed -i -e "s#https\?://forum\.cat\.io\.vn#http://$SERVER_IP#g" \
         -e "s#https\?://cat\.io\.vn#http://$SERVER_IP#g" "$f"
done

echo "=== Đảm bảo web container đang chạy ==="
docker compose up -d web
echo "localhost còn sót: $(grep -rl localhost "$W" --include=*.php 2>/dev/null | wc -l) file"
curl -s -o /dev/null -w "web HTTP %{http_code}\n" http://127.0.0.1/
echo "=== Xong ==="
