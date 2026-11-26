import cv2
import numpy as np

from fastapi import FastAPI, UploadFile, File, HTTPException
from pydantic import BaseModel
from pathlib import Path

app = FastAPI(title="Student Face Recognition API")

BASE_DIR = Path(__file__).resolve().parent.parent
MODEL_DIR = BASE_DIR / "models"

DETECTOR_PATH = MODEL_DIR / "face_detection_yunet_2023mar.onnx"
RECOGNIZER_PATH = MODEL_DIR / "face_recognition_sface_2021dec.onnx"

if not DETECTOR_PATH.exists() or not RECOGNIZER_PATH.exists():
    raise RuntimeError("OpenCV ONNX models are missing.")

detector = cv2.FaceDetectorYN.create(
    str(DETECTOR_PATH),
    "",
    (320, 320),
    0.9,
    0.3,
    5000
)

recognizer = cv2.FaceRecognizerSF.create(
    str(RECOGNIZER_PATH),
    ""
)


def extract_embedding(image_bytes):
    image_array = np.frombuffer(image_bytes, np.uint8)
    image = cv2.imdecode(image_array, cv2.IMREAD_COLOR)

    if image is None:
        raise ValueError("Invalid image.")

    height, width = image.shape[:2]

    detector.setInputSize((width, height))

    _, faces = detector.detect(image)

    if faces is None or len(faces) != 1:
        raise ValueError("Please ensure exactly one face is visible.")

    aligned_face = recognizer.alignCrop(image, faces[0])
    feature = recognizer.feature(aligned_face)

    return feature.flatten().astype(float).tolist()


class MatchRequest(BaseModel):
    embedding: list[float]
    profiles: list[dict]


@app.get("/health")
def health():
    return {"status": "running"}


@app.post("/embedding")
async def create_embedding(file: UploadFile = File(...)):
    try:
        image_bytes = await file.read()

        embedding = extract_embedding(image_bytes)

        return {
            "success": True,
            "embedding": embedding,
            "model_version": "opencv-sface-2021dec"
        }

    except ValueError as error:
        raise HTTPException(status_code=422, detail=str(error))


@app.post("/match")
def match_face(request: MatchRequest):
    if len(request.embedding) != 128:
        raise HTTPException(status_code=422, detail="Invalid embedding.")

    if not request.profiles:
        return {"success": False, "message": "No registered faces."}

    query = np.asarray(request.embedding, dtype=np.float32)

    best_match = None
    best_score = -1.0

    for profile in request.profiles:
        try:
            stored = np.asarray(
                profile["embedding"],
                dtype=np.float32
            )

            if stored.shape != query.shape:
                continue

            score = float(
                np.dot(query, stored) /
                (np.linalg.norm(query) * np.linalg.norm(stored) + 1e-10)
            )

            if score > best_score:
                best_score = score
                best_match = profile["student_id"]

        except (KeyError, TypeError, ValueError):
            continue

    # This is an initial threshold only. Calibrate using
    # representative test data before using it in production.
    threshold = 0.40

    if best_match is not None and best_score >= threshold:
        return {
            "success": True,
            "student_id": best_match,
            "similarity": round(best_score, 4)
        }

    return {
        "success": False,
        "message": "No matching student found."
    }
