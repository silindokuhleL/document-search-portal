<?php

namespace App\Services;

use PDO;
use Exception;

class StorageService
{
    private PDO $db;
    private string $uploadDir;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->uploadDir = __DIR__ . '/../../' . ($_ENV['UPLOAD_DIR'] ?? 'uploads');
        
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * @param array $fileData
     * @param string $content
     * @return array
     * @throws Exception
     */
    public function saveDocument(array $fileData, string $content): array
    {
        $filename = $this->generateUniqueFilename($fileData['name']);
        $filePath = $this->uploadDir . '/' . $filename;
        
        if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
            throw new Exception('Failed to save file');
        }
        
        $stmt = $this->db->prepare(
            'INSERT INTO documents (filename, original_filename, file_path, file_size, file_type, content_text) 
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        
        $stmt->execute([
            $filename,
            $fileData['name'],
            $filePath,
            $fileData['size'],
            $fileData['type'],
            $content
        ]);
        
        $id = $this->db->lastInsertId();
        
        return $this->getDocumentById($id);
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function getDocumentById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM documents WHERE id = ?');
        $stmt->execute([$id]);
        $doc = $stmt->fetch();
        
        return $doc ?: null;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getAllDocuments(int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare(
            'SELECT id, filename, original_filename, file_size, file_type, created_at, updated_at 
             FROM documents 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?'
        );
        $stmt->execute([$limit, $offset]);
        $documents = $stmt->fetchAll();
        
        $countStmt = $this->db->query('SELECT COUNT(*) as total FROM documents');
        $total = $countStmt->fetch()['total'];
        
        return [
            'documents' => $documents,
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit)
        ];
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteDocument(int $id): bool
    {
        $doc = $this->getDocumentById($id);
        
        if (!$doc) {
            return false;
        }
        
        if (file_exists($doc['file_path'])) {
            unlink($doc['file_path']);
        }
        
        $stmt = $this->db->prepare('DELETE FROM documents WHERE id = ?');
        $stmt->execute([$id]);
        
        return true;
    }

    /**
     * @param string $originalName
     * @return string
     */
    private function generateUniqueFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        
        return $basename . '_' . time() . '_' . uniqid() . '.' . $extension;
    }

    /**
     * @param int $id
     * @return string|null
     */
    public function getFilePath(int $id): ?string
    {
        $doc = $this->getDocumentById($id);
        return $doc ? $doc['file_path'] : null;
    }
}
