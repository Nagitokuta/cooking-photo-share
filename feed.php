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
            <h1 class="logo">
                <a href="feed.php">料理写真共有プラットフォーム</a>
            </h1>
            <nav class="nav">
                <span class="welcome">こんにちは、<?php echo htmlspecialchars($currentUser['username']); ?>さん</span>
                <a href="upload.php" class="btn btn-primary">
                    <span class="btn-icon">📷</span>
                    写真を投稿
                </a>
                <a href="mypage.php" class="btn btn-secondary">マイページ</a>
                <button id="logoutBtn" class="btn btn-outline">ログアウト</button>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="feed-header">
                <h2>新着料理写真</h2>
                <div class="feed-stats">
                    <span id="photoCount">読み込み中...</span>
                </div>
            </div>
            
            <div id="photoFeed" class="photo-feed">
                <!-- 写真一覧はJavaScriptで動的に読み込み -->
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                    <p class="loading-text">写真を読み込み中...</p>
                </div>
            </div>

            <!-- ページネーション -->
            <div id="pagination" class="pagination" style="display: none;">
                <button id="loadMoreBtn" class="btn btn-secondary btn-large">
                    <span class="btn-text">もっと見る</span>
                    <span class="btn-loading" style="display: none;">読み込み中...</span>
                </button>
            </div>

            <!-- 投稿がない場合の表示 -->
            <div id="emptyState" class="empty-state" style="display: none;">
                <div class="empty-icon">📷</div>
                <h3>まだ投稿がありません</h3>
                <p>最初の料理写真を投稿してみませんか？</p>
                <a href="upload.php" class="btn btn-primary">写真を投稿する</a>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>
    <script src="js/feed.js"></script>
</body>
</html>