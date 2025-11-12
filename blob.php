<?php
// blob.php - Secure file server with Bearer Token Authentication

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Bearer');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

class SecureBlobServer {
    private $uploadDir;
    private $bearerToken;
    
    public function __construct($uploadDir = 'uploads/', $bearerToken = null) {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->bearerToken = $bearerToken;
    }
    
    public function authenticate() {
        if (!$this->bearerToken) {
            return true; // No authentication required
        }
        
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if (!$authHeader) {
            return false;
        }
        
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return false;
        }
        
        return $matches[1] === $this->bearerToken;
    }
    
    public function getMetadata($blobId) {
        $metaFile = $this->uploadDir . $blobId . '.meta';
        if (!file_exists($metaFile)) {
            return null;
        }
        return json_decode(file_get_contents($metaFile), true);
    }
    
    private function isValidBlobId($blobId) {
        return preg_match('/^[a-f0-9]{32}\.[a-z0-9]+$/i', $blobId) && 
               strpos($blobId, '..') === false;
    }
    
    public function serveBlob($blobId) {
        $filepath = $this->uploadDir . $blobId;
        
        // Security check
        if (!$this->isValidBlobId($blobId) || !file_exists($filepath)) {
            http_response_code(404);
            echo json_encode(['error' => 'Blob not found', 'success' => false]);
            exit;
        }
        
        $metadata = $this->getMetadata($blobId);
        if (!$metadata) {
            http_response_code(404);
            echo json_encode(['error' => 'Blob metadata not found', 'success' => false]);
            exit;
        }
        
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set appropriate headers for file serving (not JSON)
        header('Content-Type: ' . $metadata['type']);
        header('Content-Length: ' . filesize($filepath));
        header('Content-Disposition: inline; filename="' . addslashes($metadata['original_name']) . '"');
        header('Cache-Control: public, max-age=31536000');
        header('ETag: "' . $metadata['hash'] . '"');
        
        // Handle conditional requests
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && 
            $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $metadata['hash'] . '"') {
            http_response_code(304);
            exit;
        }
        
        // Serve file
        readfile($filepath);
        exit;
    }
}

// Configuration - MUST MATCH blob_manager.php
$bearerToken = 'your-secure-bearer-token-here-change-this'; // CHANGE THIS!

$blobServer = new SecureBlobServer('uploads/', $bearerToken);

// Authenticate requests
if (!$blobServer->authenticate()) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Unauthorized. Bearer token required.',
        'success' => false
    ]);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Blob ID required',
        'success' => false,
        'usage' => 'Use: blob.php?id=YOUR_BLOB_ID'
    ]);
    exit;
}

$blobServer->serveBlob($_GET['id']);

?>