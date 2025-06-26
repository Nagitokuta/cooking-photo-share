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
    $currentUser = getCurrentUser();
    $user_id = $currentUser['id'];

    // パラメータの取得
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 10;
    $offset = ($page - 1) * $limit;

    // 写真一覧を取得するSQL
    $sql = '
        SELECT 
            p.id,
            p.filename,
            p.description,
            p.created_at,
            u.username,
            COUNT(DISTINCT l.id) as likes_count,
            COUNT(DISTINCT c.id) as comments_count,
            CASE WHEN ul.id IS NOT NULL THEN 1 ELSE 0 END as user_liked
        FROM photos p
        INNER JOIN users u ON p.user_id = u.id
        LEFT JOIN likes l ON p.id = l.photo_id
        LEFT JOIN comments c ON p.id = c.photo_id
        LEFT JOIN likes ul ON p.id = ul.photo_id AND ul.user_id = ?
        GROUP BY p.id, p.filename, p.description, p.created_at, u.username, ul.id
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $limit, $offset]);
    $photos = $stmt->fetchAll();

    // 総件数を取得
    $countSql = 'SELECT COUNT(*) FROM photos';
    $countStmt = $pdo->query($countSql);
    $totalCount = $countStmt->fetchColumn();

    // 写真データを整形
    $photoList = [];
    foreach ($photos as $photo) {
        $photoList[] = [
            'id' => intval($photo['id']),
            'filename' => $photo['filename'],
            'image_url' => '/cooking-photo-share/uploads/photos/' . $photo['filename'],
            'description' => $photo['description'],
            'username' => $photo['username'],
            'created_at' => $photo['created_at'],
            'likes_count' => intval($photo['likes_count']),
            'comments_count' => intval($photo['comments_count']),
            'user_liked' => intval($photo['user_liked']) === 1
        ];
    }

    // ページネーション情報
    $totalPages = ceil($totalCount / $limit);
    $hasMore = $page < $totalPages;

    echo json_encode([
        'success' => true,
        'photos' => $photoList,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => intval($totalCount),
            'limit' => $limit,
            'has_more' => $hasMore
        ],
        'total' => intval($totalCount),
        'hasMore' => $hasMore
    ]);
} catch (PDOException $e) {
    error_log("Get photos error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました'
    ]);
} catch (Exception $e) {
    error_log("Get photos error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'システムエラーが発生しました'
    ]);
}
