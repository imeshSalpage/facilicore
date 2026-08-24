FROM node:22-alpine

WORKDIR /app

# Copy package files and install dependencies
COPY package*.json ./
RUN npm install

# Copy application files
COPY . .

EXPOSE 3000
CMD ["npm", "run", "dev"]
