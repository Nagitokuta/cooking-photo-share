// 写真投稿画面専用のJavaScript

document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const photoInput = document.getElementById('photo');
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const removeImageBtn = document.getElementById('removeImage');
    const uploadPlaceholder = uploadArea.querySelector('.upload-placeholder');
    const descriptionTextarea = document.getElementById('description');
    const charCount = document.getElementById('charCount');
    const uploadForm = document.getElementById('uploadForm');
    const submitBtn = document.getElementById('submitBtn');
    const cancelBtn = document.getElementById('cancelBtn');

    // ファイル選択時の処理
    photoInput.addEventListener('change', handleFileSelect);
    
    // ドラッグ&ドロップの処理
    uploadArea.addEventListener('dragover', handleDragOver);
    uploadArea.addEventListener('dragleave', handleDragLeave);
    uploadArea.addEventListener('drop', handleDrop);
    
    // 画像削除ボタンの処理
    removeImageBtn.addEventListener('click', removeImage);
    
    // 文字数カウントの処理
    descriptionTextarea.addEventListener('input', updateCharCount);
    
    // フォーム送信の処理
    uploadForm.addEventListener('submit', handleSubmit);
    
    // キャンセルボタンの処理
    cancelBtn.addEventListener('click', handleCancel);

    // ファイル選択処理
    function handleFileSelect(e) {
        const file = e.target.files[0];
        if (file) {
            validateAndPreviewFile(file);
        }
    }

    // ドラッグオーバー処理
    function handleDragOver(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    }

    // ドラッグリーブ処理
    function handleDragLeave(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    }

    // ドロップ処理
    function handleDrop(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            photoInput.files = files;
            validateAndPreviewFile(file);
        }
    }

    // ファイルバリデーションとプレビュー
    function validateAndPreviewFile(file) {
        // ファイルタイプの確認
        if (!file.type.startsWith('image/')) {
            showError('画像ファイルを選択してください');
            return;
        }

        // ファイルサイズの確認（5MB）
        if (file.size > 5 * 1024 * 1024) {
            showError('ファイルサイズは5MB以下にしてください');
            return;
        }

        // エラー状態をクリア
        clearError();

        // プレビュー表示
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            uploadPlaceholder.style.display = 'none';
            imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    // 画像削除
    function removeImage() {
        photoInput.value = '';
        previewImage.src = '';
        uploadPlaceholder.style.display = 'block';
        imagePreview.style.display = 'none';
        clearError();
    }

    // 文字数カウント更新
    function updateCharCount() {
        const count = descriptionTextarea.value.length;
        charCount.textContent = count;
        
        if (count > 450) {
            charCount.parentElement.classList.add('warning');
        } else {
            charCount.parentElement.classList.remove('warning');
        }
    }

    // フォーム送信処理
    function handleSubmit(e) {
        e.preventDefault();

        // バリデーション
        if (!photoInput.files[0]) {
            showError('写真を選択してください');
            return;
        }

        // ボタンを無効化
        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-text').style.display = 'none';
        submitBtn.querySelector('.btn-loading').style.display = 'inline';

        // FormDataを作成
        const formData = new FormData();
        formData.append('photo', photoInput.files[0]);
        formData.append('description', descriptionTextarea.value);

        // APIに送信
        fetch('api/upload_photo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('レスポンス:', data);
            if (data.success) {
                showMessage('写真を投稿しました！', 'success');
                setTimeout(() => {
                    window.location.href = 'feed.php';
                }, 1500);
            } else {
                showError(data.message || '投稿に失敗しました');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('エラーが発生しました');
        })
        .finally(() => {
            // ボタンを有効化
            submitBtn.disabled = false;
            submitBtn.querySelector('.btn-text').style.display = 'inline';
            submitBtn.querySelector('.btn-loading').style.display = 'none';
        });
    }

    // キャンセル処理
    function handleCancel() {
        if (photoInput.files[0] || descriptionTextarea.value) {
            if (confirm('入力内容が失われますが、よろしいですか？')) {
                window.location.href = 'feed.php';
            }
        } else {
            window.location.href = 'feed.php';
        }
    }

    // エラー表示
    function showError(message) {
        uploadArea.classList.add('error');
        
        // 既存のエラーメッセージを削除
        const existingError = uploadArea.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // 新しいエラーメッセージを追加
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        uploadArea.appendChild(errorDiv);
    }

    // エラークリア
    function clearError() {
        uploadArea.classList.remove('error');
        const errorMessage = uploadArea.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    }

    // メッセージ表示（main.jsの関数を使用）
    function showMessage(message, type) {
        if (typeof window.showMessage === 'function') {
            window.showMessage(message, type);
        } else {
            alert(message);
        }
    }
});