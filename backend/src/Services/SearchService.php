<?php

namespace App\Services;

use PDO;

class SearchService
{
    private PDO $db;
    private CacheService $cache;
    
    public function __construct(PDO $db, ?CacheService $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache ?? new CacheService();
    }
    
    public function search(string $query, string $sortBy = 'relevance', int $page = 1, int $limit = 10): array
    {
        $startTime = microtime(true);
        
        // Generate cache key
        $cacheKey = "search:" . md5($query . $sortBy . $page . $limit);
        
        // Try to get from cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            $cached['cached'] = true;
            $cached['searchTime'] = round((microtime(true) - $startTime) * 1000, 2);
            return $cached;
        }
        
        if (empty(trim($query))) {
            return [
                'results' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => 0,
                'searchTime' => 0,
                'cached' => false
            ];
        }
        
        $offset = ($page - 1) * $limit;
        
        // Check if query contains short words (< 4 chars) - MySQL FULLTEXT limitation
        $words = explode(' ', $query);
        $hasShortWords = false;
        foreach ($words as $word) {
            if (strlen(trim($word)) < 4 && strlen(trim($word)) > 0) {
                $hasShortWords = true;
                break;
            }
        }
        
        // Use LIKE fallback for short words, FULLTEXT for longer queries
        if ($hasShortWords) {
            // Fallback to LIKE search for short words
            $orderClause = $sortBy === 'date' ? 'created_at DESC' : 'created_at DESC';
            
            $sql = "SELECT 
                        id, 
                        filename, 
                        original_filename, 
                        file_size, 
                        file_type, 
                        created_at,
                        1 as relevance,
                        SUBSTRING(content_text, 1, 500) as preview
                    FROM documents 
                    WHERE content_text LIKE ?
                    ORDER BY $orderClause
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['%' . $query . '%', $limit, $offset]);
            $results = $stmt->fetchAll();
            
            // Get total count for LIKE search
            $countSql = "SELECT COUNT(*) as total FROM documents WHERE content_text LIKE ?";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute(['%' . $query . '%']);
            $total = $countStmt->fetch()['total'];
        } else {
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
            
            // Get total count for FULLTEXT search
            $countSql = "SELECT COUNT(*) as total 
                         FROM documents 
                         WHERE MATCH(content_text) AGAINST(? IN NATURAL LANGUAGE MODE)";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute([$query]);
            $total = $countStmt->fetch()['total'];
        }
        
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
