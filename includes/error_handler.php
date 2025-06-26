<?php
// エラーハンドリング用の共通関数

/**
 * APIエラーレスポンスを返す
 */
function sendErrorResponse($message, $code = 400)
{
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

/**
 * APIサクセスレスポンスを返す
 */
function sendSuccessResponse($data = [], $message = '')
{
    $response = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    if (!empty($message)) {
        $response['message'] = $message;
    }

    $response = array_merge($response, $data);

    echo json_encode($response);
    exit();
}

/**
 * データベースエラーをログに記録
 */
function logDatabaseError($error, $context = '')
{
    $logMessage = date('Y-m-d H:i:s') . " [DB ERROR] ";
    if (!empty($context)) {
        $logMessage .= "[$context] ";
    }
    $logMessage .= $error . "\n";

    error_log($logMessage, 3, 'logs/database_errors.log');
}
