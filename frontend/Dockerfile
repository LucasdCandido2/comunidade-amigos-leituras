FROM node:20-alpine AS builder

WORKDIR /app

COPY frontend/package*.json ./
COPY frontend/vite.config.js ./

RUN npm install

COPY frontend/src ./src
COPY frontend/public ./public
COPY frontend/index.html ./

RUN npm run build

FROM node:20-alpine

WORKDIR /app

COPY --from=builder /app/dist ./dist

RUN npm install -g serve

EXPOSE 3000
CMD ["serve", "dist", "-l", "3000"]