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

Important local note:

- Use `http://localhost:4200/` for Browser testing with the default backend `.env`, because the backend CORS origin is configured for `http://localhost:4200`.
- Opening the frontend through `http://127.0.0.1:4200/` causes CORS failures unless the backend `CORS_ORIGIN` is changed.
