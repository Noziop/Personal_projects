# Build stage
FROM node:18-alpine as development

WORKDIR /app

COPY package*.json ./
RUN npm install
RUN npm install @vitejs/plugin-vue

COPY . .

EXPOSE 5173

CMD ["npm", "run", "dev"]