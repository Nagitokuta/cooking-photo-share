<?php
// 画像表示用のヘルパー関数

/**
 * 画像のURLを生成
 */
function getImageUrl($filename)
{
    if (empty($filename)) {
        return 'assets/images/no-image.png'; // デフォルト画像
    }

    $imagePath = UPLOAD_DIR . $filename;
    if (file_exists($imagePath)) {
        return $imagePath;
    }

    return 'assets/images/no-image.png'; // ファイルが存在しない場合
}

/**
 * サムネイル画像のURLを生成
 */
function getThumbnailUrl($filename)
{
    if (empty($filename)) {
        return 'assets/images/no-image-thumb.png'; // デフォルトサムネイル
    }

    $thumbnailPath = THUMBNAIL_DIR . 'thumb_' . $filename;
    if (file_exists($thumbnailPath)) {
        return $thumbnailPath;
    }

    // サムネイルが存在しない場合は元画像を返す
    return getImageUrl($filename);
}

/**
 * 画像の情報を取得
 */
function getImageInfo($filename)
{
    $imagePath = UPLOAD_DIR . $filename;
    if (!file_exists($imagePath)) {
        return false;
    }

    $imageInfo = getimagesize($imagePath);
    if ($imageInfo === false) {
        return false;
    }

    return [
        'width' => $imageInfo[0],
        'height' => $imageInfo[1],
        'type' => $imageInfo['mime'],
        'size' => filesize($imagePath)
    ];
}

/**
 * レスポンシブ画像のsrcsetを生成
 */
function generateSrcSet($filename)
{
    $baseUrl = getImageUrl($filename);
    $thumbnailUrl = getThumbnailUrl($filename);

    return $thumbnailUrl . ' 300w, ' . $baseUrl . ' 1200w';
}
