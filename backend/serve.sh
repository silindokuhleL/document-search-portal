#!/bin/bash

# Simple server startup script

echo "🚀 Starting Document Search Backend Server"
echo "=========================================="
echo ""
echo "Server will be available at: http://localhost:8000"
echo "API endpoints at: http://localhost:8000/api"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

# Start PHP built-in server
php -S localhost:8000 -t public
