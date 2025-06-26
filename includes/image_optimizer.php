<?php
// 画像最適化クラス

class ImageOptimizer
{
    private $maxWidth = 1200;
    private $maxHeight = 1200;
    private $quality = 85;

    public function optimizeImage($sourcePath, $destinationPath, $mimeType)
    {
        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            return false;
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];

        // リサイズが必要かチェック
        if ($sourceWidth <= $this->maxWidth && $sourceHeight <= $this->maxHeight) {
            return copy($sourcePath, $destinationPath);
        }

        // アスペクト比を保持してリサイズ
        $ratio = min($this->maxWidth / $sourceWidth, $this->maxHeight / $sourceHeight);
        $newWidth = intval($sourceWidth * $ratio);
        $newHeight = intval($sourceHeight * $ratio);

        // 元画像を読み込み
        $sourceImage = $this->createImageFromFile($sourcePath, $mimeType);
        if (!$sourceImage) {
            return false;
        }

        // リサイズ実行
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // 透明度を保持
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            imagefill($resizedImage, 0, 0, $transparent);
        }

        imagecopyresampled(
            $resizedImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $sourceWidth,
            $sourceHeight
        );

        // 保存
        $result = $this->saveImage($resizedImage, $destinationPath, $mimeType);

        // メモリ解放
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        return $result;
    }

    private function createImageFromFile($filePath, $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
                return imagecreatefromjpeg($filePath);
            case 'image/png':
                return imagecreatefrompng($filePath);
            case 'image/gif':
                return imagecreatefromgif($filePath);
            case 'image/webp':
                return imagecreatefromwebp($filePath);
            default:
                return false;
        }
    }

    private function saveImage($image, $filePath, $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
                return imagejpeg($image, $filePath, $this->quality);
            case 'image/png':
                return imagepng($image, $filePath, 6);
            case 'image/gif':
                return imagegif($image, $filePath);
            case 'image/webp':
                return imagewebp($image, $filePath, $this->quality);
            default:
                return false;
        }
    }
}
