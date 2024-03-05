FROM node:18.19.1-alpine3.19

WORKDIR /app

COPY ../src/package*.json .

RUN npm i

COPY ../src/ .