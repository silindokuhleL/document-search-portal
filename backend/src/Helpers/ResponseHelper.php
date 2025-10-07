<?php

namespace App\Helpers;

class ResponseHelper
{
    /**
     * Send a successful JSON response
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return void
     */
    public static function success($data = null, string $message = 'Success', int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send an error JSON response
     * @param string $message
     * @param int $code
     * @param array|null $errors
     * @return void
     */
    public static function error(string $message, int $code = 400, ?array $errors = null): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        // Only include stack trace in development
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development' && $errors !== null) {
            $response['debug'] = $errors;
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send a not found response
     * @param string $message
     * @return void
     */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404);
    }
    
    /**
     * Send a validation error response
     * @param string $message
     * @param array $errors
     * @return void
     */
    public static function validationError(string $message = 'Validation failed', array $errors = []): void
    {
        self::error($message, 422, $errors);
    }
    
    /**
     * Send a server error response
     * @param string $message
     * @param \Throwable|null $exception
     * @return void
     */
    public static function serverError(string $message = 'Internal server error', ?\Throwable $exception = null): void
    {
        // Log the exception
        if ($exception !== null) {
            error_log("Server Error: " . $exception->getMessage());
            error_log("Stack trace: " . $exception->getTraceAsString());
        }
        
        // Don't expose internal errors in production
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') {
            self::error('An unexpected error occurred. Please try again later.', 500);
        } else {
            $errors = $exception ? [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ] : null;
            
            self::error($message, 500, $errors);
        }
    }
    
    /**
     * Send an unauthorized response
     * @param string $message
     * @return void
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }
    
    /**
     * Send a forbidden response
     * @param string $message
     * @return void
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 403);
    }
    
    /**
     * Send a created response
     * @param mixed $data
     * @param string $message
     * @return void
     */
    public static function created($data = null, string $message = 'Resource created successfully'): void
    {
        self::success($data, $message, 201);
    }
    
    /**
     * Send a no content response
     * @return void
     */
    public static function noContent(): void
    {
        http_response_code(204);
        exit;
    }
}
