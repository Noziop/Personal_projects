FROM node:18-alpine

WORKDIR /app

COPY package*.json ./

# Installation des dépendances
RUN npm install

# Copie du reste des fichiers
COPY . .

# Expose le port 5173 (port par défaut de Vite)
EXPOSE 5173

# Commande pour lancer Vite en mode dev avec accès depuis l'extérieur
CMD ["npm", "run", "dev"]