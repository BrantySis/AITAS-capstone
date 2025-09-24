from fastapi import FastAPI, File, Form, UploadFile
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from typing import List
import cv2
import numpy as np
import insightface
import requests
import os

# -------------------------------
# FastAPI app initialization
# -------------------------------
app = FastAPI(title="Face Recognition API")

# Allow CORS for your Laravel frontend
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:8000", "https://aitas-capstone.test"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Laravel backend URL
LARAVEL_API = "https://aitas-capstone.test/api"

# Path to Herd SSL certificate
#HERD_CERT_PATH = r"C:\Users\Kurt\.config\herd\config\valet\Certificates\aitas-capstone.test.crt"
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

            for face in faces:
                embeddings.append(face.embedding.tolist())
        except Exception as e:
            print(f"[ERROR] Failed processing image {idx+1}: {e}")

    if not embeddings:
        print("[DEBUG] No faces detected in any image")
        return JSONResponse({"status":"error","message":"No faces detected"}, status_code=400)

    avg_embedding = np.mean(embeddings, axis=0).tolist()
    print(f"[DEBUG] Total embeddings: {len(embeddings)}")

    # Send embeddings to Laravel using Herd cert
    try:
        resp = requests.post(
            f"{LARAVEL_API}/store-embedding",
            json={"user_id": user_id, "embeddings": avg_embedding},
            timeout=10,
            verify=False
        )
        if resp.status_code == 200:
            print("[DEBUG] Embeddings saved successfully in Laravel")
            return JSONResponse({"status":"success","message":"Embeddings saved to DB","count":len(embeddings)})
        else:
            print(f"[ERROR] Laravel returned status {resp.status_code}: {resp.text}")
            return JSONResponse({"status":"error","message":"Failed to save embeddings","laravel_response":resp.text}, status_code=500)
    except Exception as e:
        print(f"[ERROR] Laravel POST failed: {e}")
        return JSONResponse({"status":"error","message":f"Laravel POST failed: {e}"}, status_code=500)

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
        return JSONResponse({"status":"error","message":f"Failed to read image: {e}"}, status_code=400)

    try:
        faces = get_model().get(img)
        if len(faces) == 0:
            return JSONResponse({"status":"error","message":"No face detected"}, status_code=400)

        embedding = faces[0].embedding.tolist()

        resp = requests.post(
            f"{LARAVEL_API}/recognize-embedding",
            json={"embedding": embedding},
            timeout=10,
            verify=False
        )

        if resp.status_code == 200:
            return JSONResponse(resp.json())
        else:
            return JSONResponse({"status":"error","message":"Failed to connect to Laravel","laravel_response":resp.text}, status_code=500)

    except Exception as e:
        return JSONResponse({"status":"error","message":f"Recognition failed: {e}"}, status_code=500)
