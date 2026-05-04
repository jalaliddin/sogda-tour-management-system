# Stage 1: Build Vue frontend
FROM node:20-alpine AS frontend-builder

WORKDIR /app
COPY frontend/package*.json ./
RUN npm ci
COPY frontend/ .
# In production the API is on the same domain at /api — no VITE_API_URL needed
RUN npm run build

# Stage 2: Nginx
FROM nginx:1.27-alpine

# Copy built frontend
COPY --from=frontend-builder /app/dist /var/www/frontend

# Copy nginx config
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
