<?php
// index.php - Management Interface for Blob Manager

$bearerToken = 'your-secure-bearer-token-here-change-this'; // CHANGE THIS!
$apiUrl = 'http://localhost/php-blob-manager'; // Local development URL
$apiUrl = 'http://cdn.leosofd6.lk'; // CHANGE THIS TO YOUR DOMAIN

?>
<!DOCTYPE html>
<html>

<head>
    <title>Blob Manager - Management Interface</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        :root {
            --bg-color: #f6f8fa;
            --card-bg: #ffffff;
            --text-color: #212529;
            --subtle-text: #6c757d;
            --accent-color: #00bcd4;
            /* A vibrant teal/cyan */
            --accent-hover: #0097a7;
            --shadow-light: 0 4px 12px rgba(0, 0, 0, 0.05);
            --shadow-subtle: 0 1px 3px rgba(0, 0, 0, 0.05);
            --border-color: #e9ecef;
            --success-color: #28a745;
            --error-color: #dc3545;
            --info-color: #007bff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            background: var(--bg-color);
            color: var(--text-color);
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 600;
        }

        /* UI Elements */
        .card {
            background: var(--card-bg);
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-light);
        }

        .header {
            text-align: center;
            padding: 40px 24px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .header h1 {
            font-size: 2.5rem;
            margin: 0 0 10px;
        }

        .header p {
            color: var(--subtle-text);
            margin: 0;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: var(--shadow-subtle);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: 700;
            color: var(--accent-color);
            margin-bottom: 5px;
        }

        .upload-area {
            border: 2px dashed var(--border-color);
            padding: 50px;
            text-align: center;
            border-radius: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover,
        .upload-area.dragover {
            border-color: var(--accent-color);
            background: #eaf8f9;
        }

        .upload-area p {
            font-weight: 600;
            color: var(--subtle-text);
        }

        .btn {
            background: var(--accent-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .btn:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        /* Update your existing .btn-small class */
        .btn-small {
            padding: 8px 16px;
            font-size: 14px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 400;
            border: none;
            cursor: pointer;
            /* Add this line */
            transition: background-color 0.2s ease;
            /* Add a smooth transition */
        }

        /* Add hover effects for each button type */
        .btn-view {
            background: var(--success-color);
            color: white;
        }

        .btn-view:hover {
            background: #1e7d34;
            /* Darker green */
        }

        .btn-delete {
            background: var(--error-color);
            color: white;
        }

        .btn-delete:hover {
            background: #a72432;
            /* Darker red */
        }

        .btn-refresh {
            background: #6c757d;
            padding: 8px 16px;
        }

        .result {
            margin: 15px 0;
            padding: 15px;
            border-radius: 8px;
            font-weight: 500;
        }

        .error {
            background: #f8d7da;
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }

        .success {
            background: #d4edda;
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .info {
            background: #e2e3e5;
            color: var(--subtle-text);
            border-left: 4px solid #adb5bd;
            font-weight: 400;
        }

        .blob-list-container {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow-light);
        }

        .blob-list-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .blob-item {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s ease;
        }

        .blob-item:hover {
            background: #fbfbfc;
        }

        .blob-item:last-child {
            border-bottom: none;
        }

        .blob-info h4 {
            margin: 0 0 5px 0;
            font-size: 1.1em;
        }

        .blob-info p {
            margin: 5px 0;
            color: var(--subtle-text);
            font-size: 14px;
        }

        .blob-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #e9ecef;
            border-top: 3px solid var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .url-display {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 6px;
            font-family: monospace;
            word-break: break-all;
            font-size: 14px;
            margin: 10px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .copy-btn {
            background: var(--subtle-text);
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.2s ease;
        }

        .copy-btn:hover {
            background: #5a6268;
        }

        .copy-btn::before {
            content: "📋";
        }

        .copy-btn.copied::before {
            content: "✓";
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Blob Manager</h1>
        <p>Your secure file management system</p>
    </div>

    <div class="stats" id="stats">
        <div class="stat-card">
            <div class="stat-number" id="totalFiles">-</div>
            <div>Total Files</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="totalSize">-</div>
            <div>Total Size</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="todayUploads">-</div>
            <div>Today's Uploads</div>
        </div>
    </div>

    <div class="card">
        <h2>Upload Files</h2>
        <div class="upload-area" id="uploadArea">
            <p>Drag & drop files here or click to browse</p>
            <input type="file" id="fileInput" multiple style="display: none;">
            <button type="button" class="btn" id="selectBtn">Choose Files</button>
        </div>
        <div id="uploadResults"></div>
    </div>

    <div class="blob-list-container">
        <div class="blob-list-header">
            <h2>Recent Files</h2>
            <button class="btn btn-refresh" id="refreshBtn">Refresh</button>
        </div>
        <div id="blobList">
            <div style="padding: 40px; text-align: center; color: var(--subtle-text);">
                <div class="loading"></div>
                <p>Loading files...</p>
            </div>
        </div>
    </div>

    <script>
        const API_URL = '<?php echo $apiUrl; ?>';
        const BEARER_TOKEN = '<?php echo $bearerToken; ?>';

        // API Helper
        async function apiCall(endpoint, options = {}) {
            const response = await fetch(API_URL + endpoint, {
                ...options,
                headers: {
                    'Authorization': `Bearer ${BEARER_TOKEN}`,
                    ...options.headers
                }
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({
                    error: 'Network error or invalid JSON'
                }));
                throw new Error(error.error || `HTTP ${response.status}`);
            }

            return await response.json();
        }

        // DOM Elements
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const selectBtn = document.getElementById('selectBtn');
        const uploadResults = document.getElementById('uploadResults');
        const blobList = document.getElementById('blobList');
        const refreshBtn = document.getElementById('refreshBtn');

        // Stats elements
        const totalFiles = document.getElementById('totalFiles');
        const totalSize = document.getElementById('totalSize');
        const todayUploads = document.getElementById('todayUploads');

        // Event Listeners
        selectBtn.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', (e) => handleFiles(e.target.files));
        refreshBtn.addEventListener('click', loadBlobList);

        // Drag and Drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        // File Handling
        async function handleFiles(files) {
            for (let file of files) {
                await uploadFile(file);
            }
            loadBlobList(); // Refresh list after uploads
        }

        async function uploadFile(file) {
            const formData = new FormData();
            formData.append('file', file);

            const resultDiv = document.createElement('div');
            resultDiv.className = 'result info';
            resultDiv.innerHTML = `
                <div class="loading"></div>
                <strong>${file.name}</strong> - Uploading...
            `;
            uploadResults.appendChild(resultDiv);

            try {
                const result = await apiCall('/blob_manager.php?action=upload', {
                    method: 'POST',
                    body: formData
                });

                if (result.success) {
                    resultDiv.className = 'result success';
                    resultDiv.innerHTML = `
                        <strong>${file.name}</strong> uploaded successfully!<br>
                        <strong>Blob ID:</strong> ${result.blob_id}<br>
                        <div class="url-display">
                            <span>${result.public_url}</span>
                            <button class="copy-btn" onclick="copyToClipboard(this, '${result.public_url}')"></button>
                        </div>
                        <strong>Size:</strong> ${formatBytes(result.metadata.size)}
                    `;
                } else {
                    throw new Error(result.error || 'Upload failed');
                }
            } catch (error) {
                resultDiv.className = 'result error';
                resultDiv.innerHTML = `<strong>${file.name}:</strong> ${error.message}`;
            }
        }

        // Load and Display Blobs
        async function loadBlobList() {
            try {
                const result = await apiCall('/blob_manager.php?action=list&limit=50');

                if (!result.success || !result.data) {
                    throw new Error('Failed to load blob list');
                }

                displayBlobs(result.data);
                updateStats(result.data);

            } catch (error) {
                blobList.innerHTML = `
                    <div style="padding: 40px; text-align: center; color: var(--error-color);">
                        <p>❌ Error loading files: ${error.message}</p>
                        <button class="btn" onclick="loadBlobList()">Try Again</button>
                    </div>
                `;
            }
        }

        function displayBlobs(blobs) {
            if (blobs.length === 0) {
                blobList.innerHTML = `
                    <div style="padding: 40px; text-align: center; color: var(--subtle-text);">
                        <p>No files uploaded yet.</p>
                    </div>
                `;
                return;
            }

            blobList.innerHTML = blobs.map(blob => `
                <div class="blob-item">
                    <div class="blob-info">
                        <h4>${escapeHtml(blob.metadata.original_name)}</h4>
                        <p><strong>Type:</strong> ${blob.metadata.type} | <strong>Size:</strong> ${formatBytes(blob.metadata.size)}</p>
                        <p><strong>Uploaded:</strong> ${new Date(blob.metadata.uploaded_at).toLocaleString()}</p>
                        <div class="url-display">
                            <span>${blob.public_url}</span>
                            <button class="copy-btn" onclick="copyToClipboard(this, '${blob.public_url}')"></button>
                        </div>
                    </div>
                    <div class="blob-actions">
                        <button class="btn-small btn-view" onclick="viewBlob('${blob.blob_id}')">View</button>
                        <button class="btn-small btn-delete" onclick="deleteBlob('${blob.blob_id}', this)">Delete</button>
                    </div>
                </div>
            `).join('');
        }

        function updateStats(blobs) {
            const today = new Date().toDateString();
            const todayCount = blobs.filter(blob =>
                new Date(blob.metadata.uploaded_at).toDateString() === today
            ).length;

            const totalSizeBytes = blobs.reduce((sum, blob) => sum + blob.metadata.size, 0);

            totalFiles.textContent = blobs.length;
            totalSize.textContent = formatBytes(totalSizeBytes);
            todayUploads.textContent = todayCount;
        }

        // New function to handle secure file viewing
        async function viewBlob(blobId) {
            try {
                // Fetch the file as a Blob (binary data)
                const url = `${API_URL}/blob.php?id=${encodeURIComponent(blobId)}`;
                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${BEARER_TOKEN}`
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status}`);
                }

                // Create a temporary URL for the Blob object
                const fileBlob = await response.blob();
                const blobUrl = URL.createObjectURL(fileBlob);

                // Open the blob in a new tab
                window.open(blobUrl, '_blank');

            } catch (error) {
                alert(`Failed to view file: ${error.message}`);
            }
        }

        // Delete Blob
        async function deleteBlob(blobId, button) {
            if (!confirm('Are you sure you want to delete this file?')) {
                return;
            }

            const originalText = button.textContent;
            button.textContent = '...';
            button.disabled = true;

            try {
                const result = await apiCall(`/blob_manager.php?id=${encodeURIComponent(blobId)}`, {
                    method: 'DELETE'
                });

                if (result.success) {
                    button.closest('.blob-item').remove();

                    const successDiv = document.createElement('div');
                    successDiv.className = 'result success';
                    successDiv.innerHTML = '✅ File deleted successfully';
                    uploadResults.appendChild(successDiv);

                    setTimeout(() => successDiv.remove(), 3000);
                    loadBlobList();
                } else {
                    throw new Error(result.error || 'Delete failed');
                }
            } catch (error) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'result error';
                errorDiv.innerHTML = `❌ Delete failed: ${error.message}`;
                uploadResults.appendChild(errorDiv);

                button.textContent = originalText;
                button.disabled = false;
            }
        }

        // Utility Functions
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        async function copyToClipboard(button, text) {
            try {
                await navigator.clipboard.writeText(text);
                button.classList.add('copied');
                setTimeout(() => {
                    button.classList.remove('copied');
                }, 1000);
            } catch (err) {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('URL copied to clipboard!');
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadBlobList();
        });
    </script>
</body>

</html>