<?php

// For PHP built-in server, route all requests through this file
if (php_sapi_name() === 'cli-server') {
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

require_once __DIR__ . '/../src/bootstrap.php';

use App\Helpers\Router;
use App\Controllers\DocumentController;
use App\Services\StorageService;
use App\Services\ParserService;
use App\Services\SearchService;

// Set CORS headers
setCorsHeaders();

// Initialize services
$db = getDbConnection();
$storageService = new StorageService($db);
$parserService = new ParserService();
$searchService = new SearchService($db);

// Initialize controller
$documentController = new DocumentController($storageService, $parserService, $searchService);

// Initialize router
$router = new Router();

// Define routes
$router->post('/api/documents/upload', [$documentController, 'upload']);
$router->get('/api/documents', [$documentController, 'list']);
$router->get('/api/documents/{id}', [$documentController, 'get']);
$router->delete('/api/documents/{id}', [$documentController, 'delete']);
$router->get('/api/search', [$documentController, 'search']);
$router->get('/api/suggestions', [$documentController, 'suggestions']);
$router->get('/api/documents/{id}/download', [$documentController, 'download']);

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Dispatch request
$router->dispatch($method, $uri);
