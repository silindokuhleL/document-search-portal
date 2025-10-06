# Document Search Frontend

Angular 16+ frontend application for document search and management.

## Features

- **Document Upload**: Drag-and-drop file upload with support for PDF, DOC, DOCX, and TXT files
- **Document Management**: View, download, and delete documents with pagination
- **Full-Text Search**: Real-time search with debounced input and highlighted matches
- **Search Suggestions**: Auto-suggestions based on document names and content
- **Sorting**: Sort search results by relevance or date
- **Responsive Design**: Clean, modern UI using Angular Material

## Requirements

- Node.js 16+ and npm
- Angular CLI 16+

## Installation

1. Install dependencies:
```bash
cd frontend
npm install
```

2. Configure API endpoint (optional):
Edit `src/environments/environment.ts` to change the backend API URL:
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api'
};
```

## Running the Application

### Development Server
```bash
npm start
# or
ng serve
```

The application will be available at `http://localhost:4200`

### Production Build
```bash
npm run build
```

The build artifacts will be stored in the `dist/` directory.

## Project Structure

```
src/
├── app/
│   ├── models/              # TypeScript interfaces
│   │   └── document.model.ts
│   ├── services/            # API services
│   │   ├── document.service.ts
│   │   └── search.service.ts
│   ├── modules/
│   │   ├── documents/       # Document management
│   │   │   ├── uploader/
│   │   │   ├── document-list/
│   │   │   └── document-details/
│   │   └── search/          # Search interface
│   ├── app.component.*
│   └── app.module.ts
├── environments/            # Environment configs
└── styles.scss             # Global styles
```

## Key Components

### UploaderComponent
- Drag-and-drop file upload
- File validation
- Upload progress indication
- Success/error messaging

### DocumentListComponent
- Paginated document list
- View, download, delete actions
- File type icons
- Responsive table layout

### SearchComponent
- Real-time search with debouncing
- Auto-suggestions
- Result highlighting
- Sort by relevance or date
- Pagination

### DocumentDetailsComponent
- Modal dialog for document preview
- Full content display
- Download functionality

## API Integration

The frontend communicates with the PHP backend through the following endpoints:

- `POST /api/documents/upload` - Upload document
- `GET /api/documents` - List documents
- `GET /api/documents/{id}` - Get document details
- `DELETE /api/documents/{id}` - Delete document
- `GET /api/search` - Search documents
- `GET /api/suggestions` - Get search suggestions
- `GET /api/documents/{id}/download` - Download document

## Styling

The application uses:
- Angular Material for UI components
- Custom SCSS for additional styling
- Responsive design principles
- Material Design icons

## Testing

Run unit tests:
```bash
npm test
```

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
