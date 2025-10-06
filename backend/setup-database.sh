#!/bin/bash

# Database Setup Script

echo "🗄️  Database Setup for Document Search"
echo "======================================"
echo ""

# Read database credentials
read -p "MySQL username (default: root): " DB_USER
DB_USER=${DB_USER:-root}

read -sp "MySQL password: " DB_PASS
echo ""

DB_NAME="document_search"

# Create database
echo ""
echo "Creating database '$DB_NAME'..."
mysql -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Database created successfully"
else
    echo "❌ Failed to create database"
    exit 1
fi

# Run migrations
echo ""
echo "Running migrations..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < migrations/001_create_documents_table.sql 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Migrations completed successfully"
else
    echo "❌ Failed to run migrations"
    exit 1
fi

# Update .env file
echo ""
echo "Updating .env file..."
if [ -f ".env" ]; then
    # Update existing .env
    sed -i.bak "s/DB_USER=.*/DB_USER=$DB_USER/" .env
    sed -i.bak "s/DB_PASS=.*/DB_PASS=$DB_PASS/" .env
    sed -i.bak "s/DB_NAME=.*/DB_NAME=$DB_NAME/" .env
    rm .env.bak
    echo "✅ .env file updated"
else
    # Create new .env from example
    cp .env.example .env
    sed -i.bak "s/DB_USER=.*/DB_USER=$DB_USER/" .env
    sed -i.bak "s/DB_PASS=.*/DB_PASS=$DB_PASS/" .env
    sed -i.bak "s/DB_NAME=.*/DB_NAME=$DB_NAME/" .env
    rm .env.bak
    echo "✅ .env file created"
fi

echo ""
echo "🎉 Database setup complete!"
echo ""
echo "You can now start the backend server:"
echo "  cd public"
echo "  php -S localhost:8000"
