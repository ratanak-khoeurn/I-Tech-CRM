@echo off
rem Nightly CRM backup wrapper for the "CRM Backup" scheduled task.
rem backup-crm.php reads the DB credentials from .env itself; nothing secret is stored here.

setlocal
set "PHP=C:\xampp\php\php.exe"
if not exist "%PHP%" set "PHP=php"

if not exist "D:\Company\I-TECH-MEDIA\CRM-BACKUPS" mkdir "D:\Company\I-TECH-MEDIA\CRM-BACKUPS"

"%PHP%" "%~dp0backup-crm.php" >> "D:\Company\I-TECH-MEDIA\CRM-BACKUPS\backup.log" 2>&1
endlocal
