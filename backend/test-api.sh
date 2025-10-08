#!/bin/bash

# API Testing Script for Document Search Backend

API_URL="http://localhost:8000/api"

echo "🧪 Testing Document Search API"
echo "=============================="
echo ""

# Check if backend server is running
echo "Checking if backend server is running..."
if ! curl -s -f "$API_URL/documents" > /dev/null 2>&1; then
  echo "❌ Error: Backend server is not running on http://localhost:8000"
  echo ""
  echo "Please start the backend server first:"
  echo "  cd backend/public"
  echo "  php -S localhost:8000"
  echo ""
  exit 1
fi
echo "✓ Backend server is running"
echo ""

# Test 1: Upload a document
echo "1. Testing document upload..."
if [ -f "../test-document.txt" ]; then
  curl -X POST "$API_URL/documents/upload" \
    -F "file=@../test-document.txt" \
    -w "\nStatus: %{http_code}\n\n"
else
  echo "❌ Error: test-document.txt not found in project root"
fi

# Test 2: List documents
echo "2. Testing document list..."
curl "$API_URL/documents?page=1&limit=10" \
  -w "\nStatus: %{http_code}\n\n"

# Test 3: Search documents
echo "3. Testing search..."
curl "$API_URL/search?q=test&sort=relevance" \
  -w "\nStatus: %{http_code}\n\n"

# Test 4: Get suggestions
echo "4. Testing suggestions..."
curl "$API_URL/suggestions?q=test&limit=5" \
  -w "\nStatus: %{http_code}\n\n"

echo "✅ API tests complete!"
