#!/bin/bash

# API Testing Script for Document Search Backend

API_URL="http://localhost:8000/api"

echo "🧪 Testing Document Search API"
echo "=============================="
echo ""

# Test 1: Upload a document
echo "1. Testing document upload..."
curl -X POST "$API_URL/documents/upload" \
  -F "file=@test-document.txt" \
  -w "\nStatus: %{http_code}\n\n"

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
