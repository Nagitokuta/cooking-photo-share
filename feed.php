<?php
require_once 'includes/config.php';
require_once 'includes/session.php';

// セッション初期化
initializeSession();

// ログインチェック
requireLogin();

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>フィード | 料理写真共有プラットフォーム</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/feed.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1 class="logo">料理写真共有プラットフォーム</h1>
            <nav class="nav">
                <span class="welcome">こんにちは、<?php echo htmlspecialchars($currentUser['username']); ?>さん</span>
                <a href="upload.php" class="btn btn-primary">写真を投稿</a>
                <a href="mypage.php" class="btn btn-secondary">マイページ</a>
                <button id="logoutBtn" class="btn btn-outline">ログアウト</button>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <h2>新着料理写真</h2>
            <div id="photoFeed" class="photo-feed">
                <!-- 写真一覧はJavaScriptで動的に読み込み -->
                <p class="loading">写真を読み込み中...</p>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>
    <script src="js/feed.js"></script>
</body>
</html>