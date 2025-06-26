<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/config.php';

// セッション初期化
initializeSession();

// JSONレスポンスを返すためのヘッダー設定
header('Content-Type: application/json');

// DELETEまたはPOSTメソッドを許可
if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'])) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'DELETE/POSTメソッドのみ許可されています']);
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

    // コメントIDの取得
    $comment_id = 0;
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $comment_id = isset($input['comment_id']) ? intval($input['comment_id']) : 0;
    } else {
        $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
    }

    if ($comment_id <= 0) {
        echo json_encode(['success' => false, 'message' => '無効なコメントIDです']);
        exit();
    }

    // コメントの存在確認と権限チェック
    $checkSql = '
        SELECT c.id, c.photo_id, c.user_id, p.user_id as photo_owner_id
        FROM comments c
        INNER JOIN photos p ON c.photo_id = p.id
        WHERE c.id = ?
    ';

    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$comment_id]);
    $comment = $checkStmt->fetch();

    if (!$comment) {
        echo json_encode(['success' => false, 'message' => 'コメントが見つかりません']);
        exit();
    }

    // 削除権限チェック（コメント投稿者または写真投稿者のみ削除可能）
    if ($comment['user_id'] != $user_id && $comment['photo_owner_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'コメントを削除する権限がありません']);
        exit();
    }

    // コメントを削除
    $deleteSql = 'DELETE FROM comments WHERE id = ?';
    $deleteStmt = $pdo->prepare($deleteSql);
    $result = $deleteStmt->execute([$comment_id]);

    if ($result) {
        // 写真の最新コメント数を取得
        $countSql = 'SELECT COUNT(*) FROM comments WHERE photo_id = ?';
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute([$comment['photo_id']]);
        $commentsCount = $countStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'message' => 'コメントを削除しました',
            'comments_count' => intval($commentsCount)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'コメントの削除に失敗しました'
        ]);
    }
} catch (PDOException $e) {
    error_log("Comment delete error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました'
    ]);
} catch (Exception $e) {
    error_log("Comment delete error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'システムエラーが発生しました'
    ]);
}
