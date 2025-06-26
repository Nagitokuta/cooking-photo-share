<?php
require_once '../includes/session.php';

// JSONレスポンスを返すためのヘッダー設定
header('Content-Type: application/json');

try {
    $isValid = checkLogin();

    echo json_encode([
        'valid' => $isValid,
        'user' => $isValid ? getCurrentUser() : null
    ]);
} catch (Exception $e) {
    error_log("Session check error: " . $e->getMessage());
    echo json_encode([
        'valid' => false,
        'error' => 'セッションチェックでエラーが発生しました'
    ]);
}
