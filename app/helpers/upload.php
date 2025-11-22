<?php
// FILE: /app/helpers/upload.php

/**
 * SplashMarket - File Upload Helper
 *
 * Handles secure file uploads
 * PHP 7.0+ compatible
 */

/**
 * Upload file with validation
 *
 * @param array $file Uploaded file ($_FILES array element)
 * @param string $folder Folder name (products, stores, categories)
 * @param int $maxSize Max file size in bytes (default 5MB)
 * @return string File path on success
 * @throws Exception On upload error
 */
function uploadFile($file, $folder, $maxSize = 5242880)
{
    // Check if file was uploaded
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new Exception('Invalid file upload');
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];

        $message = isset($errors[$file['error']]) ? $errors[$file['error']] : 'Unknown upload error';
        throw new Exception($message);
    }

    // Validate file size
    if ($file['size'] > $maxSize) {
        throw new Exception('File size exceeds maximum allowed (' . ($maxSize / 1024 / 1024) . 'MB)');
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, and GIF images are allowed');
    }

    // Validate file extension
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception('Invalid file extension');
    }

    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/../../storage/uploads/' . $folder . '/';

    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }

    // Generate unique filename
    $filename = generateUniqueFilename($extension);
    $destination = $uploadDir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to move uploaded file');
    }

    // Set permissions
    chmod($destination, 0644);

    // Return relative path for storage in database
    return $folder . '/' . $filename;
}

/**
 * Generate unique filename
 *
 * @param string $extension File extension
 * @return string
 */
function generateUniqueFilename($extension)
{
    if (function_exists('random_bytes')) {
        $random = bin2hex(random_bytes(16));
    } else {
        $random = bin2hex(openssl_random_pseudo_bytes(16));
    }

    return time() . '_' . $random . '.' . $extension;
}

/**
 * Delete uploaded file
 *
 * @param string $filePath File path relative to uploads directory
 * @return bool
 */
function deleteUploadedFile($filePath)
{
    if (!$filePath) {
        return false;
    }

    $fullPath = __DIR__ . '/../../storage/uploads/' . $filePath;

    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }

    return false;
}

/**
 * Get file size
 *
 * @param string $filePath File path relative to uploads directory
 * @return int File size in bytes
 */
function getFileSize($filePath)
{
    if (!$filePath) {
        return 0;
    }

    $fullPath = __DIR__ . '/../../storage/uploads/' . $filePath;

    if (file_exists($fullPath)) {
        return filesize($fullPath);
    }

    return 0;
}

/**
 * Format file size for display
 *
 * @param int $bytes File size in bytes
 * @return string
 */
function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Resize image (requires GD library)
 *
 * @param string $sourcePath Source image path
 * @param string $destPath Destination path
 * @param int $maxWidth Max width
 * @param int $maxHeight Max height
 * @return bool
 */
function resizeImage($sourcePath, $destPath, $maxWidth, $maxHeight)
{
    if (!extension_loaded('gd')) {
        return false;
    }

    // Get image info
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        return false;
    }

    list($width, $height, $type) = $imageInfo;

    // Calculate new dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);

    if ($ratio >= 1) {
        // Image is smaller than max dimensions, just copy
        return copy($sourcePath, $destPath);
    }

    $newWidth = (int) ($width * $ratio);
    $newHeight = (int) ($height * $ratio);

    // Create image resource based on type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    if (!$source) {
        return false;
    }

    // Create new image
    $dest = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG and GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
    }

    // Resize
    imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($dest, $destPath, 90);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($dest, $destPath, 9);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($dest, $destPath);
            break;
    }

    // Free memory
    imagedestroy($source);
    imagedestroy($dest);

    return $result;
}
