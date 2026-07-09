# Local Verification Log

## 2026-07-09

Environment:

- PHP 8.5.6
- Composer 2.9.8
- Node.js 24.16.0
- npm 11.13.0
- MySQL client 9.1.0

Commands run:

```bash
cd backend
composer validate --no-check-publish
```

Result:

- Passed with metadata warnings only.
- Warning: no Composer license field is set.

```bash
cd frontend
npm run lint
```

Result:

- Passed.
- Angular reported that all files pass linting.

```bash
cd frontend
npm run build
```

Result:

- Passed.
- Warnings remain for bundle/style budgets and unused `environment.prod.ts` compilation entry.

```bash
cd backend
php -S 127.0.0.1:8000 -t public
./test-api.sh
```

Result:

- Backend server responded.
- Sample TXT upload returned `201`.
- Document list returned `200`.
- Search returned `200`.
- Suggestions returned `200`.

Browser verification:

- Frontend opened at `http://localhost:4200/`.
- Document list loaded four local documents.
- Search for `test` showed suggestions, two results, relevance scores, and highlighted matches.
- Follow-up Browser check opened the frontend at `http://127.0.0.1:4210/`.
- The page rendered the Document Search title, Documents navigation, Search navigation, upload area, supported formats text, and documents table.
- No frontend console errors were reported for the `4210` frontend URL during that check.
- Follow-up Browser check opened the frontend at `http://localhost:4200/`, clicked the first document view action, and confirmed the document detail modal rendered with filename, type, created date, content preview, and download action.
- Captured document detail screenshot at `docs/proof-assets/document-detail.png`.

Additional API workflow proof:

```bash
curl http://127.0.0.1:8000/api/documents/9
```

Result:

- Document detail returned HTTP `200`.
- Response included `test-document.txt`, `text/plain`, and extracted `content_text`.

```bash
curl http://127.0.0.1:8000/api/documents/6/download
```

Result:

- PDF download returned HTTP `200`.
- Response headers included `Content-Type: application/pdf`, `Content-Disposition: attachment; filename="cors-laravel.pdf"`, and `Content-Length: 96908`.
- Saved file was detected locally as a 3-page PDF.

```bash
curl -X POST http://127.0.0.1:8000/api/documents/upload \
  -F "file=@/tmp/document-search-pdf-upload-proof.pdf;type=application/pdf"
```

Result:

- Temporary PDF upload returned success.
- Response included `file_type: application/pdf`.
- Detail check confirmed extracted text length of `3879` characters.
- Temporary PDF proof record was deleted after verification.

```bash
curl -X POST http://127.0.0.1:8000/api/documents/upload \
  -F "file=@/tmp/document-search-delete-proof.txt;type=text/plain"
curl -X DELETE http://127.0.0.1:8000/api/documents/{id}
curl http://127.0.0.1:8000/api/documents/{id}
```

Result:

- Temporary TXT upload returned success.
- Delete returned HTTP `200` with `Document deleted successfully`.
- Follow-up detail request returned HTTP `404` with `Document not found`.

Fresh migration proof:

```bash
mysql -u root -e "CREATE DATABASE document_search_migration_proof CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root document_search_migration_proof < backend/migrations/001_create_documents_table.sql
mysql -u root -e "SHOW TABLES FROM document_search_migration_proof;"
mysql -u root -e "SHOW INDEX FROM document_search_migration_proof.documents;"
mysql -u root -e "DESCRIBE document_search_migration_proof.documents;"
mysql -u root -e "DROP DATABASE IF EXISTS document_search_migration_proof;"
```

Result:

- Temporary migration database created successfully.
- Migration created the `documents` table.
- Index proof included `PRIMARY`, `idx_filename`, `idx_created_at`, and FULLTEXT `idx_content`.
- Column proof included `id`, `filename`, `original_filename`, `file_path`, `file_size`, `file_type`, `content_text`, `created_at`, and `updated_at`.
- Temporary migration database was dropped after verification.

Important local note:

- Use `http://localhost:4200/` for Browser testing with the default backend `.env`, because the backend CORS origin is configured for `http://localhost:4200`.
- Opening the frontend through `http://127.0.0.1:4200/` causes CORS failures unless the backend `CORS_ORIGIN` is changed.
