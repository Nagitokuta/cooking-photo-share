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
    $photo_id = isset($_POST['photo_id']) ? intval($_POST['photo_id']) : 0;
    $content = trim($_POST['content'] ?? '');

    // バリデーション
    $errors = [];

    if ($photo_id <= 0) {
        $errors[] = '無効な写真IDです';
    }

    if (empty($content)) {
        $errors[] = 'コメント内容は必須です';
    } elseif (strlen($content) > 500) {
        $errors[] = 'コメントは500文字以下で入力してください';
    }

    // 写真の存在確認
    if (empty($errors)) {
        $photoCheckSql = 'SELECT id FROM photos WHERE id = ?';
        $photoCheckStmt = $pdo->prepare($photoCheckSql);
        $photoCheckStmt->execute([$photo_id]);

        if (!$photoCheckStmt->fetch()) {
            $errors[] = '指定された写真が見つかりません';
        }
    }

    // エラーがある場合は終了
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => implode('、', $errors)
        ]);
        exit();
    }

    // コメントをデータベースに保存
    $insertSql = '
        INSERT INTO comments (photo_id, user_id, content, created_at) 
        VALUES (?, ?, ?, NOW())
    ';

    $stmt = $pdo->prepare($insertSql);
    $result = $stmt->execute([$photo_id, $user_id, $content]);

    if ($result) {
        $comment_id = $pdo->lastInsertId();

        // 投稿したコメントの詳細を取得
        $commentSql = '
            SELECT 
                c.id,
                c.content,
                c.created_at,
                u.username
            FROM comments c
            INNER JOIN users u ON c.user_id = u.id
            WHERE c.id = ?
        ';

        $commentStmt = $pdo->prepare($commentSql);
        $commentStmt->execute([$comment_id]);
        $comment = $commentStmt->fetch();

        // 写真の最新コメント数を取得
        $countSql = 'SELECT COUNT(*) FROM comments WHERE photo_id = ?';
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute([$photo_id]);
        $commentsCount = $countStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'message' => 'コメントを投稿しました',
            'comment' => [
                'id' => intval($comment['id']),
                'content' => $comment['content'],
                'username' => $comment['username'],
                'created_at' => $comment['created_at']
            ],
            'comments_count' => intval($commentsCount)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'コメントの投稿に失敗しました'
        ]);
    }
} catch (PDOException $e) {
    error_log("Comment post error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました'
    ]);
} catch (Exception $e) {
    error_log("Comment post error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'システムエラーが発生しました'
    ]);
}
