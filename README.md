# Test Technique ETS EMEA

Docker-first scaffold for a Symfony backend (MongoDB) and React (Vite) frontend.

## Prerequisites
- Docker and Docker Compose installed.
- Ports `3000` (frontend), `8000` (backend), `27017` (Mongo) available.

## Quick start
1. Build and run all services:
   ```bash
   docker compose up --build
   ```
2. Access the apps:
   - API: http://localhost:8000
   - Frontend (Vite dev server): http://localhost:3000
   - MongoDB: mongodb://localhost:27017 (default DB name `ets`)

## Environment
- Backend Docker env (`backend/.env.docker`): `APP_ENV`, `APP_SECRET`, `MONGODB_URL`.
- Frontend Docker env (`frontend/.env.docker`): `VITE_API_URL` (defaults to `http://api:8000` from inside containers).
- Local defaults live in `backend/.env`. Adjust as needed.

## Notes
- Images install PHP MongoDB extension and Node 20. Rebuild after dependency changes: `docker compose build`.
- Code is bind-mounted into containers for development. Ensure `composer install` (backend) and `npm install` (frontend) have been run if you clean dependencies locally.
