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

    /**
     * @param string $query
     * @param string $sortBy
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function search(string $query, string $sortBy = 'relevance', int $page = 1, int $limit = 10): array
    {
        $startTime = microtime(true);
        
        $cacheKey = "search:" . md5($query . $sortBy . $page . $limit);
        
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
        
        $words = array_filter(array_map('trim', explode(' ', $query)));
        $hasShortWords = false;
        $allWordsShort = true;

        foreach ($words as $word) {
            if (strlen($word) < 4) {
                $hasShortWords = true;
            } else {
                $allWordsShort = false;
            }
        }
        
        // Use LIKE search for short words or phrases, otherwise use FULLTEXT
        if ($hasShortWords || strlen($query) < 4) {
            $orderClause = $sortBy === 'date' ? 'created_at DESC' : 'created_at DESC';
            
            // Build LIKE conditions for better matching
            $likeConditions = [];
            $likeParams = [];
            
            // Search in content
            $likeConditions[] = 'content_text LIKE ?';
            $likeParams[] = '%' . $query . '%';
            
            // Search in filename
            $likeConditions[] = 'original_filename LIKE ?';
            $likeParams[] = '%' . $query . '%';
            
            // Also search for individual words if a query has multiple words
            if (count($words) > 1) {
                foreach ($words as $word) {
                    if (strlen($word) >= 2) {
                        $likeConditions[] = 'content_text LIKE ?';
                        $likeParams[] = '%' . $word . '%';
                    }
                }
            }
            
            $whereClause = implode(' OR ', $likeConditions);
            
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
                    WHERE $whereClause
                    ORDER BY $orderClause
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge($likeParams, [$limit, $offset]));
            $results = $stmt->fetchAll();
            
            $countSql = "SELECT COUNT(*) as total FROM documents WHERE $whereClause";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($likeParams);
        } else {
            // Use FULLTEXT search for longer queries
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
                       OR content_text LIKE ?
                    ORDER BY $orderClause
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$query, $query, '%' . $query . '%', '%' . $query . '%', $limit, $offset]);
            $results = $stmt->fetchAll();
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                         FROM documents 
                         WHERE MATCH(content_text) AGAINST(? IN NATURAL LANGUAGE MODE)
                            OR original_filename LIKE ?
                            OR content_text LIKE ?";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute([$query, '%' . $query . '%', '%' . $query . '%']);
        }
        $total = $countStmt->fetch()['total'];

        // Get context-aware preview and highlight matches
        foreach ($results as &$doc) {
            // Get full content to find match context
            $fullContent = $this->getFullContent($doc['id']);
            $doc['preview'] = $this->getContextPreview($fullContent, $query);
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

    /**
     * @param int $id
     * @return string
     */
    private function getFullContent(int $id): string
    {
        $stmt = $this->db->prepare('SELECT content_text FROM documents WHERE id = ?');
        $stmt->execute([$id]);
        $doc = $stmt->fetch();
        return $doc ? $doc['content_text'] : '';
    }

    /**
     * @param string $content
     * @param string $query
     * @return string
     */
    private function getContextPreview(string $content, string $query): string
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
        
        // If no match found, the return beginning of content
        if ($firstMatchPos === false) {
            return substr($content, 0, 500);
        }
        
        // Calculate start position to center the match
        $contextStart = max(0, $firstMatchPos - (int)(500 / 3));
        
        // Extract preview
        $preview = substr($content, $contextStart, 500);
        
        // Add ellipsis if we're not at the start
        if ($contextStart > 0) {
            $preview = '...' . $preview;
        }
        
        // Add ellipsis if there's more content
        if ($contextStart + 500 < strlen($content)) {
            $preview .= '...';
        }
        
        return $preview;
    }

    /**
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function getSuggestions(string $query, int $limit = 5): array
    {
        if (strlen($query) < 2) {
            return [];
        }
        
        // Get documents that match the query
        $sql = "SELECT content_text, original_filename
                FROM documents 
                WHERE original_filename LIKE ? 
                   OR content_text LIKE ?
                   OR MATCH(content_text) AGAINST(? IN NATURAL LANGUAGE MODE)
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$query%", "%$query%", $query, $limit * 3]);
        $documents = $stmt->fetchAll();
        
        $suggestions = [];
        $queryLower = strtolower($query);
        
        foreach ($documents as $doc) {
            // Extract words/phrases from content that contain the query
            $content = $doc['content_text'];
            $filename = $doc['original_filename'];
            
            // Split content into sentences
            $sentences = preg_split('/[.!?\n]+/', $content);
            
            foreach ($sentences as $sentence) {
                $sentence = trim($sentence);
                if (empty($sentence)) continue;
                
                // Check if sentence contains the query
                if (stripos($sentence, $query) !== false) {
                    // Extract words around the query match
                    $words = preg_split('/\s+/', $sentence);
                    
                    // Find the matching word index
                    foreach ($words as $index => $word) {
                        if (stripos($word, $query) !== false) {
                            // Get 2-4 words context
                            $start = max(0, $index - 1);
                            $end = min(count($words), $index + 3);
                            $phrase = implode(' ', array_slice($words, $start, $end - $start));
                            
                            // Clean up the phrase
                            $phrase = preg_replace('/[^a-zA-Z0-9\s-]/', '', $phrase);
                            $phrase = trim($phrase);
                            
                            if (strlen($phrase) >= 3 && strlen($phrase) <= 50) {
                                $suggestions[] = $phrase;
                            }
                            
                            if (count($suggestions) >= $limit) {
                                break 3; // Break all loops
                            }
                        }
                    }
                }
            }
            
            // Also add filename-based suggestions if they match
            $filenameParts = preg_split('/[._\-\s]+/', pathinfo($filename, PATHINFO_FILENAME));
            foreach ($filenameParts as $part) {
                if (strlen($part) >= 3 && stripos($part, $query) !== false) {
                    $suggestions[] = $part;
                    if (count($suggestions) >= $limit) {
                        break 2;
                    }
                }
            }
        }
        
        // Remove duplicates and return unique suggestions
        $suggestions = array_unique($suggestions);
        return array_values(array_slice($suggestions, 0, $limit));
    }

    /**
     * @param string $text
     * @param string $query
     * @return string
     */
    private function highlightMatches(string $text, string $query): string
    {
        $words = explode(' ', $query);
        
        foreach ($words as $word) {
            if (strlen($word) > 2) {
                $text = preg_replace(
                    '/(' . preg_quote($word, '/') . ')/i',
                    '<mark>$1</mark>',
                    $text
                );
            }
        }
        
        return $text;
    }

    /**
     * @param int $id
     * @return array|null
     */
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
