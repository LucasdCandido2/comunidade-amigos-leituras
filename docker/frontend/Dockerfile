# imagem base oficial do node.js 20 (debian bookworm)
from node:20-bookworm

workdir /app

# copiar package.json e package-lock.json primeiro (para aproveitar o cache)
COPY package*.json ./

# instalar dependências dentro do container linux
RUN npm install

# copiar o restante do código (incluindo vite.config.js, src/, etc)
COPY . .

# garantir que o node_modules/.bin esteja no path
ENV PATH="/app/node_modules/.bin:${PATH}"

EXPOSE 3000

CMD ["npm", "run", "dev", "--", "--host"]