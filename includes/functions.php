<?php
// 共通関数ファイル

/**
 * ファイルサイズを人間が読みやすい形式に変換
 */
function formatFileSize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * 安全なファイル名を生成
 */
function generateSafeFileName($originalName, $prefix = '')
{
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $timestamp = date('YmdHis');
    $random = bin2hex(random_bytes(8));

    return $prefix . $timestamp . '_' . $random . '.' . strtolower($extension);
}

/**
 * 画像ファイルかどうかを確認
 */
function isValidImageFile($filePath, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
{
    // ファイルの存在確認
    if (!file_exists($filePath)) {
        return false;
    }

    // MIMEタイプの確認
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }

    // 画像情報の取得
    $imageInfo = getimagesize($filePath);
    if ($imageInfo === false) {
        return false;
    }

    return true;
}

/**
 * ディレクトリを安全に作成
 */
function createDirectorySafely($path, $permissions = 0755)
{
    if (is_dir($path)) {
        return true;
    }

    return mkdir($path, $permissions, true);
}

/**
 * ファイルを安全に削除
 */
function deleteFileSafely($filePath)
{
    if (file_exists($filePath) && is_file($filePath)) {
        return unlink($filePath);
    }
    return true;
}

/**
 * 画像のサムネイルを生成
 */
function generateThumbnail($sourcePath, $thumbnailPath, $maxWidth = 300, $maxHeight = 300)
{
    $imageInfo = getimagesize($sourcePath);
    if ($imageInfo === false) {
        return false;
    }

    $sourceWidth = $imageInfo[0];
    $sourceHeight = $imageInfo[1];
    $mimeType = $imageInfo['mime'];

    // アスペクト比を保持してサムネイルサイズを計算
    $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
    $thumbnailWidth = intval($sourceWidth * $ratio);
    $thumbnailHeight = intval($sourceHeight * $ratio);

    // 元画像を読み込み
    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        case 'image/webp':
            $sourceImage = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }

    if (!$sourceImage) {
        return false;
    }

    // サムネイル画像を作成
    $thumbnailImage = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

    // PNG/GIFの透明度を保持
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($thumbnailImage, false);
        imagesavealpha($thumbnailImage, true);
        $transparent = imagecolorallocatealpha($thumbnailImage, 255, 255, 255, 127);
        imagefill($thumbnailImage, 0, 0, $transparent);
    }

    // リサイズ実行
    imagecopyresampled(
        $thumbnailImage,
        $sourceImage,
        0,
        0,
        0,
        0,
        $thumbnailWidth,
        $thumbnailHeight,
        $sourceWidth,
        $sourceHeight
    );

    // サムネイルを保存
    $result = false;
    switch ($mimeType) {
        case 'image/jpeg':
            $result = imagejpeg($thumbnailImage, $thumbnailPath, 80);
            break;
        case 'image/png':
            $result = imagepng($thumbnailImage, $thumbnailPath, 6);
            break;
        case 'image/gif':
            $result = imagegif($thumbnailImage, $thumbnailPath);
            break;
        case 'image/webp':
            $result = imagewebp($thumbnailImage, $thumbnailPath, 80);
            break;
    }

    // メモリ解放
    imagedestroy($sourceImage);
    imagedestroy($thumbnailImage);

    return $result;
}

/**
 * アップロードされたファイルの情報を取得
 */
function getUploadFileInfo($file)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    return [
        'name' => $file['name'],
        'tmp_name' => $file['tmp_name'],
        'size' => $file['size'],
        'type' => $file['type'],
        'error' => $file['error']
    ];
}
