#!/bin/bash
# start.sh

# Make sure the script is executable
chmod +x start.sh

exec uvicorn app:app --host 0.0.0.0 --port ${PORT:-8000} --reload

