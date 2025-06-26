<?php
require_once '../includes/session.php';

// セッション初期化
initializeSession();

// JSONレスポンスを返すためのヘッダー設定
header('Content-Type: application/json');

try {
    // ログイン状態をチェック
    if (!checkLogin()) {
        echo json_encode([
            'success' => false,
            'message' => 'ログインしていません'
        ]);
        exit();
    }

    // ログアウト処理
    logoutUser();

    echo json_encode([
        'success' => true,
        'message' => 'ログアウトしました'
    ]);

} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'ログアウト処理でエラーが発生しました'
    ]);
}
?>