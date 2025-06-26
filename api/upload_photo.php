<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/config.php';

// セッション初期化
initializeSession();

// JSONレスポンスを返すためのヘッダー設定
header('Content-Type: application/json');

// POSTメソッドのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POSTメソッドのみ許可されています']);
    exit();
}

// ログインチェック
if (!checkLogin()) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です']);
    exit();
}

try {
    $currentUser = getCurrentUser();
    $user_id = $currentUser['id'];

    // 入力データの取得
    $description = trim($_POST['description'] ?? '');

    // ファイルアップロードのチェック
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => '写真ファイルが選択されていません']);
        exit();
    }

    $uploadedFile = $_FILES['photo'];
    $fileTmpPath = $uploadedFile['tmp_name'];
    $fileName = $uploadedFile['name'];
    $fileSize = $uploadedFile['size'];
    $fileType = $uploadedFile['type'];

    // バリデーション
    $errors = [];

    // ファイルタイプの確認
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($fileType, $allowedTypes)) {
        $errors[] = 'JPEG、PNG、GIF、WebP形式の画像のみアップロード可能です';
    }

    // ファイルサイズの確認（5MB）
    if ($fileSize > MAX_FILE_SIZE) {
        $errors[] = 'ファイルサイズは5MB以下にしてください';
    }

    // 説明文の長さチェック
    if (strlen($description) > 500) {
        $errors[] = '説明文は500文字以下で入力してください';
    }

    // 画像ファイルの詳細チェック
    $imageInfo = getimagesize($fileTmpPath);
    if ($imageInfo === false) {
        $errors[] = '有効な画像ファイルではありません';
    }

    // エラーがある場合は終了
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => implode('、', $errors)
        ]);
        exit();
    }

    // ファイル名の生成（一意性を保証）
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid('photo_', true) . '.' . strtolower($fileExtension);

    // 保存先ディレクトリの確認・作成
    $uploadDir = UPLOAD_DIR;
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'アップロードディレクトリの作成に失敗しました']);
            exit();
        }
    }

    $destinationPath = $uploadDir . $newFileName;

    // 画像の最適化とリサイズ
    $optimizedImage = optimizeImage($fileTmpPath, $imageInfo, $fileType);
    if ($optimizedImage === false) {
        echo json_encode(['success' => false, 'message' => '画像の処理に失敗しました']);
        exit();
    }

    // 最適化された画像を保存
    if (!saveOptimizedImage($optimizedImage, $destinationPath, $fileType)) {
        error_log('saveOptimizedImage failed! Path: ' . $destinationPath);
        echo json_encode(['success' => false, 'message' => '画像の保存に失敗しました']);
        exit();
    }


    // データベースに写真情報を保存
    $stmt = $pdo->prepare('
        INSERT INTO photos (user_id, filename, description, created_at) 
        VALUES (?, ?, ?, NOW())
    ');

    $result = $stmt->execute([$user_id, $newFileName, $description]);

    if ($result) {
        $photo_id = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => '写真を投稿しました',
            'photo' => [
                'id' => $photo_id,
                'filename' => $newFileName,
                'description' => $description
            ]
        ]);
    } else {
        // データベース保存に失敗した場合、アップロードしたファイルを削除
        if (file_exists($destinationPath)) {
            unlink($destinationPath);
        }
        echo json_encode(['success' => false, 'message' => 'データベースへの保存に失敗しました']);
    }
} catch (PDOException $e) {
    error_log("Photo upload error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'データベースエラーが発生しました']);
} catch (Exception $e) {
    error_log("Photo upload error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'システムエラーが発生しました']);
}

// 画像最適化関数
function optimizeImage($filePath, $imageInfo, $mimeType)
{
    $width = $imageInfo[0];
    $height = $imageInfo[1];

    // 最大サイズの設定
    $maxWidth = 1200;
    $maxHeight = 1200;

    // リサイズが必要かチェック
    if ($width <= $maxWidth && $height <= $maxHeight) {
        // リサイズ不要の場合、元の画像を返す
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

    // アスペクト比を保持してリサイズ
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = intval($width * $ratio);
    $newHeight = intval($height * $ratio);

    // 元画像を読み込み
    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($filePath);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($filePath);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($filePath);
            break;
        case 'image/webp':
            $sourceImage = imagecreatefromwebp($filePath);
            break;
        default:
            return false;
    }

    if (!$sourceImage) {
        return false;
    }

    // 新しい画像を作成
    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

    // PNG/GIFの透明度を保持
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
        $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
        imagefill($resizedImage, 0, 0, $transparent);
    }

    // リサイズ実行
    imagecopyresampled(
        $resizedImage,
        $sourceImage,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $width,
        $height
    );

    // メモリ解放
    imagedestroy($sourceImage);

    return $resizedImage;
}

// 最適化された画像を保存する関数
function saveOptimizedImage($image, $filePath, $mimeType)
{
    switch ($mimeType) {
        case 'image/jpeg':
            return imagejpeg($image, $filePath, 85); // 品質85%
        case 'image/png':
            return imagepng($image, $filePath, 6); // 圧縮レベル6
        case 'image/gif':
            return imagegif($image, $filePath);
        case 'image/webp':
            return imagewebp($image, $filePath, 85); // 品質85%
        default:
            return false;
    }
}

// アップロードエラーの詳細チェック
function getUploadErrorMessage($errorCode)
{
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'ファイルサイズが大きすぎます（サーバー設定制限）';
        case UPLOAD_ERR_FORM_SIZE:
            return 'ファイルサイズが大きすぎます（フォーム制限）';
        case UPLOAD_ERR_PARTIAL:
            return 'ファイルが部分的にしかアップロードされませんでした';
        case UPLOAD_ERR_NO_FILE:
            return 'ファイルが選択されていません';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'テンポラリディレクトリが見つかりません';
        case UPLOAD_ERR_CANT_WRITE:
            return 'ファイルの書き込みに失敗しました';
        case UPLOAD_ERR_EXTENSION:
            return 'PHPの拡張機能によってアップロードが停止されました';
        default:
            return '不明なアップロードエラーが発生しました';
    }
}

// ファイルアップロードのチェック部分を更新
if (!isset($_FILES['photo'])) {
    echo json_encode(['success' => false, 'message' => ERROR_FILE_NOT_SELECTED]);
    exit();
}

$uploadError = $_FILES['photo']['error'];
if ($uploadError !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => getUploadErrorMessage($uploadError)]);
    exit();
}
