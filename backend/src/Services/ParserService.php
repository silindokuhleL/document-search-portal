<?php

namespace App\Services;

use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory;

class ParserService
{
    public function parseFile(string $filePath, string $mimeType): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        try {
            switch ($extension) {
                case 'pdf':
                    return $this->parsePdf($filePath);
                case 'doc':
                case 'docx':
                    return $this->parseWord($filePath);
                case 'txt':
                    return $this->parseTxt($filePath);
                default:
                    throw new \Exception("Unsupported file type: $extension");
            }
        } catch (\Exception $e) {
            error_log("Parse error for $filePath: " . $e->getMessage());
            return '';
        }
    }
    
    private function parsePdf(string $filePath): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();
        
        return $this->cleanText($text);
    }
    
    private function parseWord(string $filePath): string
    {
        $phpWord = IOFactory::load($filePath);
        $text = '';
        
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                } elseif (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $childElement) {
                        if (method_exists($childElement, 'getText')) {
                            $text .= $childElement->getText() . "\n";
                        }
                    }
                }
            }
        }
        
        return $this->cleanText($text);
    }
    
    private function parseTxt(string $filePath): string
    {
        $text = file_get_contents($filePath);
        return $this->cleanText($text);
    }
    
    private function cleanText(string $text): string
    {
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        // Remove control characters
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        // Trim
        $text = trim($text);
        
        return $text;
    }
    
    public function validateFile(array $file): void
    {
        $allowedExtensions = explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'pdf,doc,docx,txt');
        $maxFileSize = (int)($_ENV['MAX_FILE_SIZE'] ?? 10485760); // 10MB default
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('File upload error: ' . $file['error']);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception("File type not allowed. Allowed types: " . implode(', ', $allowedExtensions));
        }
        
        if ($file['size'] > $maxFileSize) {
            throw new \Exception("File size exceeds maximum allowed size of " . ($maxFileSize / 1048576) . "MB");
        }
    }
}
