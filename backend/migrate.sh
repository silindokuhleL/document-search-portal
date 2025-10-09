#!/bin/bash

# Simple Migration Script for Document Search

echo "🔄 Refreshing Database Migrations"
echo "================================="
echo ""

# Prompt for password
read -sp "Enter your MySQL root password: " MYSQL_PASS
echo ""
echo ""

# Drop and recreate database
echo "1. Dropping existing database..."
mysql -u root -p"$MYSQL_PASS" -e "DROP DATABASE IF EXISTS document_search;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Database dropped"
else
    echo "❌ Failed to drop database"
    exit 1
fi

echo ""
echo "2. Creating fresh database..."
mysql -u root -p"$MYSQL_PASS" -e "CREATE DATABASE document_search CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Database created"
else
    echo "❌ Failed to create database"
    exit 1
fi

echo ""
echo "3. Running migrations..."
mysql -u root -p"$MYSQL_PASS" document_search < migrations/001_create_documents_table.sql 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Migrations completed"
else
    echo "❌ Failed to run migrations"
    exit 1
fi

echo ""
echo "🎉 Migration refresh complete!"
echo ""
echo "⚠️  Note: All existing documents have been deleted."
echo ""
echo "Your backend server should automatically pick up the changes."
echo "If you experience issues, restart it:"
echo "  cd public"
echo "  php -S localhost:8000"
echo ""
