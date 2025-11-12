<?php
// blob_manager.php - Updated with Bearer Token Authentication

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Bearer');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

class BlobManager {
    private $uploadDir;
    private $baseUrl;
    private $maxFileSize;
    private $allowedTypes;
    private $bearerToken;
    
    public function __construct($uploadDir = 'uploads/', $baseUrl = null, $maxFileSize = 10485760, $bearerToken = null) {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->baseUrl = $baseUrl ?: $this->getBaseUrl();
        $this->maxFileSize = $maxFileSize;
        $this->bearerToken = $bearerToken;
        $this->allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf', 'text/plain', 'application/json', 'text/csv',
            'video/mp4', 'video/webm', 'audio/mp3', 'audio/wav', 'audio/mpeg',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip', 'application/x-rar-compressed'
        ];
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        // Create security files
        $this->createSecurityFiles();
    }
    
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . '://' . $host . $script;
    }
    
    private function createSecurityFiles() {
        // Create .htaccess to prevent direct access
        $htaccessPath = $this->uploadDir . '.htaccess';
        if (!file_exists($htaccessPath)) {
            $content = "# Prevent direct access to uploaded files\n";
            $content .= "Order Deny,Allow\n";
            $content .= "Deny from all\n";
            $content .= "Options -Indexes\n";
            $content .= "<Files *.php>\n";
            $content .= "    Deny from all\n";
            $content .= "</Files>\n";
            file_put_contents($htaccessPath, $content);
        }
        
        // Create index.php to prevent directory listing
        $indexPath = $this->uploadDir . 'index.php';
        if (!file_exists($indexPath)) {
            file_put_contents($indexPath, "<?php http_response_code(403); die('Access denied'); ?>");
        }
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
    
    public function upload($file) {
        try {
            // Validate upload
            $validation = $this->validateUpload($file);
            if ($validation !== true) {
                return ['error' => $validation, 'success' => false];
            }
            
            // Generate unique filename
            $extension = $this->getFileExtension($file['name']);
            $filename = $this->generateUniqueFilename($extension);
            $filepath = $this->uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                return ['error' => 'Failed to save file', 'success' => false];
            }
            
            // Store metadata
            $metadata = [
                'original_name' => $file['name'],
                'size' => $file['size'],
                'type' => $file['type'],
                'uploaded_at' => date('Y-m-d H:i:s'),
                'hash' => hash_file('sha256', $filepath),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];
            
            $this->saveMetadata($filename, $metadata);
            
            return [
                'success' => true,
                'blob_id' => $filename,
                'public_url' => $this->getPublicUrl($filename),
                'metadata' => $metadata
            ];
            
        } catch (Exception $e) {
            return ['error' => 'Upload failed: ' . $e->getMessage(), 'success' => false];
        }
    }
    
    private function validateUpload($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->getUploadErrorMessage($file['error']);
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return 'File too large. Maximum size: ' . $this->formatBytes($this->maxFileSize);
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return 'File type not allowed: ' . $mimeType;
        }
        
        // Additional security checks
        if ($this->isSuspiciousFile($file['name'], $file['tmp_name'])) {
            return 'File appears to be malicious';
        }
        
        return true;
    }
    
    private function isSuspiciousFile($filename, $tmpPath) {
        // Check for dangerous extensions
        $dangerousExts = ['php', 'phtml', 'php3', 'php4', 'php5', 'pl', 'py', 'cgi', 'asp', 'aspx', 'jsp', 'exe', 'bat', 'cmd'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $dangerousExts)) {
            return true;
        }
        
        // Check file content for PHP tags
        $content = file_get_contents($tmpPath, false, null, 0, 1024);
        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false || strpos($content, '<script') !== false) {
            return true;
        }
        
        return false;
    }
    
    private function generateUniqueFilename($extension) {
        do {
            $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        } while (file_exists($this->uploadDir . $filename));
        
        return $filename;
    }
    
    private function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    private function saveMetadata($filename, $metadata) {
        $metaFile = $this->uploadDir . $filename . '.meta';
        file_put_contents($metaFile, json_encode($metadata, JSON_PRETTY_PRINT));
    }
    
    public function getMetadata($blobId) {
        $metaFile = $this->uploadDir . $blobId . '.meta';
        if (!file_exists($metaFile)) {
            return null;
        }
        
        return json_decode(file_get_contents($metaFile), true);
    }
    
    public function getPublicUrl($blobId) {
        return $this->baseUrl . '/blob.php?id=' . urlencode($blobId);
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
        
        // Set appropriate headers
        header('Content-Type: ' . $metadata['type']);
        header('Content-Length: ' . filesize($filepath));
        header('Content-Disposition: inline; filename="' . $metadata['original_name'] . '"');
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
    
    private function isValidBlobId($blobId) {
        return preg_match('/^[a-f0-9]{32}\.[a-z0-9]+$/i', $blobId) && 
               strpos($blobId, '..') === false;
    }
    
    public function deleteBlob($blobId) {
        if (!$this->isValidBlobId($blobId)) {
            return ['error' => 'Invalid blob ID', 'success' => false];
        }
        
        $filepath = $this->uploadDir . $blobId;
        $metapath = $filepath . '.meta';
        
        $deleted = [];
        if (file_exists($filepath)) {
            unlink($filepath);
            $deleted[] = 'file';
        }
        
        if (file_exists($metapath)) {
            unlink($metapath);
            $deleted[] = 'metadata';
        }
        
        if (empty($deleted)) {
            return ['error' => 'Blob not found', 'success' => false];
        }
        
        return ['success' => true, 'deleted' => $deleted];
    }
    
    public function listBlobs($limit = 50, $offset = 0) {
        $files = glob($this->uploadDir . '*.meta');
        $blobs = [];
        
        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        $files = array_slice($files, $offset, $limit);
        
        foreach ($files as $metaFile) {
            $filename = basename($metaFile, '.meta');
            $metadata = json_decode(file_get_contents($metaFile), true);
            
            if ($metadata) {
                $blobs[] = [
                    'blob_id' => $filename,
                    'public_url' => $this->getPublicUrl($filename),
                    'metadata' => $metadata
                ];
            }
        }
        
        return [
            'success' => true,
            'data' => $blobs,
            'total' => count(glob($this->uploadDir . '*.meta')),
            'limit' => $limit,
            'offset' => $offset
        ];
    }
    
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File too large';
            case UPLOAD_ERR_PARTIAL:
                return 'File partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// Configuration - CHANGE THESE VALUES
$config = [
    'base_url' => 'http://cdn.leosofd6.lk',
    'base_url' => 'http://localhost/php-blob-manager', // Local development URL
    'upload_dir' => 'uploads/',
    'max_file_size' => 50 * 1024 * 1024, // 50MB
    'bearer_token' => 'your-secure-bearer-token-here-change-this' // CHANGE THIS!
];

// Initialize blob manager
$blobManager = new BlobManager(
    $config['upload_dir'], 
    $config['base_url'], 
    $config['max_file_size'], 
    $config['bearer_token']
);

// Authenticate requests
if (!$blobManager->authenticate()) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Unauthorized. Bearer token required.',
        'success' => false
    ]);
    exit;
}

