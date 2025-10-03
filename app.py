from fastapi import FastAPI, File, Form, UploadFile
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from fastapi.openapi.utils import get_openapi
from typing import List
import cv2
import numpy as np
import insightface
import requests
import os
from dotenv import load_dotenv
from numpy.linalg import norm

# -------------------------------
# Load environment variables
# -------------------------------
load_dotenv()

FASTAPI_URL = os.getenv("FASTAPI_URL", "http://127.0.0.1:8001")
LARAVEL_URL = os.getenv("LARAVEL_URL", "http://127.0.0.1:8000/api")

print(f"[DEBUG] FastAPI URL: {FASTAPI_URL}")
print(f"[DEBUG] Laravel URL: {LARAVEL_URL}")

# -------------------------------
# FastAPI app initialization
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
    # 👇 Always use the .env FASTAPI_URL for Swagger
    openapi_schema["servers"] = [
        {"url": FASTAPI_URL}
    ]
    app.openapi_schema = openapi_schema
    return app.openapi_schema

app.openapi = custom_openapi

# Allow CORS for Laravel + Expose URLs
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://localhost:8000",
        "https://aitas-capstone.test",
        FASTAPI_URL,
        LARAVEL_URL,
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# -------------------------------
# InsightFace model (lazy load)
# -------------------------------
model = None

def get_model():
    global model
    if model is None:
        print("[DEBUG] Loading InsightFace model...")
        model = insightface.app.FaceAnalysis()
        model.prepare(ctx_id=-1)  # CPU mode
        print("[DEBUG] Model loaded successfully.")
    return model

# -------------------------------
# Cosine similarity function
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
    print(f"[DEBUG] Received {len(images)} images for user_id={user_id}")

    embeddings = []

    for idx, image in enumerate(images):
        print(f"[DEBUG] Reading image {idx+1}")
        try:
            file_bytes = await image.read()
            npimg = np.frombuffer(file_bytes, np.uint8)
            img = cv2.imdecode(npimg, cv2.IMREAD_COLOR)

            faces = get_model().get(img)
            print(f"[DEBUG] Detected {len(faces)} faces in image {idx+1}")

            if len(faces) == 0:
                return JSONResponse({
                    "status": "error",
                    "message": f"No face detected in image {idx+1}"
                }, status_code=400)

            if len(faces) > 1:
                return JSONResponse({
                    "status": "error",
                    "message": f"Multiple faces detected in image {idx+1}. Please upload images with only one person."
                }, status_code=400)

            embeddings.append(faces[0].embedding.tolist())

        except Exception as e:
            print(f"[ERROR] Failed processing image {idx+1}: {e}")

    if not embeddings:
        print("[DEBUG] No faces detected in any image")
        return JSONResponse({"status": "error", "message": "No faces detected"}, status_code=400)

    # -------------------------------
    # Validate consistency of embeddings
    # -------------------------------
    threshold = 0.55  # adjust for strictness
    consistent = True

    for i in range(len(embeddings)):
        for j in range(i + 1, len(embeddings)):
            sim = cosine_similarity(embeddings[i], embeddings[j])
            print(f"[DEBUG] Similarity between image {i+1} and {j+1}: {sim:.4f}")
            if sim < threshold:
                consistent = False
                break
        if not consistent:
            break

    if not consistent:
        return JSONResponse({
            "status": "error",
            "message": "Uploaded images do not belong to the same person"
        }, status_code=400)

    # Average embedding only if consistent
    avg_embedding = np.mean(embeddings, axis=0).tolist()
    print(f"[DEBUG] Total embeddings collected: {len(embeddings)}")

    # Send embeddings to Laravel
    try:
        resp = requests.post(
            f"{LARAVEL_URL}/store-embedding",
            json={"user_id": user_id, "embeddings": avg_embedding},
            timeout=10,
            verify=False
        )
        if resp.status_code == 200:
            print("[DEBUG] Embeddings saved successfully in Laravel")
            return JSONResponse({"status": "success", "message": "Embeddings saved to DB", "count": len(embeddings)})
        else:
            print(f"[ERROR] Laravel returned status {resp.status_code}: {resp.text}")
            return JSONResponse({"status": "error", "message": "Failed to save embeddings", "laravel_response": resp.text}, status_code=500)
    except Exception as e:
        print(f"[ERROR] Laravel POST failed: {e}")
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
            return JSONResponse({"status": "error", "message": "Multiple faces detected. Please upload an image with only one person."}, status_code=400)

        # New face embedding
        new_embedding = faces[0].embedding

        # 🔹 Get all stored embeddings from Laravel
        resp = requests.get(f"{LARAVEL_URL}/get-embeddings", timeout=10, verify=False)
        if resp.status_code != 200:
            return JSONResponse({"status": "error", "message": "Failed to fetch embeddings from Laravel"}, status_code=500)

        data = resp.json()
        if "embeddings" not in data:
            return JSONResponse({"status": "error", "message": "No embeddings found in DB"}, status_code=404)

        stored_embeddings = data["embeddings"]  # [{ "user_id": 1, "embedding": [..] }, ...]

        best_match = None
        best_score = -1
        threshold = 0.55  # tune this

        for item in stored_embeddings:
            db_vec = np.array(item["embedding"])
            score = cosine_similarity(new_embedding, db_vec)

            if score > best_score:
                best_score = score
                best_match = item["user_id"]

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
                "message": "Face not recognized",
                "best_score": float(best_score)
            })

    except Exception as e:
        return JSONResponse({"status": "error", "message": f"Recognition failed: {e}"}, status_code=500)
