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
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    // バリデーション
    $errors = [];

    if ($photo_id <= 0) {
        $errors[] = '無効な写真IDです';
    }

    if (!in_array($action, ['like', 'unlike'])) {
        $errors[] = '無効なアクションです';
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

    // 既存のいいねをチェック
    $existingLikeSql = 'SELECT id FROM likes WHERE photo_id = ? AND user_id = ?';
    $existingLikeStmt = $pdo->prepare($existingLikeSql);
    $existingLikeStmt->execute([$photo_id, $user_id]);
    $existingLike = $existingLikeStmt->fetch();

    $result = false;
    $message = '';
    $isLiked = false;

    if ($action === 'like') {
        if (!$existingLike) {
            // いいねを追加
            $insertSql = 'INSERT INTO likes (photo_id, user_id, created_at) VALUES (?, ?, NOW())';
            $insertStmt = $pdo->prepare($insertSql);
            $result = $insertStmt->execute([$photo_id, $user_id]);
            $message = 'いいねしました';
            $isLiked = true;
        } else {
            // 既にいいね済み
            $result = true;
            $message = '既にいいね済みです';
            $isLiked = true;
        }
    } else { // unlike
        if ($existingLike) {
            // いいねを削除
            $deleteSql = 'DELETE FROM likes WHERE photo_id = ? AND user_id = ?';
            $deleteStmt = $pdo->prepare($deleteSql);
            $result = $deleteStmt->execute([$photo_id, $user_id]);
            $message = 'いいねを取り消しました';
            $isLiked = false;
        } else {
            // いいねしていない
            $result = true;
            $message = 'いいねしていません';
            $isLiked = false;
        }
    }

    if ($result) {
        // 最新のいいね数を取得
        $countSql = 'SELECT COUNT(*) FROM likes WHERE photo_id = ?';
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute([$photo_id]);
        $likesCount = $countStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'message' => $message,
            'liked' => $isLiked,
            'likes_count' => intval($likesCount)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'いいね処理に失敗しました'
        ]);
    }
} catch (PDOException $e) {
    error_log("Like error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました'
    ]);
} catch (Exception $e) {
    error_log("Like error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'システムエラーが発生しました'
    ]);
}