// Handle different operations
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            if ($action === 'upload' || isset($_FILES['file'])) {
                if (!isset($_FILES['file'])) {
                    echo json_encode(['error' => 'No file provided', 'success' => false]);
                } else {
                    echo json_encode($blobManager->upload($_FILES['file']));
                }
            } else {
                echo json_encode(['error' => 'Invalid action', 'success' => false]);
            }
            break;
            
        case 'GET':
            if ($action === 'list') {
                $limit = min((int)($_GET['limit'] ?? 50), 100);
                $offset = (int)($_GET['offset'] ?? 0);
                echo json_encode($blobManager->listBlobs($limit, $offset));
            } elseif ($action === 'metadata' && isset($_GET['id'])) {
                $metadata = $blobManager->getMetadata($_GET['id']);
                if ($metadata) {
                    echo json_encode(['success' => true, 'data' => $metadata]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Blob not found', 'success' => false]);
                }
            } elseif ($action === 'info') {
                // API info endpoint
                echo json_encode([
                    'success' => true,
                    'api_version' => '1.0',
                    'max_file_size' => $config['max_file_size'],
                    'max_file_size_formatted' => $this->formatBytes($config['max_file_size']),
                    'endpoints' => [
                        'POST /upload' => 'Upload file',
                        'GET /?action=list' => 'List blobs',
                        'GET /?action=metadata&id={blob_id}' => 'Get metadata',
                        'DELETE /?id={blob_id}' => 'Delete blob'
                    ]
                ]);
            } else {
                echo json_encode(['error' => 'Invalid action', 'success' => false]);
            }
            break;
            
        case 'DELETE':
            if (isset($_GET['id'])) {
                echo json_encode($blobManager->deleteBlob($_GET['id']));
            } else {
                echo json_encode(['error' => 'No blob ID provided', 'success' => false]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed', 'success' => false]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error: ' . $e->getMessage(),
        'success' => false
    ]);
}

?>