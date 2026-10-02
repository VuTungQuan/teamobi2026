@ECHO OFF
CD /D "%~dp0"
TITLE Ket noi vao server NRO
REM Chay 1 lan. Tu hien UAC -> bam Yes.
net session >nul 2>&1 || (powershell -NoProfile -Command "Start-Process -Verb RunAs '%~f0'" & exit /b)

set SERVER=100.93.7.125
set PORT=14445
set AUTHKEY=tskey-auth-ki21czR8W721CNTRL-UPSNGWjc9iBe8a6WPHAeiBjrKwtBZKNAY
set TS="C:\Program Files\Tailscale\tailscale.exe"

if not exist %TS% (
  if not exist "%~dp0tailscale-setup.msi" (echo Thieu file tailscale-setup.msi trong thu muc nay & pause & exit /b)
  echo === Dang cai Tailscale, cho khoang 1 phut ===
  msiexec /i "%~dp0tailscale-setup.msi" /passive /norestart
  if not exist %TS% (echo == Cai Tailscale that bai == & pause & exit /b)
)

echo === Dang vao mang cua server ===
%TS% up --authkey=%AUTHKEY% --hostname=nro-%COMPUTERNAME%
if errorlevel 1 (echo == Khong vao duoc mang. Auth key co the da het han - xin key moi == & pause & exit /b)

echo === Cho Tailscale dung route roi kiem tra server (toi da 30 giay) ===
powershell -NoProfile -Command "for($i=1;$i -le 15;$i++){ $c=New-Object Net.Sockets.TcpClient; try{ $c.Connect('%SERVER%',%PORT%); 'THAY SERVER OK (lan thu '+$i+')'; $c.Close(); exit 0 }catch{ Write-Host ('  thu lan '+$i+' chua duoc, cho 2s...'); Start-Sleep -Seconds 2 } }; 'KHONG THAY SERVER sau 30 giay'; exit 1"
if errorlevel 1 (
  echo.
  echo Server dang tat, hoac chu server chua bat MySQL va run.bat. Nhan chu server kiem tra.
  pause & exit /b
)

sc config iphlpsvc start= auto >nul 2>&1
net start iphlpsvc >nul 2>&1

echo === Chuyen huong 127.0.0.1:%PORT% -^> %SERVER%:%PORT% ===
netsh interface portproxy delete v4tov4 listenaddress=127.0.0.1 listenport=%PORT% >nul 2>&1
netsh interface portproxy add v4tov4 listenaddress=127.0.0.1 listenport=%PORT% connectaddress=%SERVER% connectport=%PORT% || (echo Loi netsh & pause & exit /b)

echo.
netsh interface portproxy show v4tov4
echo.
echo ================== XONG - MO XUNGLORDLOCAL.exe DE CHOI ==================
echo Lan sau chi can mo XUNGLORDLOCAL.exe, khong phai chay lai file nay.
pause
