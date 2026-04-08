[Region: asia-southeast1]
=========================
Using Detected Dockerfile
=========================

context: mvtm-X_3i

internal
load build definition from Dockerfile
0ms

auth
library/node:pull token for registry-1.docker.io
0ms

builder
FROM docker.io/library/node:20-alpine@sha256:f598378b5240225e6beab68fa9f356db1fb8efe55173e6d4d8153113bb8f333c
7ms

internal
load build context
0ms

builder
WORKDIR /app cached
0ms

builder
COPY frontend/src ./src
0ms

builder
COPY frontend/vite.config.js ./ cached
0ms

builder
COPY frontend/public ./public
0ms
-------------------
11 |     COPY frontend/public ./public
12 | >>> COPY frontend/index.html ./



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