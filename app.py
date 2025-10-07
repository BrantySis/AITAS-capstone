import os
import logging
from typing import List

import cv2
import numpy as np
import insightface
import requests
from numpy.linalg import norm
from fastapi import FastAPI, File, Form, UploadFile
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from fastapi.openapi.utils import get_openapi
import psutil


# -------------------------------
# Environment Variables (Render)
# -------------------------------
FASTAPI_URL = os.getenv("FASTAPI_URL")
LARAVEL_URL = os.getenv("LARAVEL_URL")

if not FASTAPI_URL or not LARAVEL_URL:
    raise RuntimeError("FASTAPI_URL and LARAVEL_URL must be set in environment variables")

# -------------------------------
# Logging
# -------------------------------
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)
logger.info(f"FastAPI URL: {FASTAPI_URL}")
logger.info(f"Laravel URL: {LARAVEL_URL}")

def log_memory(stage: str):
    process = psutil.Process(os.getpid())
    used = process.memory_info().rss / 1024 ** 2  # MB
    print(f"[MEMORY] {stage}: {used:.2f} MB used")

# -------------------------------
# FastAPI Initialization
# -------------------------------
app = FastAPI(title="Face Recognition API")

def custom_openapi():
    if app.openapi_schema:
        return app.openapi_schema
    openapi_schema = get_openapi(
        title=app.title,
        version="1.0.0",
        description="Face Recognition API Docs",
        routes=app.routes,
    )
    # Always use FASTAPI_URL for Swagger
    openapi_schema["servers"] = [{"url": FASTAPI_URL}]
    app.openapi_schema = openapi_schema
    return app.openapi_schema

app.openapi = custom_openapi

# -------------------------------
# CORS Middleware
# -------------------------------
app.add_middleware(
    CORSMiddleware,
    allow_origins=["https://aitas-capstone.onrender.com"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# -------------------------------
# InsightFace Model Lazy Load
# -------------------------------
model = None
def get_model():
    global model
    if model is None:
        logger.info("Loading InsightFace model...")
        log_memory("Before model load")   # ✅ Added here
        model = insightface.app.FaceAnalysis()
        model.prepare(ctx_id=-1)  # CPU mode
        logger.info("Model loaded successfully.")
        log_memory("After model load")    # ✅ Added here
    return model

# -------------------------------
# Cosine similarity
# -------------------------------
def cosine_similarity(vec1, vec2):
    return np.dot(vec1, vec2) / (norm(vec1) * norm(vec2))

# -------------------------------
# REGISTER FACE
# -------------------------------
@app.post("/register")
async def register_face(
    images: List[UploadFile] = File(...),
    user_id: int = Form(...)
):
    logger.info(f"Received {len(images)} images for user_id={user_id}")
    embeddings = []

    for idx, image in enumerate(images):
        try:
            file_bytes = await image.read()
            npimg = np.frombuffer(file_bytes, np.uint8)
            img = cv2.imdecode(npimg, cv2.IMREAD_COLOR)

            faces = get_model().get(img)
            logger.info(f"Detected {len(faces)} faces in image {idx+1}")

            if len(faces) == 0:
                return JSONResponse({"status": "error", "message": f"No face detected in image {idx+1}"}, status_code=400)
            if len(faces) > 1:
                return JSONResponse({"status": "error", "message": f"Multiple faces detected in image {idx+1}. Only one person allowed."}, status_code=400)

            embeddings.append(faces[0].embedding.tolist())
        except Exception as e:
            logger.error(f"Failed processing image {idx+1}: {e}")

    if not embeddings:
        return JSONResponse({"status": "error", "message": "No faces detected"}, status_code=400)

    # Validate consistency
    threshold = 0.55
    consistent = all(
        cosine_similarity(embeddings[i], embeddings[j]) >= threshold
        for i in range(len(embeddings))
        for j in range(i + 1, len(embeddings))
    )

    if not consistent:
        return JSONResponse({"status": "error", "message": "Uploaded images do not belong to the same person"}, status_code=400)

    avg_embedding = np.mean(embeddings, axis=0).tolist()
    logger.info(f"Total embeddings collected: {len(embeddings)}")

    # Send embeddings to Laravel
    try:
        resp = requests.post(
            f"{LARAVEL_URL}/api/store-embedding",
            json={"user_id": user_id, "embeddings": avg_embedding},
            timeout=10
        )
        if resp.status_code == 200:
            logger.info("Embeddings saved successfully in Laravel")
            return JSONResponse({"status": "success", "message": "Embeddings saved to DB", "count": len(embeddings)})
        else:
            logger.error(f"Laravel returned {resp.status_code}: {resp.text}")
            return JSONResponse({"status": "error", "message": "Failed to save embeddings", "laravel_response": resp.text}, status_code=500)
    except Exception as e:
        logger.error(f"Laravel POST failed: {e}")
        return JSONResponse({"status": "error", "message": f"Laravel POST failed: {e}"}, status_code=500)

# -------------------------------
# RECOGNIZE FACE
# -------------------------------
@app.post("/recognize")
async def recognize_face(image: UploadFile = File(...)):
    try:
        file_bytes = await image.read()
        npimg = np.frombuffer(file_bytes, np.uint8)
        img = cv2.imdecode(npimg, cv2.IMREAD_COLOR)
    except Exception as e:
        return JSONResponse({"status": "error", "message": f"Failed to read image: {e}"}, status_code=400)

    try:
        faces = get_model().get(img)
        if len(faces) == 0:
            return JSONResponse({"status": "error", "message": "No face detected"}, status_code=400)
        if len(faces) > 1:
            return JSONResponse({"status": "error", "message": "Multiple faces detected. Only one person allowed."}, status_code=400)

        new_embedding = faces[0].embedding

        resp = requests.get(f"{LARAVEL_URL}/api/get-embeddings", timeout=10)
        if resp.status_code != 200:
            return JSONResponse({"status": "error", "message": "Failed to fetch embeddings from Laravel"}, status_code=500)

        data = resp.json()
        stored_embeddings = data.get("embeddings", [])
        if not stored_embeddings:
            return JSONResponse({"status": "error", "message": "No embeddings found in DB"}, status_code=404)

        best_match = None
        best_score = -1
        threshold = 0.55

        for item in stored_embeddings:
            db_vec = np.array(item["embedding"])
            score = cosine_similarity(new_embedding, db_vec)
            if score > best_score:
                best_score = score
                best_match = item["user_id"]

        if best_score >= threshold:
            return JSONResponse({"status": "success", "match": best_match, "similarity": float(best_score)})
        else:
            return JSONResponse({"status": "fail", "match": None, "message": "Face not recognized", "best_score": float(best_score)})

    except Exception as e:
        return JSONResponse({"status": "error", "message": f"Recognition failed: {e}"}, status_code=500)
