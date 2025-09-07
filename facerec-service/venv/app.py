from fastapi import FastAPI, File, Form, UploadFile
from fastapi.responses import JSONResponse
import cv2
import numpy as np
import insightface

# Initialize FastAPI app
app = FastAPI()

# Load InsightFace model (ArcFace)
model = insightface.app.FaceAnalysis()
model.prepare(ctx_id=0)  # 0 = GPU if available, -1 = CPU only

# Store known faces
known_face_embeddings = []
known_face_names = []


@app.post("/register")
async def register_face(image: UploadFile = File(...), name: str = Form(...)):
    # Read image into numpy array
    file_bytes = await image.read()
    npimg = np.frombuffer(file_bytes, np.uint8)
    img = cv2.imdecode(npimg, cv2.IMREAD_COLOR)

    faces = model.get(img)

    if len(faces) > 0:
        # Take first face embedding
        embedding = faces[0].embedding
        known_face_embeddings.append(embedding)
        known_face_names.append(name)
        return JSONResponse({"status": "success", "message": f"Face registered for {name}"})
    else:
        return JSONResponse({"status": "error", "message": "No face detected"}, status_code=400)


@app.post("/recognize")
async def recognize_face(image: UploadFile = File(...)):
    # Read image into numpy array
    file_bytes = await image.read()
    npimg = np.frombuffer(file_bytes, np.uint8)
    img = cv2.imdecode(npimg, cv2.IMREAD_COLOR)

    faces = model.get(img)

    if len(faces) == 0:
        return JSONResponse({"status": "error", "message": "No face detected"}, status_code=400)

    # Compare embedding with known ones
    embedding = faces[0].embedding
    name = "Unknown"

    if known_face_embeddings:
        similarities = [np.dot(embedding, kf) / (np.linalg.norm(embedding) * np.linalg.norm(kf)) for kf in known_face_embeddings]
        best_match_idx = int(np.argmax(similarities))
        if similarities[best_match_idx] > 0.4:  # threshold (tune as needed)
            name = known_face_names[best_match_idx]

    return JSONResponse({"status": "success", "recognized_as": name})

