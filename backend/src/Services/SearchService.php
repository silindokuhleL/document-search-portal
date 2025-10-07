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
                       OR original_filename LIKE ?
                    ORDER BY $orderClause
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$query, $query, '%' . $query . '%', $limit, $offset]);
            $results = $stmt->fetchAll();
            
            // Get total count for FULLTEXT search
            $countSql = "SELECT COUNT(*) as total 
                         FROM documents 
                         WHERE MATCH(content_text) AGAINST(? IN NATURAL LANGUAGE MODE)
                            OR original_filename LIKE ?";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute([$query, '%' . $query . '%']);
            $total = $countStmt->fetch()['total'];
        }
        
        // Get context-aware preview and highlight matches
        foreach ($results as &$doc) {
            // Get full content to find match context
            $fullContent = $this->getFullContent($doc['id']);
            $doc['preview'] = $this->getContextPreview($fullContent, $query, 500);
            $doc['preview'] = $this->highlightMatches($doc['preview'], $query);
        }
        unset($doc); // Break the reference
        
        $endTime = microtime(true);
        $searchTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
        
        $response = [
            'results' => $results,
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit),
            'searchTime' => $searchTime,
            'cached' => false
        ];
        
        // Cache the result for 5 minutes (300 seconds)
        $this->cache->set($cacheKey, $response, 300);
        
        return $response;
    }
    
    private function getFullContent(int $id): string
    {
        $stmt = $this->db->prepare('SELECT content_text FROM documents WHERE id = ?');
        $stmt->execute([$id]);
        $doc = $stmt->fetch();
        return $doc ? $doc['content_text'] : '';
    }
    
    private function getContextPreview(string $content, string $query, int $maxLength = 500): string
    {
        if (empty($content)) {
            return '';
        }
        
        // Find the position of the first occurrence of any word in the query
        $queryWords = explode(' ', strtolower($query));
        $contentLower = strtolower($content);
        $firstMatchPos = false;
        
        foreach ($queryWords as $word) {
            $word = trim($word);
            if (strlen($word) > 0) {
                $pos = strpos($contentLower, $word);
                if ($pos !== false && ($firstMatchPos === false || $pos < $firstMatchPos)) {
                    $firstMatchPos = $pos;
                }
            }
        }
        
        // If no match found, return beginning of content
        if ($firstMatchPos === false) {
            return substr($content, 0, $maxLength);
        }
        
        // Calculate start position to center the match
        $contextStart = max(0, $firstMatchPos - (int)($maxLength / 3));
        
        // Extract preview
        $preview = substr($content, $contextStart, $maxLength);
        
        // Add ellipsis if we're not at the start
        if ($contextStart > 0) {
            $preview = '...' . $preview;
        }
        
        // Add ellipsis if there's more content
        if ($contextStart + $maxLength < strlen($content)) {
            $preview .= '...';
        }
        
        return $preview;
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
