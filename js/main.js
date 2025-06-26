// DOM読み込み完了後に実行
document.addEventListener('DOMContentLoaded', function() {
    // ログインフォームの処理
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    // 登録フォームの処理
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }

    // ログアウトボタンの処理
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }

    // セッション期限切れのチェック
    checkSessionStatus();
});

// ログイン処理
function handleLogin(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    // ボタンを無効化
    submitBtn.disabled = true;
    submitBtn.textContent = 'ログイン中...';
    
    fetch('api/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('ログインしました', 'success');
            setTimeout(() => {
                window.location.href = 'feed.php';
            }, 1000);
        } else {
            showMessage(data.message || 'ログインに失敗しました', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('エラーが発生しました', 'error');
    })
    .finally(() => {
        // ボタンを有効化
        submitBtn.disabled = false;
        submitBtn.textContent = 'ログイン';
    });
}

// 登録処理
function handleRegister(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    // パスワード確認
    if (formData.get('password') !== formData.get('password_confirm')) {
        showMessage('パスワードが一致しません', 'error');
        return;
    }
    
    // ボタンを無効化
    submitBtn.disabled = true;
    submitBtn.textContent = '登録中...';
    
    fetch('api/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('登録が完了しました', 'success');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1500);
        } else {
            showMessage(data.message || '登録に失敗しました', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('エラーが発生しました', 'error');
    })
    .finally(() => {
        // ボタンを有効化
        submitBtn.disabled = false;
        submitBtn.textContent = '登録する';
    });
}

// ログアウト処理
function handleLogout() {
    if (confirm('ログアウトしますか？')) {
        fetch('api/logout.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('ログアウトしました', 'success');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1000);
            } else {
                showMessage(data.message || 'ログアウトに失敗しました', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('エラーが発生しました', 'error');
        });
    }
}

// メッセージ表示関数
function showMessage(message, type = 'info') {
    // 既存のメッセージを削除
    const existingMessage = document.querySelector('.message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // 新しいメッセージを作成
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}-message`;
    messageDiv.textContent = message;
    
    // メッセージを挿入
    const container = document.querySelector('.container');
    const firstChild = container.firstElementChild;
    container.insertBefore(messageDiv, firstChild.nextSibling);
    
    // 3秒後に自動削除
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 3000);
}

// セッション状態のチェック
function checkSessionStatus() {
    // 5分ごとにセッション状態をチェック
    setInterval(() => {
        fetch('api/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (!data.valid) {
                showMessage('セッションが期限切れです。再度ログインしてください', 'error');
                setTimeout(() => {
                    window.location.href = 'index.php?error=session_expired';
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Session check error:', error);
        });
    }, 300000); // 5分 = 300000ms
}