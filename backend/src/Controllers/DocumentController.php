<?php

namespace App\Controllers;

use App\Services\StorageService;
use App\Services\ParserService;
use App\Services\SearchService;

class DocumentController
{
    private StorageService $storageService;
    private ParserService $parserService;
    private SearchService $searchService;
    
    public function __construct(
        StorageService $storageService,
        ParserService $parserService,
        SearchService $searchService
    ) {
        $this->storageService = $storageService;
        $this->parserService = $parserService;
        $this->searchService = $searchService;
    }
    
    public function upload(): void
    {
        try {
            if (!isset($_FILES['file'])) {
                errorResponse('No file uploaded', 400);
            }
            
            $file = $_FILES['file'];
            
            // Validate file
            $this->parserService->validateFile($file);
            
            // Parse content
            $content = $this->parserService->parseFile($file['tmp_name'], $file['type']);
            
            // Save document
            $document = $this->storageService->saveDocument($file, $content);
            
            jsonResponse([
                'message' => 'Document uploaded successfully',
                'document' => $document
            ], 201);
            
        } catch (\Exception $e) {
            errorResponse($e->getMessage(), 400);
        }
    }
    
    public function list(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 10);
        
        $result = $this->storageService->getAllDocuments($page, $limit);
        jsonResponse($result);
    }
    
    public function get(string $id): void
    {
        $document = $this->storageService->getDocumentById((int)$id);
        
        if (!$document) {
            errorResponse('Document not found', 404);
        }
        
        jsonResponse($document);
    }
    
    public function delete(string $id): void
    {
        $success = $this->storageService->deleteDocument((int)$id);
        
        if (!$success) {
            errorResponse('Document not found', 404);
        }
        
        jsonResponse(['message' => 'Document deleted successfully']);
    }
    
    public function search(): void
    {
        $query = $_GET['q'] ?? '';
        $sortBy = $_GET['sort'] ?? 'relevance';
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 10);
        
        $result = $this->searchService->search($query, $sortBy, $page, $limit);
        jsonResponse($result);
    }
    
    public function suggestions(): void
    {
        $query = $_GET['q'] ?? '';
        $limit = (int)($_GET['limit'] ?? 5);
        
        $suggestions = $this->searchService->getSuggestions($query, $limit);
        jsonResponse(['suggestions' => $suggestions]);
    }
    
    public function download(string $id): void
    {
        $filePath = $this->storageService->getFilePath((int)$id);
        
        if (!$filePath || !file_exists($filePath)) {
            errorResponse('File not found', 404);
        }
        
        $document = $this->storageService->getDocumentById((int)$id);
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $document['original_filename'] . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    }
}
