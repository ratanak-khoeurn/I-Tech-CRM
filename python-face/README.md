# Face Recognition Attendance Service

A small FastAPI microservice that turns a camera photo into a 128-dimension
face embedding and matches it against enrolled students. The Laravel CRM calls
it over HTTP (see `config/services.php` → `services.face.url`, default
`http://127.0.0.1:8001`).

## How it fits the CRM

1. **Enroll a face** — on a student's page, *Enroll face* captures a photo,
   the CRM POSTs it to `/embedding`, and stores the returned vector (encrypted)
   in `student_face_profiles`.
2. **Scan for attendance** — on the Attendance page, *Start camera* → *Scan face*
   captures a frame, the CRM POSTs it to `/embedding`, then to `/match` with all
   active profiles. A match returns the student, and the form auto-submits a
   daily check-in/check-out.

Detection uses OpenCV YuNet (`face_detection_yunet_2023mar.onnx`); recognition
uses SFace (`face_recognition_sface_2021dec.onnx`). Both files live in `models/`
and must be present or the service refuses to start.

## Run it

Double-click `start.bat`, or from a terminal:

```bat
cd python-face
venv\Scripts\python.exe -m uvicorn --app-dir app main:app --host 127.0.0.1 --port 8001
```

Keep this window open while using face attendance. First run creates the venv
and installs `requirements.txt` automatically.

Verify it is up:

```
http://127.0.0.1:8001/health   ->   {"status":"running"}
```

## Endpoints

| Method | Path         | Purpose                                             |
| ------ | ------------ | --------------------------------------------------- |
| GET    | `/health`    | Liveness check.                                     |
| POST   | `/embedding` | Multipart `file` (image) → 128-d embedding vector.  |
| POST   | `/match`     | JSON `{embedding, profiles[]}` → best cosine match. |

`/embedding` requires **exactly one** visible face (HTTP 422 otherwise).
`/match` returns a hit when cosine similarity ≥ `0.40`.

## Notes

- The service must be reachable from the machine running the CRM. For remote /
  mobile camera use, the CRM page must be served over HTTPS.
- `0.40` is a starting threshold. Calibrate against real enrolled faces before
  relying on it in production.
