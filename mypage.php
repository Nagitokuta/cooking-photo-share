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
    <title>マイページ | 料理写真共有プラットフォーム</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/feed.css">
    <link rel="stylesheet" href="css/mypage.css">
</head>

<body>
    <header class="header">
        <div class="header-content">
            <h1 class="logo">料理写真共有プラットフォーム</h1>
            <nav class="nav">
                <a href="upload.php" style="white-space: nowrap;" class="btn btn-primary">
                    <span class="btn-icon">📷</span>
                    写真を投稿
                </a>
                <a href="feed.php" style="white-space: nowrap;" class="btn btn-secondary">フィードに戻る</a>
                <button id="logoutBtn" style="white-space: nowrap;" class="btn btn-outline">ログアウト</button>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <!-- プロフィールセクション -->
            <div class="profile-section">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                    </div>
                    <div class="profile-info">
                        <h2 class="profile-username"><?php echo htmlspecialchars($currentUser['username']); ?></h2>
                        <p class="profile-email"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                        <div class="profile-stats" id="profileStats">
                            <div class="stat-item">
                                <span class="stat-number" id="postsCount">-</span>
                                <span class="stat-label">投稿</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="likesCount">-</span>
                                <span class="stat-label">いいね</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="commentsCount">-</span>
                                <span class="stat-label">コメント</span>
                            </div>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <button id="editProfileBtn" class="btn btn-secondary">プロフィール編集</button>
                    </div>
                </div>
            </div>

            <!-- 投稿一覧セクション -->
            <div class="posts-section">
                <div class="section-header">
                    <h3>投稿一覧</h3>
                    <div class="view-toggle">
                        <button id="gridViewBtn" class="view-btn active">グリッド</button>
                        <button id="listViewBtn" class="view-btn">リスト</button>
                    </div>
                </div>

                <div id="myPosts" class="posts-grid">
                    <!-- 投稿一覧はJavaScriptで動的に読み込み -->
                    <div class="loading-container">
                        <div class="loading-spinner"></div>
                        <p class="loading-text">投稿を読み込み中...</p>
                    </div>
                </div>

                <!-- 投稿がない場合の表示 -->
                <div id="emptyPosts" class="empty-state" style="display: none;">
                    <div class="empty-icon">📷</div>
                    <h3>まだ投稿がありません</h3>
                    <p>最初の料理写真を投稿してみませんか？</p>
                    <a href="upload.php" class="btn btn-primary">写真を投稿する</a>
                </div>
            </div>
        </div>
    </main>

    <!-- プロフィール編集モーダル -->
    <div id="editProfileModal" class="modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>プロフィール編集</h3>
                <button class="modal-close" id="closeEditModal">×</button>
            </div>
            <form id="editProfileForm" class="modal-body">
                <div class="form-group">
                    <label for="editUsername">ユーザー名</label>
                    <input type="text" id="editUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="editEmail">メールアドレス</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="editPassword">新しいパスワード（変更する場合のみ）</label>
                    <input type="password" id="editPassword" name="password">
                </div>
                <div class="form-group">
                    <label for="editPasswordConfirm">パスワード確認</label>
                    <input type="password" id="editPasswordConfirm" name="password_confirm">
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <button type="button" class="btn btn-secondary" id="cancelEditBtn">キャンセル</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script src="js/mypage.js"></script>
</body>

</html>