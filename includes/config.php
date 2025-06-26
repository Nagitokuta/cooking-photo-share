<?php
// データベース設定
define('DB_HOST', 'mysql');
define('DB_NAME', 'cooking_photo_share');
define('DB_USER', 'root');
define('DB_PASS', 'password');

// アプリケーション設定
define('APP_NAME', '料理写真共有プラットフォーム');
define('UPLOAD_DIR', 'uploads/photos/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// セッション設定
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
