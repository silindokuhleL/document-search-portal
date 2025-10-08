# Document Search Backend

PHP 8.0+ backend API for document management and full-text search.

## Features

- **Document Upload**: Support for PDF and TXT files
- **Full-Text Search**: MySQL FULLTEXT indexing with intelligent fallback to LIKE search
- **Smart Suggestions**: Context-aware search suggestions extracted from document content
- **Document Parsing**: Automatic content extraction from uploaded files
- **Caching System**: Built-in caching for improved search performance
- **RESTful API**: Clean, well-structured API endpoints
- **CORS Support**: Configured for frontend integration

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+ (with FULLTEXT search support)
- Composer (dependency management)
- Apache with mod_rewrite or nginx
- PHP Extensions: PDO, pdo_mysql, mbstring, fileinfo

## Installation

1. Install dependencies:
```bash
cd backend
composer install
```

2. Configure environment:
```bash
cp .env.example .env
# Edit .env with your database credentials
```

3. Create database:
```bash
mysql -u root -p
CREATE DATABASE document_search CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Run migrations:
```bash
mysql -u root -p document_search < migrations/001_create_documents_table.sql
```

5. Set permissions:
```bash
chmod -R 755 uploads/
chmod -R 755 cache/
```

## Running the Server

### Using PHP Built-in Server (Development)
```bash
cd public
php -S localhost:8000
```

### Using Apache
Point your virtual host document root to the `public` directory.

## API Endpoints

### Documents

- **`POST /api/documents/upload`**
  - Upload a document
  - Form data: `file` (multipart/form-data)
  - Returns: Document metadata with ID

- **`GET /api/documents`**
  - List all documents with pagination
  - Query params: `page` (default: 1), `limit` (default: 10)
  - Returns: Paginated document list

- **`GET /api/documents/{id}`**
  - Get specific document details
  - Returns: Document metadata and content

- **`DELETE /api/documents/{id}`**
  - Delete a document and its file
  - Returns: Success confirmation

- **`GET /api/documents/{id}/download`**
  - Download original document file
  - Returns: File stream with appropriate headers

### Search

- **`GET /api/search`**
  - Full-text search across documents
  - Query params:
    - `q` (required): Search query
    - `sort` (optional): `relevance` or `date` (default: relevance)
    - `page` (optional): Page number (default: 1)
    - `limit` (optional): Results per page (default: 10)
  - Returns: Search results with highlighted previews, relevance scores, and pagination info

- **`GET /api/suggestions`**
  - Get intelligent search suggestions based on document content
  - Query params:
    - `q` (required): Partial search query (min 2 chars)
    - `limit` (optional): Max suggestions (default: 5)
  - Returns: Array of contextual search term suggestions

## Configuration

Edit `.env` file (copy from `.env.example`):

```env
APP_ENV=development

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=document_search
DB_USER=root
DB_PASS=your_password

# Upload Settings
UPLOAD_DIR=uploads
ALLOWED_EXTENSIONS=pdf,txt  # Only PDF and TXT supported
MAX_FILE_SIZE=10485760      # 10MB in bytes

# CORS Configuration
CORS_ORIGIN=http://localhost:4200
```

**Note**: Currently only PDF and TXT files are supported. DOC/DOCX support would require implementing additional parsing logic with `phpoffice/phpword`.

## Project Structure

```
backend/
├── public/
│   ├── index.php           # Entry point & routing
│   └── .htaccess           # Apache rewrite rules
├── src/
│   ├── Controllers/
│   │   └── DocumentController.php
│   ├── Services/
│   │   ├── SearchService.php      # Search & suggestions logic
│   │   ├── StorageService.php     # Database operations
│   │   ├── ParserService.php      # File parsing
│   │   └── CacheService.php       # Search result caching
│   ├── Helpers/
│   │   ├── ResponseHelper.php     # JSON response formatting
│   │   └── Router.php             # Request routing
│   └── bootstrap.php              # App initialization
├── migrations/
│   └── 001_create_documents_table.sql
├── uploads/                       # Uploaded files storage
├── cache/                         # Search cache files
├── composer.json                  # PHP dependencies
└── .env                          # Environment configuration
```

## Key Features Explained

### Intelligent Search
- **FULLTEXT Search**: For queries with words ≥4 characters
- **LIKE Search**: Fallback for short words or phrases
- **Multi-field Search**: Searches both content and filenames
- **Context-aware Previews**: Shows relevant excerpts with matches highlighted

### Smart Suggestions
- Extracts meaningful phrases from document content
- Returns contextual search terms (not just filenames)
- Includes filename-based suggestions as fallback
- Ensures suggestions always return results when clicked

### Caching
- Automatic caching of search results (5-minute TTL)
- File-based cache system
- Improves performance for repeated searches

## Testing

### Automated Testing (Recommended)

Use the included test script to test all API endpoints automatically:

```bash
chmod +x test-api.sh
./test-api.sh
```

**What the script tests:**
- ✅ Backend server availability check
- ✅ Document upload (uses `../test-document.txt`)
- ✅ Document listing with pagination
- ✅ Search functionality with relevance scoring
- ✅ Context-aware search suggestions
- ✅ HTTP status codes and JSON responses

**Sample Output:**
```
🧪 Testing Document Search API
==============================

Checking if backend server is running...
✓ Backend server is running

1. Testing document upload...
Status: 201

2. Testing document list...
Status: 200

3. Testing search...
Status: 200

4. Testing suggestions...
Status: 200

✅ API tests complete!
```

### Manual Testing

Test individual endpoints with curl:

**Upload a document:**
```bash
curl -X POST http://localhost:8000/api/documents/upload \
  -F "file=@../test-document.txt"
```

**List documents:**
```bash
curl "http://localhost:8000/api/documents?page=1&limit=10"
```

**Search documents:**
```bash
curl "http://localhost:8000/api/search?q=test&sort=relevance"
```

**Get suggestions:**
```bash
curl "http://localhost:8000/api/suggestions?q=test&limit=5"
```

**Download document:**
```bash
curl "http://localhost:8000/api/documents/1/download" -o downloaded.pdf
```

## Troubleshooting

### FULLTEXT Search Not Working
- Ensure MySQL version supports FULLTEXT on InnoDB tables (5.6+)
- Verify `content_text` column has FULLTEXT index
- Check minimum word length: MySQL default is 4 characters

### File Upload Fails
- Check `uploads/` directory permissions (755 or 777)
- Verify `MAX_FILE_SIZE` in `.env`
- Check PHP `upload_max_filesize` and `post_max_size` settings

### CORS Errors
- Ensure `CORS_ORIGIN` in `.env` matches frontend URL
- Check Apache/nginx CORS headers configuration
