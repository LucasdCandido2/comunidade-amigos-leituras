[Region: asia-southeast1]
=========================
Using Detected Dockerfile
=========================

context: h83r-DRu8

internal
load build definition from Dockerfile
0ms

internal
load metadata for docker.io/library/node:20-alpine
763ms

internal
load .dockerignore
0ms

internal
load build context
0ms

builder
FROM docker.io/library/node:20-alpine@sha256:f598378b5240225e6beab68fa9f356db1fb8efe55173e6d4d8153113bb8f333c
7ms

builder
WORKDIR /app cached
1ms

builder
COPY frontend/src ./src cached
0ms

builder
RUN npm install cached
0ms

builder
COPY frontend/package*.json ./ cached
0ms

builder
COPY frontend/vite.config.js ./
0ms

builder
COPY frontend/index.html ./ cached
0ms

builder
COPY frontend/public ./public cached
0ms
Dockerfile:11
-------------------
9 |     COPY frontend/public ./public
10 |     COPY frontend/index.html ./
11 | >>> COPY frontend/vite.config.js ./
12 |
13 |     RUN npm run build
-------------------
ERROR: failed to build: failed to solve: failed to compute cache key: failed to calculate checksum of ref pvmsspwgy887a3ywz66nvmau6::cgk6azu1gewbtg7qajf0h2ujj: "/frontend/vite.config.js": not found



esse é o json de configuração do railway

{
  "$schema": "https://railway.com/railway.schema.json",
  "build": {
    "builder": "DOCKERFILE",
    "buildCommand": "npm install && npm run build",
    "dockerfilePath": "Dockerfile"
  },
  "deploy": {
    "runtime": "V2",
    "numReplicas": 1,
    "startCommand": "npx serve dist -l 3000",
    "preDeployCommand": [
      "npm install && npm run build"
    ],
    "sleepApplication": false,
    "useLegacyStacker": false,
    "ipv6EgressEnabled": false,
    "multiRegionConfig": {
      "asia-southeast1-eqsg3a": {
        "numReplicas": 1
      }
    },
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}