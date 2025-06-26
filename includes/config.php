<?php
// データベース設定
define('DB_HOST', 'localhost');
define('DB_NAME', 'cooking_photo_share');
define('DB_USER', 'root');
define('DB_PASS', '');

// アプリケーション設定
define('APP_NAME', '料理写真共有プラットフォーム');
define('UPLOAD_DIR',  __DIR__ . '/../uploads/photos/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// セッション設定
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

// ファイルアップロード設定
define('THUMBNAIL_DIR', 'uploads/thumbnails/');
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// 画像処理設定
define('MAX_IMAGE_WIDTH', 1200);
define('MAX_IMAGE_HEIGHT', 1200);
define('THUMBNAIL_WIDTH', 300);
define('THUMBNAIL_HEIGHT', 300);
define('JPEG_QUALITY', 85);
define('PNG_COMPRESSION', 6);
define('WEBP_QUALITY', 85);

// エラーメッセージ
define('ERROR_FILE_NOT_SELECTED', '写真ファイルが選択されていません');
define('ERROR_INVALID_FILE_TYPE', 'JPEG、PNG、GIF、WebP形式の画像のみアップロード可能です');
define('ERROR_FILE_TOO_LARGE', 'ファイルサイズは5MB以下にしてください');
define('ERROR_INVALID_IMAGE', '有効な画像ファイルではありません');
define('ERROR_UPLOAD_FAILED', 'ファイルのアップロードに失敗しました');
define('ERROR_DATABASE_SAVE_FAILED', 'データベースへの保存に失敗しました');
