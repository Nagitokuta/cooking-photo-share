<?php
require_once '../includes/db.php';

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
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // バリデーション
    $errors = [];

    // ユーザー名のバリデーション
    if (empty($username)) {
        $errors[] = 'ユーザー名は必須です';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'ユーザー名は3文字以上50文字以下で入力してください';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'ユーザー名は英数字とアンダースコアのみ使用できます';
    }

    // メールアドレスのバリデーション
    if (empty($email)) {
        $errors[] = 'メールアドレスは必須です';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '正しいメールアドレスを入力してください';
    }

    // パスワードのバリデーション
    if (empty($password)) {
        $errors[] = 'パスワードは必須です';
    } elseif (strlen($password) < 6) {
        $errors[] = 'パスワードは6文字以上で入力してください';
    }

    // パスワード確認のバリデーション
    if ($password !== $password_confirm) {
        $errors[] = 'パスワードが一致しません';
    }

    // 既存ユーザーの重複チェック
    if (empty($errors)) {
        // ユーザー名の重複チェック
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'このユーザー名は既に使用されています';
        }

        // メールアドレスの重複チェック
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'このメールアドレスは既に登録されています';
        }
    }

    // エラーがある場合はエラーレスポンスを返す
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => implode('、', $errors)
        ]);
        exit();
    }

    // パスワードのハッシュ化
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // データベースに新規ユーザーを登録
    $stmt = $pdo->prepare('
        INSERT INTO users (username, email, password, created_at) 
        VALUES (?, ?, ?, NOW())
    ');

    $result = $stmt->execute([$username, $email, $hashedPassword]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'ユーザー登録が完了しました'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ユーザー登録に失敗しました'
        ]);
    }
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'データベースエラーが発生しました'
    ]);
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'システムエラーが発生しました'
    ]);
}
