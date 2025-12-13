FROM node:20-slim

WORKDIR /app

COPY package*.json ./
RUN npm install --no-progress

COPY . .

# Expose for Vite dev server
EXPOSE 3000

CMD ["npm", "run", "dev", "--", "--host", "--port", "3000"]
