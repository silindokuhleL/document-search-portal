# Document Search Backend

PHP 8.0+ backend API for document management and search.

## Features

- Document upload (PDF, DOC, DOCX, TXT)
- Full-text search with MySQL FULLTEXT indexing
- Document parsing and content extraction
- RESTful API endpoints
- CORS support for frontend integration

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Composer
- Apache with mod_rewrite (or nginx)

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

- `POST /api/documents/upload` - Upload a document
- `GET /api/documents` - List all documents (with pagination)
- `GET /api/documents/{id}` - Get document details
- `DELETE /api/documents/{id}` - Delete a document
- `GET /api/documents/{id}/download` - Download a document

### Search

- `GET /api/search?q={query}&sort={relevance|date}&page={page}&limit={limit}` - Search documents
- `GET /api/suggestions?q={query}&limit={limit}` - Get search suggestions

## Configuration

Edit `.env` file:

```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=document_search
DB_USER=root
DB_PASS=your_password

UPLOAD_DIR=uploads
ALLOWED_EXTENSIONS=pdf,doc,docx,txt
MAX_FILE_SIZE=10485760

CORS_ORIGIN=http://localhost:4200
```

## Testing

Upload a document:
```bash
curl -X POST http://localhost:8000/api/documents/upload \
  -F "file=@/path/to/document.pdf"
```

Search documents:
```bash
curl "http://localhost:8000/api/search?q=your+search+term"
```
