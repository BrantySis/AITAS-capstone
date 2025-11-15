import os
import json
import logging
from typing import List
import cv2
import numpy as np
import insightface
from numpy.linalg import norm
from fastapi import FastAPI, File, Form, UploadFile
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from fastapi.openapi.utils import get_openapi
import requests

# -------------------------------
# Environment Variables
# -------------------------------
FASTAPI_URL = os.getenv("FASTAPI_URL", "https://aitas-capstone.test:8001")
LARAVEL_URL = os.getenv("LARAVEL_URL", "https://aitas-capstone.test")

if not FASTAPI_URL or not LARAVEL_URL:
    raise RuntimeError("FASTAPI_URL and LARAVEL_URL must be set in environment variables")

# -------------------------------
# Logging
# -------------------------------
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)
logger.info(f"FastAPI URL: {FASTAPI_URL}")
logger.info(f"Laravel URL: {LARAVEL_URL}")

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
    openapi_schema["servers"] = [{"url": FASTAPI_URL}]
    app.openapi_schema = openapi_schema
    return app.openapi_schema

app.openapi = custom_openapi

# -------------------------------
# CORS Middleware
# -------------------------------
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "https://aitas-capstone.onrender.com",
        "http://127.0.0.1:8000",
        "https://aitas-capstone.test",
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# -------------------------------
# Load InsightFace Model (Lazy)
# -------------------------------
model = None
def get_model():
    global model
    if model is None:
        logger.info("Loading InsightFace model...")
        model = insightface.app.FaceAnalysis()
        model.prepare(ctx_id=-1)  # CPU mode
        logger.info("Model loaded successfully.")
    return model

# -------------------------------
# Cosine Similarity
# -------------------------------
def cosine_similarity(vec1, vec2):
    vec1 = np.asarray(vec1, dtype=float)
    vec2 = np.asarray(vec2, dtype=float)
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
            logger.info(f"Detected {len(faces)} faces in image {idx + 1}")

            if len(faces) == 0:
                return JSONResponse(
                    {"status": "error", "message": f"No face detected in image {idx + 1}"},
                    status_code=400
                )
            if len(faces) > 1:
                return JSONResponse(
                    {"status": "error", "message": f"Multiple faces detected in image {idx + 1}"},
                    status_code=400
                )

            embeddings.append(faces[0].embedding.tolist())

        except Exception as e:
            logger.error(f"Failed processing image {idx + 1}: {e}")
            return JSONResponse(
                {"status": "error", "message": f"Internal error during image processing: {e}"},
                status_code=500
            )

    if not embeddings:
        return JSONResponse({"status": "error", "message": "No faces detected"}, status_code=400)

    # Check if all embeddings are consistent (same person)
    threshold = 0.55
    consistent = all(
        cosine_similarity(embeddings[i], embeddings[j]) >= threshold
        for i in range(len(embeddings))
        for j in range(i + 1, len(embeddings))
    )

    if not consistent:
        return JSONResponse({
            "status": "error",
            "message": "Consistency check failed: Images do not belong to the same person."
        }, status_code=400)

    avg_embedding = np.mean(embeddings, axis=0).tolist()
    logger.info(f"Successfully calculated average embedding for user {user_id}")

    return JSONResponse({
        "status": "success",
        "message": "Face scan successful. Embeddings ready for registration.",
        "embeddings": avg_embedding
    })

# -------------------------------  
# RECOGNIZE FACE WITH USER CHECK  
# -------------------------------  
@app.post("/recognize")
async def recognize_face(image: UploadFile = File(...), user_id: int = Form(...)):
    try:
        # Read uploaded image
        file_bytes = await image.read()
        npimg = np.frombuffer(file_bytes, np.uint8)
        img = cv2.imdecode(npimg, cv2.IMREAD_COLOR)
    except Exception as e:
        return JSONResponse({"status": "error", "message": f"Failed to read image: {e}"}, status_code=400)

    try:
        # Detect faces
        faces = get_model().get(img)
        if len(faces) == 0:
            return JSONResponse({"status": "error", "message": "No face detected"}, status_code=400)
        if len(faces) > 1:
            return JSONResponse({"status": "error", "message": "Multiple faces detected"}, status_code=400)

        new_embedding = faces[0].embedding

        # Fetch stored embeddings from Laravel
        resp = requests.get(f"{LARAVEL_URL}/api/get-embeddings", timeout=10, verify=False)
        if resp.status_code != 200:
            return JSONResponse({"status": "error", "message": "Failed to fetch embeddings from Laravel"}, status_code=500)

        data = resp.json()
        stored_embeddings = data.get("embeddings", [])
        if not stored_embeddings:
            return JSONResponse({"status": "error", "message": "No embeddings found in DB"}, status_code=404)

        # Compare against all embeddings
        best_match = None
        best_score = -1
        threshold = 0.55

        for item in stored_embeddings:
            emb_raw = item["embedding"]
            stored_user_id = int(item["user_id"])

            # Only compare embeddings for the same user_id
            if stored_user_id != user_id:
                continue

            # Convert embedding string to numpy array if needed
            if isinstance(emb_raw, str):
                try:
                    emb_raw = json.loads(emb_raw)
                except Exception:
                    logger.warning(f"Failed to parse embedding for user {stored_user_id}")
                    continue

            emb_array = np.array(emb_raw, dtype=float).flatten()
            score = cosine_similarity(new_embedding, emb_array)

            if score > best_score:
                best_score = score
                best_match = stored_user_id

        if best_score >= threshold:
            return JSONResponse({
                "status": "success",
                "match": best_match,
                "similarity": float(best_score)
            })
        else:
            return JSONResponse({
                "status": "fail",
                "match": None,
                "message": "Face does not match the current user",
                "best_score": float(best_score)
            })

    except Exception as e:
        logger.error(f"Recognition failed: {e}")
        return JSONResponse({"status": "error", "message": f"Recognition failed: {e}"}, status_code=500)

