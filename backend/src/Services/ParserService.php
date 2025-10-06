<?php

namespace App\Services;

use Exception;
use Smalot\PdfParser\Parser as PdfParser;

class ParserService
{
    /**
     * @param string $filePath
     * @param string $mimeType
     * @return string
     */
    public function parseFile(string $filePath, string $mimeType): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (empty($extension)) {
            $extension = $this->getExtensionFromMimeType($mimeType);
        }

        try {
            return match ($extension) {
                'pdf' => $this->parsePdf($filePath),
                'txt' => $this->parseTxt($filePath),
                default => throw new Exception("Unsupported file type: '$extension' (MIME: $mimeType)"),
            };
        } catch (Exception $e) {
            return "Content extraction failed: " . $e->getMessage();
        }
    }

    /**
     * @param string $mimeType
     * @return string
     */
    private function getExtensionFromMimeType(string $mimeType): string
    {
        $mimeMap = [
            'application/pdf' => 'pdf',
            'text/plain' => 'txt',
        ];
        
        return $mimeMap[$mimeType] ?? '';
    }

    /**
     * @param string $filePath
     * @return string
     * @throws Exception
     */
    private function parsePdf(string $filePath): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            
            if (empty(trim($text))) {
                return "PDF parsed successfully but no text content was found. This may be a scanned PDF or image-based PDF.";
            }
            
            return $this->cleanText($text);
        } catch (Exception $e) {
            throw new Exception("PDF parsing failed: " . $e->getMessage());
        }
    }

    /**
     * @param string $filePath
     * @return string
     */
    private function parseTxt(string $filePath): string
    {
        $text = file_get_contents($filePath);
        return $this->cleanText($text);
    }

    /**
     * @param string $text
     * @return string
     */
    private function cleanText(string $text): string
    {
        // Remove control characters but preserve line breaks and spaces
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        // Normalize line breaks to \n
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        // Remove excessive blank lines (more than 2 consecutive)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        // Trim
        $text = trim($text);
        
        return $text;
    }

    /**
     * @param array $file
     * @return void
     * @throws Exception
     */
    public function validateFile(array $file): void
    {
        $allowedExtensions = explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'pdf,txt');
        $maxFileSize = (int)($_ENV['MAX_FILE_SIZE'] ?? 10485760);
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception("File type not allowed. Allowed types: " . implode(', ', $allowedExtensions));
        }
        
        if ($file['size'] > $maxFileSize) {
            throw new Exception("File size exceeds maximum allowed size of " . ($maxFileSize / 1048576) . "MB");
        }
    }
}
