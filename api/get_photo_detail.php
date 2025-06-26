<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/config.php';

// セッション初期化
initializeSession();

// JSONレスポンスを返すためのヘッダー設定
header('Content-Type: application/json');

// ログインチェック
if (!checkLogin()) {
    echo json_encode(['success' => false, 'message' => 'ログインが必要です']);
    exit();
}

try {
    $currentUser = getCurrentUser();
    $user_id = $currentUser['id'];

    // 写真IDの取得
    $photo_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($photo_id <= 0) {
        echo json_encode(['success' => false, 'message' => '無効な写真IDです']);
        exit();
    }

    // 写真詳細を取得
    $photoSql = '
        SELECT 
            p.id,
            p.filename,
            p.description,
            p.created_at,
            u.id as user_id,
            u.username,
            COUNT(DISTINCT l.id) as likes_count,
            COUNT(DISTINCT c.id) as comments_count,
            CASE WHEN ul.id IS NOT NULL THEN 1 ELSE 0 END as user_liked
        FROM photos p
        INNER JOIN users u ON p.user_id = u.id
        LEFT JOIN likes l ON p.id = l.photo_id
        LEFT JOIN comments c ON p.id = c.photo_id
        LEFT JOIN likes ul ON p.id = ul.photo_id AND ul.user_id = ?
        WHERE p.id = ?
        GROUP BY p.id, p.filename, p.description, p.created_at, u.id, u.username, ul.id
    ';

    $stmt = $pdo->prepare($photoSql);
    $stmt->execute([$user_id, $photo_id]);
    $photo = $stmt->fetch();

    if (!$photo) {
        echo json_encode(['success' => false, 'message' => '写真が見つかりません']);
        exit();
    }

    // コメント一覧を取得
    $commentsSql = '
        SELECT 
            c.id,
            c.content,
            c.created_at,
            u.username
        FROM comments c
        INNER JOIN users u ON c.user_id = u.id
        WHERE c.photo_id = ?
        ORDER BY c.created_at ASC
    ';

    $commentsStmt = $pdo->prepare($commentsSql);
    $commentsStmt->execute([$photo_id]);
    $comments = $commentsStmt->fetchAll();

    // コメントデータを整形
    $commentList = [];
    foreach ($comments as $comment) {
        $commentList[] = [
            'id' => intval($comment['id']),
            'content' => $comment['content'],
            'username' => $comment['username'],
            'created_at' => $comment['created_at']
        ];
    }

    // レスポンスデータを整形
    $photoDetail = [
        'id' => intval($photo['id']),
        'filename' => $photo['filename'],
        'image_url' => '/cooking-photo-share/uploads/photos/' . $photo['filename'],
        'description' => $photo['description'],
        'user_id' => intval($photo['user_id']),
        'username' => $photo['username'],
        'created_at' => $photo['created_at'],
        'likes_count' => intval($photo['likes_count']),
        'comments_count' => intval($photo['comments_count']),
        'user_liked' => intval($photo['user_liked']) === 1,
        'comments' => $commentList
    ];

    echo json_encode([
        'success' => true,
        'photo' => $photoDetail
    ]);
} catch (PDOException $e) {
    error_log("Get photo detail error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました'
    ]);
} catch (Exception $e) {
    error_log("Get photo detail error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'システムエラーが発生しました'
    ]);
}
