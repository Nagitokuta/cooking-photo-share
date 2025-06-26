<?php
require_once 'includes/config.php';
require_once 'includes/session.php';

// セッション初期化
initializeSession();

// 既にログイン済みの場合はフィードにリダイレクト
redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録 | 料理写真共有プラットフォーム</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>新規ユーザー登録</h1>
        <form id="registerForm" class="auth-form">
            <div class="form-group">
                <label for="username">ユーザー名</label>
                <input type="text" id="username" name="username" required>
                <small class="form-help">3文字以上50文字以下、英数字とアンダースコアのみ</small>
            </div>
            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required>
                <small class="form-help">6文字以上</small>
            </div>
            <div class="form-group">
                <label for="password_confirm">パスワード確認</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit" class="btn btn-primary">登録する</button>
        </form>
        <p class="auth-link">
            既にアカウントをお持ちの方は<a href="index.php">こちら</a>
        </p>
    </div>
    <script src="js/main.js"></script>
</body>
</html>