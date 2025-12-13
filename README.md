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
- Backend Docker env (`backend/.env.docker`): `APP_ENV`, `APP_SECRET`, `MONGODB_URL`, `MONGODB_DB`, `JWT_*`, `CORS_ALLOW_ORIGIN`.
- Frontend Docker env (`frontend/.env.docker`): `VITE_API_URL` (defaults to `http://api:8000` from inside containers).
- Local defaults live in `backend/.env`. Adjust as needed.

## Notes
- Images install PHP MongoDB extension and Node 20. Rebuild after dependency changes: `docker compose build`.
- Code is bind-mounted into containers for development. Ensure `composer install` (backend) and `npm install` (frontend) have been run if you clean dependencies locally.

## Backend endpoints (base `http://localhost:8000`)
- `POST /api/auth/register` — create user and return JWT.
- `POST /api/auth/login` — JSON login, returns JWT.
- `GET /api/me` — current user profile (Bearer token).
- `PATCH /api/me` — update name/email (Bearer token).
- `GET /api/sessions` — list sessions, pagination via `page`/`limit` (Bearer token).
- `POST /api/sessions` — create session (Bearer token).
- `PUT|PATCH /api/sessions/{id}` — update session (Bearer token).
- `DELETE /api/sessions/{id}` — delete session (Bearer token).
- `GET /api/reservations` — list current user reservations (Bearer token).
- `POST /api/reservations` — book session `{ "sessionId": "<id>" }` (Bearer token).
- `DELETE /api/reservations/{id}` — cancel own reservation (Bearer token).

## Backend setup (manual, if not using Docker)
```bash
cd backend
# install deps locally (ignore platform reqs if extensions missing)
composer install --ignore-platform-req=ext-mongodb --ignore-platform-req=ext-xml

# generate JWT keys once
mkdir -p config/jwt
php bin/console lexik:jwt:generate-keypair --skip-if-exists
```
Environment flags to set:
- `MONGODB_URL` (e.g., `mongodb://localhost:27017`)
- `MONGODB_DB` (e.g., `ets`)
- `JWT_SECRET_KEY` / `JWT_PUBLIC_KEY` (paths to generated keys)
- `JWT_PASSPHRASE` (matches the passphrase used to generate keys)
- `CORS_ALLOW_ORIGIN` (e.g., `^https?://localhost:3000$`)
