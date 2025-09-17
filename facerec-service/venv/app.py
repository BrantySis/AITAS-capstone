from fastapi import FastAPI, File, Form, UploadFile
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from typing import List
import cv2
import numpy as np
import insightface
import requests  # NEW: for talking to Laravel

# Initialize FastAPI app
app = FastAPI(title="Face Recognition API")

# Allow all origins (for local testing with Laravel frontend)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # later restrict to Laravel domain
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Load InsightFace model (ArcFace)
model = insightface.app.FaceAnalysis()
model.prepare(ctx_id=0)  # 0 = GPU if available, -1 = CPU only

# Laravel backend URL (adjust if needed)
LARAVEL_API = "http://127.0.0.1:8001"

# ---------------------------
# REGISTER FACE
# ---------------------------
@app.post("/register")
async def register_face(
    images: List[UploadFile] = File(...),
    user_id: int = Form(...)
):
    """
    Register multiple images for a single user (front, left, right).
    Store embeddings in Laravel DB.
    """
    embeddings = []

    for image in images:
        file_bytes = await image.read()
        npimg = np.frombuffer(file_bytes, np.uint8)
        img = cv2.imdecode(npimg, cv2.IMREAD_COLOR)

        faces = model.get(img)

        if len(faces) > 0:
            for face in faces:
                embeddings.append(face.embedding.tolist())

    if not embeddings:
        return JSONResponse({
            "status": "error",
            "message": "No faces detected"
        }, status_code=400)

    # Average embedding (optional)
    avg_embedding = np.mean(embeddings, axis=0).tolist()

    # Send to Laravel DB
    resp = requests.post(
        f"{LARAVEL_API}/store-embedding",
        json={"user_id": user_id, "embeddings": avg_embedding}
    )

    if resp.status_code == 200:
        return JSONResponse({
            "status": "success",
            "message": "Embeddings saved to DB",
            "count": len(embeddings)
        })
    else:
        return JSONResponse({
            "status": "error",
            "message": "Failed to save embeddings to DB",
            "laravel_response": resp.text
        }, status_code=500)

# ---------------------------
# RECOGNIZE FACE
# ---------------------------
@app.post("/recognize")
async def recognize_face(image: UploadFile = File(...)):
    """
    Recognize a face against all users stored in Laravel DB.
    """
    file_bytes = await image.read()
    npimg = np.frombuffer(file_bytes, np.uint8)
    img = cv2.imdecode(npimg, cv2.IMREAD_COLOR)

    faces = model.get(img)
    if len(faces) == 0:
        return JSONResponse({"status": "error", "message": "No face detected"}, status_code=400)

    embedding = faces[0].embedding.tolist()

    # Ask Laravel to compare
    resp = requests.post(
        f"{LARAVEL_API}/recognize-embedding",
        json={"embedding": embedding}
    )

    if resp.status_code == 200:
        return JSONResponse(resp.json())
    else:
        return JSONResponse({
            "status": "error",
            "message": "Failed to connect to Laravel",
            "laravel_response": resp.text
        }, status_code=500)
