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
    <title>写真を投稿 | 料理写真共有プラットフォーム</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/feed.css">
    <link rel="stylesheet" href="css/upload.css">
</head>

<body>
    <header class="header">
        <div class="header-content">
            <h1 class="logo">料理写真共有プラットフォーム</h1>
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
            <div class="upload-container">
                <h2>写真を投稿する</h2>

                <form id="uploadForm" class="upload-form" enctype="multipart/form-data">
                    <!-- 写真アップロード部分 -->
                    <div class="form-group">
                        <label for="photo" class="upload-label">
                            <span class="label-text">写真を選択</span>
                            <span class="label-help">JPG、PNG、GIF形式（最大5MB）</span>
                        </label>

                        <div class="upload-area" id="uploadArea">
                            <input type="file" id="photo" name="photo" accept="image/*" required>
                            <div class="upload-placeholder">
                                <div class="upload-icon">📷</div>
                                <p class="upload-text">
                                    <span class="primary-text">クリックして写真を選択</span><br>
                                    <span class="secondary-text">または、ここにファイルをドラッグ&ドロップ</span>
                                </p>
                            </div>
                            <div class="image-preview" id="imagePreview" style="display: none;">
                                <img id="previewImage" src="" alt="プレビュー">
                                <button type="button" class="remove-image" id="removeImage">×</button>
                            </div>
                        </div>
                    </div>

                    <!-- 説明文入力部分 -->
                    <div class="form-group">
                        <label for="description" class="description-label">
                            <span class="label-text">説明文</span>
                            <span class="char-count">
                                <span id="charCount">0</span>/500文字
                            </span>
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            maxlength="500"
                            placeholder="料理の説明や作り方のポイントなど、自由に書いてください！"></textarea>
                    </div>

                    <!-- 投稿ボタン -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large" id="submitBtn">
                            <span class="btn-text">投稿する</span>
                            <span class="btn-loading" style="display: none;">投稿中...</span>
                        </button>
                        <button type="button" class="btn btn-secondary btn-large" id="cancelBtn">
                            キャンセル
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>
    <script src="js/upload.js"></script>
</body>

</html>