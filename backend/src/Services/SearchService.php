<?php

namespace App\Services;

use PDO;

class SearchService
{
    private PDO $db;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    public function search(string $query, string $sortBy = 'relevance', int $page = 1, int $limit = 10): array
    {
        $startTime = microtime(true);
        
        if (empty(trim($query))) {
            return [
                'results' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => 0,
                'searchTime' => 0
            ];
        }
        
        $offset = ($page - 1) * $limit;
        
        // Use MySQL FULLTEXT search for better performance
        $orderClause = $sortBy === 'date' ? 'created_at DESC' : 'relevance DESC';
        
        $sql = "SELECT 
                    id, 
                    filename, 
                    original_filename, 
                    file_size, 
                    file_type, 
                    created_at,
                    MATCH(content_text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance,
                    SUBSTRING(content_text, 1, 500) as preview
                FROM documents 
                WHERE MATCH(content_text) AGAINST(? IN NATURAL LANGUAGE MODE)
                ORDER BY $orderClause
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query, $query, $limit, $offset]);
        $results = $stmt->fetchAll();
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total 
                     FROM documents 
                     WHERE MATCH(content_text) AGAINST(? IN NATURAL LANGUAGE MODE)";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute([$query]);
        $total = $countStmt->fetch()['total'];
        
        // Highlight matches in preview
        foreach ($results as &$result) {
            $result['preview'] = $this->highlightMatches($result['preview'], $query);
        }
        
        $endTime = microtime(true);
        $searchTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
        
        return [
            'results' => $results,
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit),
            'searchTime' => $searchTime
        ];
    }
    
    public function getSuggestions(string $query, int $limit = 5): array
    {
        if (strlen($query) < 2) {
            return [];
        }
        
        $sql = "SELECT DISTINCT original_filename 
                FROM documents 
                WHERE original_filename LIKE ? 
                   OR MATCH(content_text) AGAINST(? IN NATURAL LANGUAGE MODE)
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$query%", $query, $limit]);
        
        return array_column($stmt->fetchAll(), 'original_filename');
    }
    
    private function highlightMatches(string $text, string $query): string
    {
        $words = explode(' ', $query);
        
        foreach ($words as $word) {
            if (strlen($word) > 2) { // Only highlight words longer than 2 chars
                $text = preg_replace(
                    '/(' . preg_quote($word, '/') . ')/i',
                    '<mark>$1</mark>',
                    $text
                );
            }
        }
        
        return $text;
    }
    
    public function getDocumentContent(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, original_filename, content_text, file_type, created_at 
             FROM documents 
             WHERE id = ?'
        );
        $stmt->execute([$id]);
        
        return $stmt->fetch() ?: null;
    }
}
