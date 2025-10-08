#!/bin/bash

echo "🚀 Document Search Application Setup"
echo "===================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running from project root
if [ ! -d "backend" ] || [ ! -d "frontend" ]; then
    echo -e "${RED}Error: Please run this script from the project root directory${NC}"
    exit 1
fi

# Backend Setup
echo -e "${YELLOW}Setting up Backend...${NC}"
cd backend

# Check for PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}PHP is not installed. Please install PHP 8.0 or higher.${NC}"
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "✓ PHP version: $PHP_VERSION"

# Check for Composer
if ! command -v composer &> /dev/null; then
    echo -e "${RED}Composer is not installed. Please install Composer.${NC}"
    exit 1
fi

echo "✓ Composer found"

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install

# Setup .env file
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ Created .env file${NC}"
    echo -e "${YELLOW}⚠ Please edit backend/.env with your database credentials${NC}"
else
    echo "✓ .env file already exists"
fi

# Create uploads directory
if [ ! -d "uploads" ]; then
    mkdir -p uploads
    chmod 755 uploads
    echo -e "${GREEN}✓ Created uploads directory${NC}"
else
    echo "✓ uploads directory already exists"
fi

# Create cache directory
if [ ! -d "cache" ]; then
    mkdir -p cache
    chmod 755 cache
    echo -e "${GREEN}✓ Created cache directory${NC}"
else
    echo "✓ cache directory already exists"
fi

cd ..

# Frontend Setup
echo ""
echo -e "${YELLOW}Setting up Frontend...${NC}"
cd frontend

# Check for Node.js
if ! command -v node &> /dev/null; then
    echo -e "${RED}Node.js is not installed. Please install Node.js 16 or higher.${NC}"
    exit 1
fi

NODE_VERSION=$(node -v)
echo "✓ Node.js version: $NODE_VERSION"

# Check for npm
if ! command -v npm &> /dev/null; then
    echo -e "${RED}npm is not installed.${NC}"
    exit 1
fi

echo "✓ npm found"

# Install Node dependencies
echo "Installing Node.js dependencies (this may take a few minutes)..."
npm install

cd ..

# Database Setup Instructions
echo ""
echo -e "${GREEN}✅ Setup Complete!${NC}"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo ""
echo "1. Configure Database:"
echo "   - Edit backend/.env with your MySQL credentials"
echo "   - Create database: CREATE DATABASE document_search CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "   - Run migration: mysql -u root -p document_search < backend/migrations/001_create_documents_table.sql"
echo ""
echo "2. Start Backend Server:"
echo "   cd backend/public"
echo "   php -S localhost:8000"
echo ""
echo "3. Start Frontend Server (in a new terminal):"
echo "   cd frontend"
echo "   npm start"
echo ""
echo "4. Open your browser:"
echo "   http://localhost:4200"
echo ""
echo -e "${GREEN}Happy coding! 🎉${NC}"
