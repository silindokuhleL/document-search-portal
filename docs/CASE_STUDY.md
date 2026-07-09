# Document Search Portal Case Study

## Portfolio Summary

Document Search Portal is a full-stack document management and search application built with a PHP REST API, MySQL FULLTEXT search, and an Angular frontend. It lets users upload PDF and TXT files, extract searchable content, search across documents, view highlighted matches, and manage uploaded files through a responsive interface.

## Problem

Teams often store important information inside scattered PDF and text files. Without a searchable interface, finding a policy, clause, instruction, or reference requires opening files manually and guessing where the right information lives.

## Solution

This project turns uploaded documents into searchable records. The backend parses document content, stores metadata and extracted text, indexes content in MySQL, and exposes REST endpoints for upload, search, suggestions, document listing, deletion, and download. The frontend gives users a practical interface for uploading, browsing, searching, sorting, and reading highlighted results.

## What I Built

- A PHP API with custom routing and service-oriented backend structure.
- Document upload validation for file type, file size, and storage safety.
- PDF and TXT parsing through backend parser services.
- MySQL FULLTEXT search with fallback matching for shorter queries.
- Context-aware suggestions based on document content.
- File-based search caching to reduce repeated lookup cost.
- Angular document upload, list, detail, and search workflows.
- Debounced search input, loading states, pagination, and error handling.
- Setup scripts and documentation for local installation and API testing.

## Architecture

```mermaid
flowchart LR
    U["User"] --> A["Angular frontend"]
    A -->|REST JSON| P["PHP API"]
    P --> R["Custom router"]
    R --> C["Document controller"]
    C --> S["Storage service"]
    C --> X["Parser service"]
    C --> Q["Search service"]
    Q --> K["Cache service"]
    S --> D["MySQL documents table"]
    Q --> D
    X --> F["Uploaded PDF/TXT files"]
```

## Technical Decisions

- **PHP without a large framework:** Keeps the API small and makes routing, services, and request handling easy to inspect.
- **Service layer:** Keeps parsing, storage, search, and cache logic out of controller methods.
- **MySQL FULLTEXT:** Provides search relevance without adding Elasticsearch or another search service.
- **LIKE fallback:** Improves short-query behavior when FULLTEXT minimum word length would be too restrictive.
- **Debounced Angular search:** Reduces unnecessary requests while keeping the UI responsive.
- **File cache:** Speeds up repeated searches and demonstrates practical performance thinking.

## Stack

- PHP 8
- MySQL / MariaDB
- PDO
- Composer
- `smalot/pdfparser`
- Angular 16
- TypeScript
- Angular Material
- RxJS

## Proof To Capture

- Screenshot of the document upload flow.
- Screenshot of the document list/detail flow.
- Screenshot of search results with highlighted matches.
- Screenshot of suggestions while typing.
- API verification output from `backend/test-api.sh`.
- Frontend build/lint output.
- Optional short walkthrough video.

## Portfolio Positioning

This project proves that I can build useful backend-heavy product features, not only static interfaces. It shows REST API design, file handling, parsing, database search, caching, Angular integration, and practical documentation.
