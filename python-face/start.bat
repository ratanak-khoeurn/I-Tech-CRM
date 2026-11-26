@echo off
REM Starts the student face-recognition microservice used by the CRM
REM (config('services.face.url'), default http://127.0.0.1:8001).
REM Run this in its own terminal and keep it open while scanning attendance.

cd /d "%~dp0"

if not exist "venv\Scripts\python.exe" (
    echo [face-api] Creating virtual environment...
    python -m venv venv
    echo [face-api] Installing dependencies...
    venv\Scripts\python.exe -m pip install --upgrade pip
    venv\Scripts\python.exe -m pip install -r requirements.txt
)

echo [face-api] Starting on http://127.0.0.1:8001  (press Ctrl+C to stop)
venv\Scripts\python.exe -m uvicorn --app-dir app main:app --host 127.0.0.1 --port 8001
