<?php

namespace App\Controllers;

use App\Services\ParserService;
use App\Services\SearchService;
use App\Services\StorageService;

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

    /**
     * @return void
     */
    public function upload(): void
    {
        try {
            if (!isset($_FILES['file'])) {
                errorResponse('No file uploaded', 400);
            }
            
            $file = $_FILES['file'];
            
            $this->parserService->validateFile($file);
            
            $content = $this->parserService->parseFile($file['tmp_name'], $file['type']);
            
            $document = $this->storageService->saveDocument($file, $content);
            
            jsonResponse([
                'message' => 'Document uploaded successfully',
                'document' => $document
            ], 201);
            
        } catch (\Exception $e) {
            errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * @return void
     */
    public function list(): void
    {
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 10);
        
        $result = $this->storageService->getAllDocuments($page, $limit);
        jsonResponse($result);
    }

    /**
     * @param string $id
     * @return void
     */
    public function get(string $id): void
    {
        $document = $this->storageService->getDocumentById((int)$id);
        
        if (!$document) {
            errorResponse('Document not found', 404);
        }
        
        jsonResponse($document);
    }

    /**
     * @param string $id
     * @return void
     */
    public function delete(string $id): void
    {
        $success = $this->storageService->deleteDocument((int)$id);
        
        if (!$success) {
            errorResponse('Document not found', 404);
        }
        
        jsonResponse(['message' => 'Document deleted successfully']);
    }

    /**
     * @return void
     */
    public function search(): void
    {
        $query = $_GET['q'] ?? '';
        $sortBy = $_GET['sort'] ?? 'relevance';
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 10);
        
        $result = $this->searchService->search($query, $sortBy, $page, $limit);
        jsonResponse($result);
    }

    /**
     * @return void
     */
    public function suggestions(): void
    {
        $query = $_GET['q'] ?? '';
        $limit = (int)($_GET['limit'] ?? 5);
        
        $suggestions = $this->searchService->getSuggestions($query, $limit);
        jsonResponse(['suggestions' => $suggestions]);
    }

    /**
     * @param string $id
     * @return void
     */
    public function download(string $id): void
    {
        try {
            $filePath = $this->storageService->getFilePath((int)$id);
            
            if (!$filePath || !file_exists($filePath)) {
                errorResponse('File not found', 404);
            }
            
            $document = $this->storageService->getDocumentById((int)$id);
            
            if (!$document) {
                errorResponse('Document not found', 404);
            }
            
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $contentType = match($extension) {
                'pdf' => 'application/pdf',
                'txt' => 'text/plain',
                'doc', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                default => 'application/octet-stream'
            };
            
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $document['original_filename'] . '"');
            header('Content-Length: ' . filesize($filePath));
            
            readfile($filePath);
            exit;
        } catch (\Exception $e) {
            error_log("Download error: " . $e->getMessage());
            errorResponse('Failed to download file: ' . $e->getMessage(), 500);
        }
    }
}
