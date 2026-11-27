@echo off
rem Start XAMPP Apache if it is not already running. Apache serves the CRM origin on
rem 127.0.0.1:8081 (deploy/apache-crm.conf), which is what the Cloudflare tunnel points at.
rem Note: System32 tools are called by full path so this also works from a MSYS shell.

%SystemRoot%\System32\tasklist.exe /FI "IMAGENAME eq httpd.exe" | %SystemRoot%\System32\find.exe /I "httpd.exe" >nul
if %errorlevel%==0 (
    echo Apache is already running.
    exit /b 0
)

start "XAMPP Apache" /min "C:\xampp\apache\bin\httpd.exe"
%SystemRoot%\System32\ping.exe -n 4 127.0.0.1 >nul
%SystemRoot%\System32\tasklist.exe /FI "IMAGENAME eq httpd.exe" | %SystemRoot%\System32\find.exe /I "httpd.exe" >nul
if errorlevel 1 (
    echo Apache did not start. Check C:\xampp\apache\logs\error.log
    exit /b 1
)
echo Apache started.
