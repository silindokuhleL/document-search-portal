# Document Search Frontend

Angular 16+ frontend application for document search and management.

## Features

- **Document Upload**: Drag-and-drop file upload with support for PDF and TXT files
- **Document Management**: View, download, and delete documents with pagination
- **Full-Text Search**: Real-time search with debounced input (300ms) and highlighted matches
- **Smart Suggestions**: Context-aware search suggestions extracted from document content
- **Advanced Sorting**: Sort search results by relevance score or upload date
- **Search Performance**: Displays search time and result count
- **Responsive Design**: Clean, modern UI using Angular Material
- **Error Handling**: Comprehensive error handling with user-friendly messages
- **Loading States**: Visual feedback during search and upload operations

## Requirements

- Node.js 16+ and npm
- Angular CLI 16+
- Backend API running (see backend README)

## Installation

1. Install dependencies:
```bash
cd frontend
npm install
```

2. Configure API endpoint:
Edit `src/environments/environment.ts` if your backend runs on a different URL:
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api'  
};
```

For production, edit `src/environments/environment.prod.ts`:
```typescript
export const environment = {
  production: true,
  apiUrl: 'https://your-api-domain.com/api'
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
│   ├── models/                    # TypeScript interfaces
│   │   └── document.model.ts      # Document, SearchResult, SearchResponse
│   ├── services/                  # API services
│   │   ├── document.service.ts    # Document CRUD operations
│   │   └── search.service.ts      # Search & suggestions
│   ├── interceptors/              # HTTP interceptors
│   │   └── error.interceptor.ts   # Global error handling
│   ├── modules/
│   │   ├── documents/             # Document management
│   │   │   ├── uploader/          # File upload component
│   │   │   ├── document-list/     # Document listing
│   │   │   └── document-details/  # Document preview modal
│   │   └── search/                # Search interface
│   │       ├── search.component.ts
│   │       ├── search.component.html
│   │       └── search.component.scss
│   ├── shared/                    # Shared components/utilities
│   ├── app.component.*            # Root component
│   └── app.module.ts              # Main module
├── environments/                  # Environment configs
│   ├── environment.ts             # Development config
│   └── environment.prod.ts        # Production config
└── styles.scss                   # Global styles
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
- Real-time search with 300ms debouncing
- Smart context-aware suggestions
- Match highlighting with `<mark>` tags
- Sort by relevance score or date
- Pagination with configurable page sizes
- Search performance metrics display
- Empty state and no-results messaging

### DocumentDetailsComponent
- Modal dialog for document preview
- Full content display
- Download functionality

## API Integration

The frontend communicates with the PHP backend through these endpoints:

| Method | Endpoint | Purpose |
|--------|----------|----------|
| POST | `/api/documents/upload` | Upload document |
| GET | `/api/documents` | List documents with pagination |
| GET | `/api/documents/{id}` | Get document details |
| DELETE | `/api/documents/{id}` | Delete document |
| GET | `/api/documents/{id}/download` | Download document file |
| GET | `/api/search` | Search documents (with sort, pagination) |
| GET | `/api/suggestions` | Get contextual search suggestions |

All API calls include:
- Error handling with RxJS operators
- 30-second timeout for search operations
- Automatic retry logic for failed requests

## Styling

The application uses:
- **Angular Material 16**: Core UI components (cards, buttons, inputs, dialogs)
- **Custom SCSS**: Component-specific styling with BEM methodology
- **Responsive Design**: Mobile-first approach with flexbox/grid
- **Material Icons**: Consistent iconography throughout
- **Color Scheme**: Material Design color palette
- **Typography**: Roboto font family

## Key Features Explained

### Search Functionality
- **Debounced Input**: 300ms delay prevents excessive API calls
- **Minimum Query Length**: Requires 2+ characters to trigger search
- **Immediate Suggestion Search**: Clicking a suggestion immediately performs search
- **Highlighted Matches**: Search terms highlighted in result previews
- **Relevance Scoring**: Shows percentage match for relevance-sorted results

### Smart Suggestions
- Displays contextual phrases from document content
- Updates in real-time as you type
- Clicking a suggestion immediately searches for that term
- Suggestions cleared after selection
- Only shows when there's an active search query

### Document Upload
- Drag-and-drop interface
- File type validation (PDF and TXT only)
- Size limit enforcement (10MB default)
- Progress indication
- Success/error notifications

## Development

### Running Tests
```bash
# Unit tests
npm test

```

### Building for Production
```bash
npm run build
# Output in dist/ directory
# Configure environment.prod.ts with production API URL
```

### Code Quality
```bash
# Lint TypeScript
ng lint

# Format code
npm run format
```

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### API Connection Issues
- Verify backend is running on `http://localhost:8000`
- Check `environment.ts` has correct `apiUrl`
- Ensure CORS is configured in backend `.env`

### Search Not Working
- Check browser console for errors
- Verify minimum 2-character query length
- Ensure backend search endpoint is accessible

### Upload Failures
- Check file size (max 10MB by default)
- Verify file type is supported
- Check backend upload permissions

## Performance Optimization

- Search results cached on backend (5-minute TTL)
- Debounced search input reduces API calls
- Lazy loading for document list
- OnPush change detection strategy (where applicable)
- Production build with AOT compilation
