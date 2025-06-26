<?php
// セッション管理の共通処理

// セッションの設定を強化
function initializeSession()
{

    // セッション開始
    if (session_status() === PHP_SESSION_NONE) {
        // セッションの設定
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_secure', 0); // HTTPSの場合は1に設定

        session_start();
    }

    // セッションハイジャック対策
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }

    // セッションの有効期限チェック（30分）
    if (
        isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity'] > 1800)
    ) {
        destroySession();
        return false;
    }

    $_SESSION['last_activity'] = time();
    return true;
}

// ログイン状態をチェックする関数
function checkLogin()
{
    if (!initializeSession()) {
        return false;
    }

    return isset($_SESSION['user_id']) &&
        isset($_SESSION['username']) &&
        isset($_SESSION['email']);
}

// ログインが必要なページでリダイレクトする関数
function requireLogin()
{
    if (!checkLogin()) {
        header('Location: index.php?error=login_required');
        exit();
    }
}

// 現在のユーザー情報を取得する関数
function getCurrentUser()
{
    if (!checkLogin()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email']
    ];
}

// ログイン済みユーザーを認証画面からリダイレクトする関数
function redirectIfLoggedIn()
{
    if (checkLogin()) {
        header('Location: feed.php');
        exit();
    }
}

// セッションを破棄する関数
function destroySession()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = array();

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }

        session_destroy();
    }
}

// ユーザーをログインさせる関数
function loginUser($user)
{
    initializeSession();

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();

    // セッションIDを再生成（セキュリティ強化）
    session_regenerate_id(true);
}

// ユーザーをログアウトさせる関数
function logoutUser()
{
    destroySession();
}
