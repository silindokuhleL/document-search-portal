# Video Demo Script (5-10 minutes)

## Introduction (30 seconds)

"Hi! Today I'll be demonstrating the Document Search application I built - a full-stack solution for uploading, managing, and searching documents using PHP and Angular."

"This application allows users to upload documents in various formats, extract their content, and perform full-text search with real-time suggestions and highlighted results."

## Application Demo (3-4 minutes)

### 1. Document Upload (1 minute)

"Let's start by uploading some documents."

**Actions:**
- Navigate to Documents tab
- Show drag-and-drop area
- Drag a PDF file and drop it
- Show upload progress
- Upload a Word document using the browse button
- Upload a text file
- Show success messages

"As you can see, the application supports PDF, Word documents, and text files. The drag-and-drop interface makes it easy to upload files, and we get immediate feedback on the upload status."

### 2. Document Management (1 minute)

"Now let's look at the document management features."

**Actions:**
- Show the document list with pagination
- Point out file icons, sizes, and dates
- Click view icon to show document details modal
- Show the content preview
- Download a document
- Delete a document with confirmation
- Show pagination controls

"The document list provides a clean overview with pagination. We can view document details, download files, and delete documents with confirmation to prevent accidents."

### 3. Search Functionality (2 minutes)

"The real power of this application is in the search functionality."

**Actions:**
- Go to Search tab
- Start typing a search query slowly
- Show auto-suggestions appearing
- Select a suggestion
- Show search results with:
  - Highlighted matches
  - Relevance scores
  - Performance metrics (search time)
- Change sort from "Relevance" to "Date"
- Show results reordering
- Try different search terms
- Show pagination in search results
- Download a document from search results

"The search is debounced, so it waits for you to finish typing before querying. Notice the highlighted matches in the preview - this makes it easy to see why a document matched your search. The search is fast, typically under 100 milliseconds, and you can sort by relevance or date."

## Code Walkthrough (3-4 minutes)

### 1. Backend Architecture (1.5 minutes)

"Let me show you the backend architecture."

**Show:**
- Project structure in IDE
- `backend/src/Services/` folder

"The backend uses a service-oriented architecture with three main services:"

**StorageService.php:**
```php
// Show saveDocument method
"This handles file storage and database persistence using PDO prepared statements for security."
```

**ParserService.php:**
```php
// Show parseFile method
"The parser service extracts text from different file formats using specialized libraries - smalot/pdfparser for PDFs and phpoffice/phpword for Word documents."
```

**SearchService.php:**
```php
// Show search method with FULLTEXT
"For search, I'm using MySQL's FULLTEXT indexing in NATURAL LANGUAGE MODE, which provides built-in relevance scoring and good performance for moderate document volumes."
```

**DocumentController.php:**
```php
// Show upload and search methods
"The controller orchestrates these services and handles HTTP requests."
```

### 2. Frontend Architecture (1.5 minutes)

"On the frontend, I'm using Angular with a component-based architecture."

**Show:**
- `frontend/src/app/modules/` structure

**UploaderComponent:**
```typescript
// Show drag-and-drop handlers
"The uploader component implements native HTML5 drag-and-drop with visual feedback."
```

**SearchComponent:**
```typescript
// Show RxJS reactive search
"The search uses RxJS operators for a reactive implementation - debounceTime to wait 300ms after typing, distinctUntilChanged to avoid duplicate queries, and switchMap to cancel previous requests."
```

**Services:**
```typescript
// Show DocumentService and SearchService
"The services encapsulate all HTTP communication with the backend API."
```

## Design Decisions & Trade-offs (2 minutes)

### 1. MySQL FULLTEXT vs Elasticsearch

"For search, I chose MySQL FULLTEXT indexing over Elasticsearch."

**Pros:**
- "No additional infrastructure needed"
- "Built-in relevance scoring"
- "Fast for moderate volumes"

**Cons:**
- "Limited to about 1 million documents efficiently"
- "Minimum word length restrictions"

"For this application's scope, MySQL FULLTEXT provides the right balance of simplicity and performance. If we needed to scale beyond a million documents, migrating to Elasticsearch would be the next step."

### 2. File Storage

"I'm using local filesystem storage with database metadata."

**Trade-off:**
- "Simple and fast for development"
- "Would migrate to S3 or cloud storage for production to support distributed systems"

### 3. No PHP Framework

"I built the backend without a major framework like Laravel."

**Rationale:**
- "Demonstrates understanding of core concepts"
- "Lightweight and fast"
- "Full control over architecture"

"This shows I can build from fundamentals, though for a larger production app, I'd likely use Laravel for its ecosystem and built-in features."

## Interesting Technical Implementations (1 minute)

### 1. Custom Router

```php
// Show Router.php regex pattern matching
"I built a custom router using regex pattern matching for clean RESTful routes. It converts route patterns like '/api/documents/{id}' into regex patterns for matching."
```

### 2. Search Result Highlighting

```php
// Show highlightMatches method
"The backend wraps search matches in <mark> tags, and the frontend renders them safely using Angular's innerHTML binding. This provides visual feedback on why documents matched."
```

### 3. Reactive Search

```typescript
// Show RxJS pipeline again
"The reactive search implementation is elegant - it combines debouncing, deduplication, and request cancellation in just a few lines of code."
```

## Conclusion (30 seconds)

"This application demonstrates:"
- "Full-stack development with PHP and Angular"
- "RESTful API design"
- "Document parsing and full-text search"
- "Modern UI/UX with Material Design"
- "Clean architecture and separation of concerns"

"The codebase is well-documented with a comprehensive README, and I've included a SOLUTION.md that details all the design decisions and trade-offs."

"Thanks for watching!"

---

## Demo Tips

1. **Preparation:**
   - Have 3-4 test documents ready (PDF, DOCX, TXT)
   - Ensure backend and frontend are running
   - Clear any test data for a clean demo
   - Have IDE open with key files ready to show

2. **Screen Setup:**
   - Browser with application (main screen)
   - IDE with code (secondary screen or tab)
   - Terminal with servers running (background)

3. **Pacing:**
   - Speak clearly and not too fast
   - Pause briefly after each feature demo
   - Show, don't just tell - demonstrate features

4. **Recording Tools:**
   - Loom (recommended)
   - OBS Studio
   - QuickTime (Mac)
   - Windows Game Bar (Windows)

5. **Audio:**
   - Use a good microphone
   - Minimize background noise
   - Test audio before recording
