#!/usr/bin/env bash
# Pull code mới rồi restart server game (21.jar). Chạy bằng Git Bash.
set -e
cd "$(dirname "$0")"

JAR=21.jar

echo "=== Pull code ==="
git pull --ff-only

echo "=== Tat server game cu ==="
# ponytail: kill thẳng, người đang online mất dữ liệu chưa lưu. Muốn an toàn thì bảo trì trong game trước khi chạy file này.
powershell -NoProfile -Command "Get-CimInstance Win32_Process -Filter \"Name='java.exe'\" | Where-Object { \$_.CommandLine -like '*$JAR*' } | ForEach-Object { Stop-Process -Id \$_.ProcessId -Force }"

echo "=== Bat server game ==="
cd Teamobi2026/SRC
cmd //c start "NRO Server" run.bat

echo "=== Xong ==="
