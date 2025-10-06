# Quick Start Guide

Get the Document Search application running in 5 minutes!

## Prerequisites

- ✅ PHP 8.0+ installed
- ✅ MySQL 5.7+ or MariaDB 10.2+ installed
- ✅ Composer installed
- ✅ Node.js 16+ and npm installed
- ✅ Angular CLI 16+ (`npm install -g @angular/cli`)

## Automated Setup

Run the setup script:

```bash
chmod +x setup.sh
./setup.sh
```

## Manual Setup

### 1. Backend Setup (5 steps)

```bash
# Step 1: Install dependencies
cd backend
composer install

# Step 2: Configure environment
cp .env.example .env
# Edit .env with your database credentials

# Step 3: Create database
mysql -u root -p
CREATE DATABASE document_search CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Step 4: Run migration
mysql -u root -p document_search < migrations/001_create_documents_table.sql

# Step 5: Start server
cd public
php -S localhost:8000
```

✅ Backend running at `http://localhost:8000`

### 2. Frontend Setup (3 steps)

Open a new terminal:

```bash
# Step 1: Install dependencies
cd frontend
npm install

# Step 2: Start development server
npm start
```

✅ Frontend running at `http://localhost:4200`

## First Use

1. **Open browser**: Navigate to `http://localhost:4200`

2. **Upload a document**:
   - Go to "Documents" tab
   - Drag & drop a PDF, DOC, DOCX, or TXT file
   - Click "Upload"

3. **Search documents**:
   - Go to "Search" tab
   - Type your search query
   - View results with highlighted matches

## Test Data

Create a test document to try the search:

```bash
# Create a sample text file
echo "This is a test document about artificial intelligence and machine learning. 
The document discusses various aspects of AI technology and its applications 
in modern software development." > test-document.txt
```

Upload this file and search for "artificial intelligence" or "machine learning".

## Troubleshooting

### Backend Issues

**Database connection failed:**
```bash
# Check MySQL is running
mysql -u root -p -e "SELECT 1;"

# Verify credentials in backend/.env
cat backend/.env
```

**Port 8000 already in use:**
```bash
# Use a different port
php -S localhost:8080

# Update frontend/src/environments/environment.ts:
# apiUrl: 'http://localhost:8080/api'
```

### Frontend Issues

**Port 4200 already in use:**
```bash
# Use a different port
ng serve --port 4300
```

**Module not found errors:**
```bash
# Clear cache and reinstall
rm -rf node_modules package-lock.json
npm install
```

## API Testing

Test the backend API directly:

```bash
# Upload a document
curl -X POST http://localhost:8000/api/documents/upload \
  -F "file=@test-document.txt"

# List documents
curl http://localhost:8000/api/documents

# Search
curl "http://localhost:8000/api/search?q=artificial"
```

## Next Steps

- 📖 Read [README.md](README.md) for detailed documentation
- 🏗️ Read [SOLUTION.md](SOLUTION.md) for architecture details
- 🔧 Customize `.env` settings for your needs
- 🚀 Deploy to production (see README.md)

## Support

If you encounter issues:

1. Check the browser console for frontend errors
2. Check PHP error logs for backend issues
3. Verify all prerequisites are installed
4. Ensure database migrations are run
5. Check CORS settings in backend/.env

Happy searching! 🔍
