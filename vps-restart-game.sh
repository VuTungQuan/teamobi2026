#!/usr/bin/env bash
# VPS: pull source game mới từ GitHub, áp lại patch Docker rồi restart container game.
# Java nạp jar lúc khởi động -> BẮT BUỘC restart để dùng jar/code mới.
set -e
cd "$(dirname "$0")"

SRC=Teamobi2026/SRC

# reset --hard trong vps-restart-web.sh xoá luôn patch web (DB host, domain) -> gọi script web để pull + áp lại patch web,
# nếu không web sẽ lỗi "mysqli ... localhost" sau mỗi lần restart game.
echo "=== Pull code + áp lại patch web ==="
bash ./vps-restart-web.sh

echo "=== Patch DB host: localhost -> db ==="
sed -i 's/^database.host=localhost/database.host=db/' "$SRC/Config.properties"

echo "=== Fix hoa/thường tile_set_info (Windows -> Linux) ==="
# code đọc 'tile_set_info' (i thường); file thật là 'tile_set_Info'. Linux phân biệt hoa/thường.
ln -sf tile_set_Info "$SRC/data/map/tile_set_info"

echo "=== Restart game ==="
docker compose up -d game       # tạo nếu container chưa tồn tại
docker compose restart game     # reload jar/code mới

sleep 20
docker compose ps game --format "{{.State}} {{.Status}} {{.Ports}}"
echo "--- log ---"
docker compose logs --tail=12 game 2>&1 | sed -r 's/\x1B\[[0-9;]*[mK]//g' | grep -vE '^game-1 +. $' | tail -12
echo "=== Xong ==="
