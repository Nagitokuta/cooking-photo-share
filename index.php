<?php
require_once 'includes/config.php';
require_once 'includes/session.php';

// セッション初期化
initializeSession();

// 既にログイン済みの場合はフィードにリダイレクト
redirectIfLoggedIn();

// エラーメッセージの取得
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'login_required':
            $error_message = 'ログインが必要です';
            break;
        case 'session_expired':
            $error_message = 'セッションが期限切れです。再度ログインしてください';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン | 料理写真共有プラットフォーム</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>料理写真共有プラットフォーム</h1>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form id="loginForm" class="auth-form">
            <div class="form-group">
                <label for="username">ユーザー名またはメールアドレス</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">ログイン</button>
        </form>
        <p class="auth-link">
            アカウントをお持ちでない方は<a href="register.php">こちら</a>
        </p>
    </div>
    <script src="js/main.js"></script>
</body>
</html>