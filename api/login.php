<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

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

try {
    // 入力データの取得
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // バリデーション
    $errors = [];

    if (empty($username)) {
        $errors[] = 'ユーザー名は必須です';
    }

    if (empty($password)) {
        $errors[] = 'パスワードは必須です';
    }

    // エラーがある場合はエラーレスポンスを返す
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => implode('、', $errors)
        ]);
        exit();
    }

    // ユーザー情報の取得（ユーザー名またはメールアドレスで検索）
    $stmt = $pdo->prepare('
        SELECT id, username, email, password 
        FROM users 
        WHERE username = ? OR email = ?
    ');
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    // ユーザーが存在しない場合
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'ユーザー名またはパスワードが正しくありません'
        ]);
        exit();
    }

    // パスワードの検証
    if (!password_verify($password, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ユーザー名またはパスワードが正しくありません'
        ]);
        exit();
    }

    // ログイン成功：セッションにユーザー情報を保存
    loginUser($user);

    echo json_encode([
        'success' => true,
        'message' => 'ログインしました',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email']
        ]
    ]);
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました'
    ]);
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'システムエラーが発生しました'
    ]);
}
