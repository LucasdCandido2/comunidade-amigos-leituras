FROM node:20-alpine AS builder

WORKDIR /app

COPY package*.json ./
COPY vite.config.js ./

ARG VITE_API_URL
ENV VITE_API_URL=$VITE_API_URL

RUN npm install

COPY src ./src
COPY public ./public
COPY index.html ./

RUN npm run build

FROM node:20-alpine

WORKDIR /app

COPY --from=builder /app/dist ./dist

RUN npm install -g serve

EXPOSE 3000
CMD ["serve", "dist", "-l", "3000"]