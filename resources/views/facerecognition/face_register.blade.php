<!DOCTYPE html>
<html>
<head>
    <title>Face Registration & Recognition</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        #video {
            width: 400px;
            height: 300px;
            border: 2px solid black;
            border-radius: 8px;
        }
        #canvas {
            display: none;
        }
        button {
            margin-top: 10px;
            padding: 8px 12px;
            border-radius: 4px;
        }
        p {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Face Registration & Recognition</h2>

    <!-- Video Feed -->
    <video id="video" autoplay></video>
    <canvas id="canvas" width="400" height="300"></canvas>

    <br>
    <input type="text" id="name" placeholder="Enter your name">
    <button id="registerAndRecognizeBtn">Register & Recognize</button>

    <p id="status"></p>
    <p id="recognizeStatus"></p>

    <script>

         // Check if getUserMedia is available
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("Your browser does not support camera access. Please use a modern browser and access via HTTP/HTTPS.");
        // Optionally, disable the button so user cannot click it
        document.getElementById('registerAndRecognizeBtn').disabled = true;
    }
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const context = canvas.getContext('2d');
        const registerAndRecognizeBtn = document.getElementById('registerAndRecognizeBtn');
        const status = document.getElementById('status');
        const recognizeStatus = document.getElementById('recognizeStatus');

        let recognizing = false;
        let recognizeInterval;

        // Function to access webcam
        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                return true;
            } catch (err) {
                alert("Camera access denied: " + err);
                return false;
            }
        }

        // Function to capture frame and register face
        async function registerFace(name) {
            return new Promise((resolve, reject) => {
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(async (blob) => {
                    const formData = new FormData();
                    formData.append("image", blob, "face.jpg");
                    formData.append("name", name);

                    try {
                        const response = await fetch("http://192.168.1.12:8000/register", {
                            method: "POST",
                            body: formData
                        });
                        const data = await response.json();
                        resolve(data.message);
                    } catch (error) {
                        reject(error);
                    }
                }, "image/jpeg");
            });
        }

        // Function to start live face recognition
        function startRecognition() {
            recognizing = true;
            recognizeInterval = setInterval(async () => {
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(async (blob) => {
                    const formData = new FormData();
                    formData.append("image", blob, "face.jpg");

                    try {
                        const response = await fetch("http://192.168.1.12:8000/recognize", {
                            method: "POST",
                            body: formData
                        });
                        const data = await response.json();
                        recognizeStatus.textContent = data.name
                            ? "Recognized: " + data.name
                            : "Face not recognized";
                    } catch (error) {
                        recognizeStatus.textContent = "Error: " + error;
                    }
                }, "image/jpeg");
            }, 1000); // every 1 second
        }

        // ✅ Main button flow
        registerAndRecognizeBtn.addEventListener('click', async () => {
            const name = document.getElementById("name").value.trim();
            if (!name) {
                status.textContent = "Please enter a name.";
                return;
            }

            const cameraStarted = await startCamera();
            if (!cameraStarted) return;

            status.textContent = "Position your face in front of the camera...";
            setTimeout(async () => {
                try {
                    const message = await registerFace(name);
                    status.textContent = "Registration successful: " + message;
                    // Start live recognition automatically
                    startRecognition();
                } catch (error) {
                    status.textContent = "Registration failed: " + error;
                }
            }, 2000); // wait 2 seconds for user to position face
        });
    </script>
</body>
</html>
