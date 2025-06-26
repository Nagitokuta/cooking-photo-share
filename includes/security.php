<?php
// セキュリティ関連の設定

// セキュリティヘッダーを設定
function setSecurityHeaders()
{
    // XSS対策
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');

    // HTTPS強制（本番環境）
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    // CSP（Content Security Policy）
    $csp = "default-src 'self'; ";
    $csp .= "script-src 'self' 'unsafe-inline'; ";
    $csp .= "style-src 'self' 'unsafe-inline'; ";
    $csp .= "img-src 'self' data: blob:; ";
    $csp .= "font-src 'self'; ";
    header("Content-Security-Policy: " . $csp);
}

// 入力値のサニタイズ
function sanitizeInput($input)
{
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }

    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// CSRF対策トークン生成
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF対策トークン検証
function verifyCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
