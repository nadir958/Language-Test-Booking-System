# Test Technique ETS EMEA

 Docker-based setup for a Symfony backend (MongoDB) and React (Vite) frontend.

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
- Frontend Docker env (`frontend/.env.docker`): `VITE_API_URL` (use `http://localhost:8000` from the browser).
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

## Quick cURL examples (replace `<TOKEN>` / ids)
- Register (returns token):
  ```bash
  curl -X POST http://localhost:8000/api/auth/register \
    -H "Content-Type: application/json" \
    -d '{"name":"Alice","email":"alice@example.com","password":"secret123"}'
  ```
- Login:
  ```bash
  curl -X POST http://localhost:8000/api/auth/login \
    -H "Content-Type: application/json" \
    -d '{"email":"alice@example.com","password":"secret123"}'
  ```
- Create a session:
  ```bash
  curl -X POST http://localhost:8000/api/sessions \
    -H "Authorization: Bearer <TOKEN>" \
    -H "Content-Type: application/json" \
    -d '{"language":"English","location":"Paris","startAt":"2025-12-20T09:00:00Z","seats":12}'
  ```
- List sessions:
  ```bash
  curl -H "Authorization: Bearer <TOKEN>" "http://localhost:8000/api/sessions?page=1&limit=10"
  ```
- Book a reservation:
  ```bash
  curl -X POST http://localhost:8000/api/reservations \
    -H "Authorization: Bearer <TOKEN>" \
    -H "Content-Type: application/json" \
    -d '{"sessionId":"<SESSION_ID>"}'
  ```
- List reservations:
  ```bash
  curl -H "Authorization: Bearer <TOKEN>" http://localhost:8000/api/reservations
  ```
- Cancel a reservation:
  ```bash
  curl -X DELETE -H "Authorization: Bearer <TOKEN>" http://localhost:8000/api/reservations/<RESERVATION_ID>
  ```
