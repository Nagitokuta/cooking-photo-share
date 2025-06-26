<?php
// ログ管理クラス

define('DEBUG', true);

class Logger
{
    private $logDir = 'logs/';

    public function __construct()
    {
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function log($level, $message, $context = [])
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message";

        if (!empty($context)) {
            $logEntry .= ' ' . json_encode($context);
        }

        $logEntry .= "\n";

        $filename = $this->logDir . date('Y-m-d') . '.log';
        file_put_contents($filename, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public function error($message, $context = [])
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning($message, $context = [])
    {
        $this->log('WARNING', $message, $context);
    }

    public function info($message, $context = [])
    {
        $this->log('INFO', $message, $context);
    }

    public function debug($message, $context = [])
    {
        if (defined('DEBUG') && DEBUG) {
            $this->log('DEBUG', $message, $context);
        }
    }
}

// グローバルロガーインスタンス
$logger = new Logger();
