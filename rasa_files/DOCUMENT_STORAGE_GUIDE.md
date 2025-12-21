# Rasa Server Document Storage and Management Guide

## Overview

The `faq_updater.py` Flask microservice provides comprehensive document storage and management capabilities for the Rasa server environment. Documents are stored in the `./docs/` directory relative to the project root, with various API endpoints available for upload, download, update, and listing operations.

## Document Storage Structure

### Directory Location
- **Path**: `./docs/` (relative to project root)
- **Supported Formats**: `.txt` and `.md` files
- **Backup Location**: `./backup/` (automatic backups created on file updates)

### File Organization
- Documents are stored as plain text files
- File names should include appropriate extensions (.txt or .md)
- Automatic backup creation when updating existing files
- Files are organized in a flat directory structure

## Available API Functions

### 1. Upload File (`POST /upload-file`)

**Purpose**: Upload a new text file to the docs directory.

**Request Format**:
```json
{
  "file_content": "File content as string",
  "file_name": "filename.txt",
  "file_type": "txt"
}
```

**Response**:
```json
{
  "ok": true,
  "message": "Successfully uploaded file filename.txt",
  "file_path": "/path/to/docs/filename.txt",
  "backup_created": false
}
```

**Features**:
- Creates backup if file already exists
- Supports .txt and .md files
- Returns file path and backup status

### 2. List Documents (`GET /list-docs`)

**Purpose**: List all text files in the docs directory.

**Response**:
```json
{
  "ok": true,
  "files": [
    {
      "name": "document.txt",
      "size": 1024,
      "modified": "2025-12-21T09:30:00.000Z"
    }
  ],
  "count": 1
}
```

**Features**:
- Lists only .txt and .md files
- Provides file size and modification timestamp
- Includes debug logging for troubleshooting

### 3. Download File (`GET /download/<filename>`)

**Purpose**: Download the content of a specific file from docs directory.

**Parameters**:
- `filename`: URL-encoded file name
- `token`: Authentication token (header or query parameter)

**Response**: Raw file content as plain text.

**Security Features**:
- File path validation (must be within docs directory)
- Extension validation (.txt or .md only)
- Authentication required

### 4. Update Document (`POST /update-document`)

**Purpose**: Update the content of an existing document.

**Request Format**:
```json
{
  "file_name": "existing_file.txt",
  "file_content": "Updated content",
  "file_type": "txt"
}
```

**Response**:
```json
{
  "ok": true,
  "message": "Successfully updated document existing_file.txt",
  "file_path": "/path/to/docs/existing_file.txt",
  "backup_path": "/path/to/backup/existing_file_20251221_093000.txt"
}
```

**Features**:
- Automatic backup creation
- File existence validation
- Content size limit (1MB)
- Extension validation

### 5. Download Announcements (`GET /download-announcements`)

**Purpose**: Parse and download structured announcements from Announcements.txt.

**Special Parsing**:
- Splits content by "---------\n" separator
- Supports two formats:
  - New format: `id: 1\ntitle: Title\nContent...`
  - Old format: `id: 1\nContent...`
- Returns sorted by ID (newest first)

**Response**:
```json
{
  "ok": true,
  "announcements": [
    {
      "id": 1,
      "title": "Announcement Title",
      "content": "Announcement content..."
    }
  ],
  "count": 1
}
```

## Authentication

All endpoints require authentication via:
- **Header**: `X-FAQ-UPDATER-TOKEN`
- **Query Parameter**: `token`

Token is validated against `RASA_SECRET` environment variable.

## File Operations

### Backup System
- Automatic backups created in `./backup/` directory
- Timestamp format: `YYYYMMDD_HHMMSS`
- Backup filename: `{original_name}_{timestamp}.{extension}`

### File Validation
- **Allowed Extensions**: .txt, .md
- **Size Limit**: 1MB for updates
- **Path Security**: Files must remain within docs directory
- **Encoding**: UTF-8 for all operations

## Error Handling

### Common Error Responses
- `401 Unauthorized`: Missing or invalid token
- `400 Bad Request`: Invalid request format
- `403 Forbidden`: File type not allowed
- `404 Not Found`: File does not exist
- `413 Payload Too Large`: File content exceeds 1MB
- `500 Internal Server Error`: Server-side errors

## Usage Examples

### Upload a Document
```bash
curl -X POST http://localhost:5001/upload-file \
  -H "X-FAQ-UPDATER-TOKEN: your-secret" \
  -H "Content-Type: application/json" \
  -d '{
    "file_content": "This is my document content",
    "file_name": "my_document.txt",
    "file_type": "txt"
  }'
```

### List Documents
```bash
curl -H "X-FAQ-UPDATER-TOKEN: your-secret" \
  http://localhost:5001/list-docs
```

### Download a Document
```bash
curl -H "X-FAQ-UPDATER-TOKEN: your-secret" \
  "http://localhost:5001/download/my_document.txt"
```

### Update a Document
```bash
curl -X POST http://localhost:5001/update-document \
  -H "X-FAQ-UPDATER-TOKEN: your-secret" \
  -H "Content-Type: application/json" \
  -d '{
    "file_name": "my_document.txt",
    "file_content": "Updated content",
    "file_type": "txt"
  }'
```

## Integration Notes

- All operations are logged with detailed debug information
- CORS enabled for cross-origin requests
- File locks used for thread-safe operations (though not shown in document endpoints)
- Environment variables loaded from `.env` file
- Relative paths used for portability

## Related Functions (FAQ Management - Deprecated)

Note: FAQ-related functions were removed from the system, but the document management capabilities remain fully functional. The following endpoints are still available but FAQ-specific:

- `/sync-faqs` (stores faqs.json and faqs.txt)
- `/download-faqs` (downloads faqs.json)
- `/update-faq` (dynamic action generation - no longer used)

The document storage system is independent and continues to function for general document management needs.