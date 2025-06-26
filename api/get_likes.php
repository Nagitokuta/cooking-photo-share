<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/config.php';

// セッション初期化
initializeSession();

// JSONレスポンスを返すためのヘッダー設定
header('Content-Type: application/json');

// GETメソッドのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'GETメソッドのみ許可されています']);
    exit();
}

// ログインチェック
if (!checkLogin()) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です']);
    exit();
}

try {
    // 写真IDの取得
    $photo_id = isset($_GET['photo_id']) ? intval($_GET['photo_id']) : 0;

    if ($photo_id <= 0) {
        echo json_encode(['success' => false, 'message' => '無効な写真IDです']);
        exit();
    }

    // いいねしたユーザー一覧を取得
    $likesSql = '
        SELECT 
            l.id,
            l.created_at,
            u.username
        FROM likes l
        INNER JOIN users u ON l.user_id = u.id
        WHERE l.photo_id = ?
        ORDER BY l.created_at DESC
        LIMIT 50
    ';

    $likesStmt = $pdo->prepare($likesSql);
    $likesStmt->execute([$photo_id]);
    $likes = $likesStmt->fetchAll();

    // いいね数を取得
    $countSql = 'SELECT COUNT(*) FROM likes WHERE photo_id = ?';
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$photo_id]);
    $likesCount = $countStmt->fetchColumn();

    // データを整形
    $likesList = [];
    foreach ($likes as $like) {
        $likesList[] = [
            'id' => intval($like['id']),
            'username' => $like['username'],
            'created_at' => $like['created_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'likes' => $likesList,
        'likes_count' => intval($likesCount)
    ]);
} catch (PDOException $e) {
    error_log("Get likes error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました'
    ]);
} catch (Exception $e) {
    error_log("Get likes error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'システムエラーが発生しました'
    ]);
}
