#!/bin/bash

echo "🗄️  Quick Database Setup"
echo "======================"
echo ""

# Prompt for password
read -sp "Enter your MySQL root password: " MYSQL_PASS
echo ""

# Create database
echo "Creating database..."
mysql -u root -p"$MYSQL_PASS" -e "CREATE DATABASE IF NOT EXISTS document_search CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Database created successfully"
else
    echo "❌ Failed to create database. Please check your password."
    exit 1
fi

# Run migration
echo "Running migration..."
mysql -u root -p"$MYSQL_PASS" document_search < migrations/001_create_documents_table.sql 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Migration completed successfully"
else
    echo "❌ Failed to run migration"
    exit 1
fi

# Update .env if password was provided
if [ ! -z "$MYSQL_PASS" ]; then
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' "s/DB_PASS=.*/DB_PASS=$MYSQL_PASS/" .env
    else
        sed -i "s/DB_PASS=.*/DB_PASS=$MYSQL_PASS/" .env
    fi
    echo "✅ Updated .env file with password"
fi

echo ""
echo "🎉 Database setup complete!"
echo ""
echo "Now restart your PHP server:"
echo "  php -S localhost:8000 -t public public/index.php"
