# 🗂️ PHP Blob Manager

> A secure, modern file management system with Bearer token authentication, built with pure PHP. Upload, manage, and serve files with a beautiful web interface.

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)
[![XAMPP](https://img.shields.io/badge/XAMPP-Compatible-orange?style=flat-square)](https://www.apachefriends.org/)

---

## ✨ Features

- 🔐 **Secure Authentication** - Bearer token-based API security
- 📤 **Easy File Upload** - Drag & drop or traditional file picker
- 🎨 **Modern UI** - Beautiful, responsive web interface
- 📊 **File Statistics** - Track total files, size, and daily uploads
- 🔗 **Public URLs** - Generate shareable links for uploaded files
- 🗑️ **File Management** - View, delete, and manage all your files
- 🛡️ **Security First** - File type validation, size limits, and malicious file detection
- 📝 **Metadata Tracking** - Store file information, upload dates, and hashes
- ⚡ **Fast & Lightweight** - Pure PHP, no external dependencies

---

## 🚀 Quick Start

### Prerequisites

- PHP 7.4+ (or XAMPP with PHP)
- Apache web server
- Web browser

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/php-blob-manager.git
   cd php-blob-manager
   ```

2. **Place in your web server directory**
   - For XAMPP: Copy to `C:\xampp\htdocs\php-blob-manager`
   - For Linux: Copy to `/var/www/html/php-blob-manager`

3. **Configure your settings**
   
   Edit these files and set your bearer token and base URL:
   
   - `index.php` (line 4-5)
   - `blob_manager.php` (line 342-345)
   - `blob.php` (line 99)
   
   ```php
   // Example configuration
   $bearerToken = 'your-super-secret-token-here';
   $apiUrl = 'http://localhost/php-blob-manager'; // or your domain
   ```

4. **Start your web server**
   - XAMPP: Start Apache from XAMPP Control Panel
   - Linux: `sudo systemctl start apache2`

5. **Access the application**
   ```
   http://localhost/php-blob-manager/index.php
   ```

---

## 📖 Usage

### Web Interface

1. Open the management interface in your browser
2. **Upload files**: Drag & drop files or click "Choose Files"
3. **View files**: Click the "View" button to open files in a new tab
4. **Delete files**: Click "Delete" to remove files permanently
5. **Copy URLs**: Click the 📋 button to copy public URLs to clipboard

### API Endpoints

#### Upload File
```http
POST /blob_manager.php?action=upload
Authorization: Bearer your-token-here
Content-Type: multipart/form-data

file: [binary file data]
```

**Response:**
```json
{
  "success": true,
  "blob_id": "a1b2c3d4e5f6...",
  "public_url": "http://yourdomain.com/blob.php?id=a1b2c3d4e5f6...",
  "metadata": {
    "original_name": "document.pdf",
    "size": 1024000,
    "type": "application/pdf",
    "uploaded_at": "2024-01-15 10:30:00",
    "hash": "sha256_hash_here"
  }
}
```

#### List Files
```http
GET /blob_manager.php?action=list&limit=50&offset=0
Authorization: Bearer your-token-here
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "blob_id": "a1b2c3d4e5f6...",
      "public_url": "http://yourdomain.com/blob.php?id=...",
      "metadata": { ... }
    }
  ],
  "total": 25,
  "limit": 50,
  "offset": 0
}
```

#### Get File Metadata
```http
GET /blob_manager.php?action=metadata&id=YOUR_BLOB_ID
Authorization: Bearer your-token-here
```

#### Delete File
```http
DELETE /blob_manager.php?id=YOUR_BLOB_ID
Authorization: Bearer your-token-here
```

#### View/Download File
```http
GET /blob.php?id=YOUR_BLOB_ID
Authorization: Bearer your-token-here
```

---

## 🔧 Configuration

### File Size Limit

Default: **50MB**. Change in `blob_manager.php`:

```php
'max_file_size' => 100 * 1024 * 1024, // 100MB
```

### Allowed File Types

Supported types include:
- Images: JPEG, PNG, GIF, WebP, SVG
- Documents: PDF, DOC, DOCX, TXT, CSV, JSON
- Media: MP4, WebM, MP3, WAV
- Archives: ZIP, RAR

Modify in `blob_manager.php` (line 27-33) to add/remove types.

### Base URL

Set your domain/base URL in:
- `index.php` - For the web interface
- `blob_manager.php` - For API responses

---

## 🛡️ Security Features

- ✅ **Bearer Token Authentication** - All API requests require valid tokens
- ✅ **File Type Validation** - Only allowed MIME types accepted
- ✅ **Malicious File Detection** - Scans for PHP tags and dangerous extensions
- ✅ **Secure File Storage** - Files stored with random names, `.htaccess` protection
- ✅ **Path Traversal Protection** - Validates blob IDs to prevent directory traversal
- ✅ **Size Limits** - Configurable maximum file size
- ✅ **IP Tracking** - Logs uploader IP addresses

---

## 📁 Project Structure

```
php-blob-manager/
├── 📁 uploads/              # File storage directory
│   ├── .htaccess           # Security rules
│   └── index.php          # Directory protection
├── 🐘 blob.php             # File serving endpoint
├── 🐘 blob_manager.php     # Main API handler
├── 🐘 index.php            # Web management interface
├── 📄 README.md            # This file
└── 📄 introduction.txt     # Quick start guide
```

---

## 🎯 Use Cases

- 📦 **File Hosting Service** - Host and share files with public URLs
- 🖼️ **Image Gallery** - Manage and serve images
- 📄 **Document Management** - Store and organize documents
- 🎬 **Media Server** - Serve video and audio files
- 🔗 **CDN Alternative** - Simple content delivery for small projects

---

## 🔒 Security Best Practices

1. **Change the default bearer token** immediately
2. **Use HTTPS** in production environments
3. **Set strong bearer tokens** (32+ characters, random)
4. **Regularly review** uploaded files
5. **Monitor file sizes** and storage usage
6. **Keep PHP updated** to latest stable version

---

## 🐛 Troubleshooting

### Files not uploading?
- Check PHP `upload_max_filesize` and `post_max_size` in `php.ini`
- Verify `uploads/` directory has write permissions (755 or 777)
- Check Apache error logs

### Authentication errors?
- Ensure bearer token matches in all three files
- Check Authorization header format: `Bearer your-token-here`

### Files not accessible?
- Verify `.htaccess` file exists in `uploads/` directory
- Check file permissions
- Ensure `blob.php` can read from `uploads/` directory

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- Built with pure PHP - no frameworks required
- Inspired by modern blob storage services
- Designed for simplicity and security

---

## 📧 Support

For issues, questions, or contributions, please open an issue on GitHub.

---

<div align="center">

**Made with ❤️ using PHP**

⭐ Star this repo if you find it useful!

</div>
