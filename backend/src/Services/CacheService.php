<?php

namespace App\Services;

use Exception;

class CacheService
{
    private string $cacheDir;
    private int $defaultTtl = 3600; // 1 hour

    public function __construct()
    {
        $this->cacheDir = __DIR__ . '/../../cache';
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get cached value
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key): mixed
    {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }

        $data = file_get_contents($filename);
        $cache = unserialize($data);

        // Check if expired
        if ($cache['expires_at'] < time()) {
            unlink($filename);
            return null;
        }

        return $cache['value'];
    }

    /**
     * Set cache value
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl Time to live in seconds
     * @return bool
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $filename = $this->getCacheFilename($key);

        $cache = [
            'value' => $value,
            'expires_at' => time() + $ttl,
            'created_at' => time()
        ];

        return file_put_contents($filename, serialize($cache)) !== false;
    }

    /**
     * Delete cached value
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $filename = $this->getCacheFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }

    /**
     * Clear all cache
     * @return void
     */
    public function clear(): void
    {
        $files = glob($this->cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Get cache filename for key
     * @param string $key
     * @return string
     */
    private function getCacheFilename(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }

    /**
     * Remember: Get from cache or execute callback and cache result
     * @param string $key
     * @param callable $callback
     * @param int|null $ttl
     * @return mixed
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
}
