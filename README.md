# Document Search Application

A full-stack document search application with PHP backend and Angular frontend. Upload documents (PDF, DOC, DOCX, TXT) and perform full-text search with real-time suggestions and highlighted results.

## Features

### Backend (PHP 8.0+)
- RESTful API architecture
- Document upload and parsing (PDF, DOC, DOCX, TXT)
- MySQL FULLTEXT search indexing
- Content extraction from various file formats
- File storage management
- CORS support

### Frontend (Angular 16+)
- Drag-and-drop file upload
- Document management with pagination
- Real-time search with debouncing
- Auto-suggestions
- Search result highlighting
- Sort by relevance or date
- Responsive Material Design UI

## Tech Stack

**Backend:**
- PHP 8.0+
- MySQL/MariaDB with FULLTEXT indexing
- Composer packages:
  - smalot/pdfparser (PDF parsing)
  - phpoffice/phpword (Word document parsing)
  - vlucas/phpdotenv (environment configuration)

**Frontend:**
- Angular 16+
- TypeScript
- Angular Material
- RxJS

## Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Composer
- Node.js 16+ and npm
- Angular CLI 16+

### Backend Setup

1. Navigate to backend directory:
```bash
cd backend
```

2. Install PHP dependencies:
```bash
composer install
```

3. Configure environment:
```bash
cp .env.example .env
# Edit .env with your database credentials
```

4. Create database:
```bash
mysql -u root -p
CREATE DATABASE document_search CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

5. Run migrations:
```bash
mysql -u root -p document_search < migrations/001_create_documents_table.sql
```

6. Start PHP server:
```bash
cd public
php -S localhost:8000
```

Backend API will be available at `http://localhost:8000`

### Frontend Setup

1. Navigate to frontend directory:
```bash
cd frontend
```

2. Install dependencies:
```bash
npm install
```

3. Start development server:
```bash
npm start
```

Frontend will be available at `http://localhost:4200`

## Project Structure

```
document-search/
├── backend/
│   ├── public/              # Web root
│   │   ├── index.php        # Entry point
│   │   └── .htaccess
│   ├── src/
│   │   ├── Controllers/     # API controllers
│   │   ├── Services/        # Business logic
│   │   │   ├── StorageService.php
│   │   │   ├── ParserService.php
│   │   │   └── SearchService.php
│   │   ├── Helpers/         # Utility classes
│   │   └── bootstrap.php    # App initialization
│   ├── migrations/          # Database migrations
│   ├── uploads/             # Uploaded files
│   ├── composer.json
│   └── .env
│
├── frontend/
│   ├── src/
│   │   ├── app/
│   │   │   ├── models/      # TypeScript interfaces
│   │   │   ├── services/    # API services
│   │   │   ├── modules/
│   │   │   │   ├── documents/
│   │   │   │   │   ├── uploader/
│   │   │   │   │   ├── document-list/
│   │   │   │   │   └── document-details/
│   │   │   │   └── search/
│   │   │   └── app.module.ts
│   │   └── environments/
│   ├── angular.json
│   └── package.json
│
├── README.md
└── SOLUTION.md
```

## API Endpoints

### Documents
- `POST /api/documents/upload` - Upload a document
- `GET /api/documents?page={page}&limit={limit}` - List documents
- `GET /api/documents/{id}` - Get document details
- `DELETE /api/documents/{id}` - Delete document
- `GET /api/documents/{id}/download` - Download document

### Search
- `GET /api/search?q={query}&sort={relevance|date}&page={page}&limit={limit}` - Search documents
- `GET /api/suggestions?q={query}&limit={limit}` - Get search suggestions

## Usage

1. **Upload Documents**: 
   - Go to the "Documents" tab
   - Drag and drop files or click to browse
   - Supported formats: PDF, DOC, DOCX, TXT (max 10MB)

2. **Manage Documents**:
   - View uploaded documents in the list
   - Click view icon to see document details
   - Download or delete documents as needed

3. **Search Documents**:
   - Go to the "Search" tab
   - Enter search terms (minimum 2 characters)
   - View results with highlighted matches
   - Sort by relevance or date
   - Click download to get the document

## Configuration

### Backend (.env)
```env
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

### Frontend (environment.ts)
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api'
};
```

## Testing

### Backend
Test API endpoints using curl:
```bash
# Upload document
curl -X POST http://localhost:8000/api/documents/upload \
  -F "file=@/path/to/document.pdf"

# Search
curl "http://localhost:8000/api/search?q=search+term"
```

### Frontend
```bash
cd frontend
npm test
```

## Production Deployment

### Backend
1. Configure Apache/Nginx virtual host pointing to `backend/public`
2. Set up environment variables
3. Ensure proper file permissions for uploads directory
4. Enable MySQL FULLTEXT indexing

### Frontend
1. Build for production:
```bash
cd frontend
npm run build
```
2. Deploy `dist/` contents to web server
3. Update `environment.prod.ts` with production API URL

## Troubleshooting

**Database Connection Issues:**
- Verify MySQL credentials in `.env`
- Ensure database exists and migrations are run
- Check MySQL service is running

**File Upload Issues:**
- Check PHP `upload_max_filesize` and `post_max_size` settings
- Verify uploads directory permissions (755)
- Ensure allowed file extensions in `.env`

**CORS Issues:**
- Verify `CORS_ORIGIN` in backend `.env` matches frontend URL
- Check browser console for CORS errors

**Search Not Working:**
- Ensure FULLTEXT index is created (check migrations)
- Verify documents have content extracted
- Check MySQL FULLTEXT minimum word length settings

## License

MIT

## Author

Document Search Application - Full Stack Assessment
