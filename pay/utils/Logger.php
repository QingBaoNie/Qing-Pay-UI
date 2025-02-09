<?php

class Logger {
    private static $logFile;
    
    public static function init() {
        self::$logFile = $_SERVER['DOCUMENT_ROOT'] . "/log/log.log";
        self::ensureLogDirectory();
    }
    
    /**
     * 记录日志
     * @param string $type 日志类型
     * @param string $message 日志信息
     * @param mixed $data 相关数据
     * @param string $client_ip 客户端IP
     */
    public static function log($type, $message, $data = null, $client_ip = '') {
        $log = sprintf(
            "[%s] 【%s】 %s%s %s\n",
            date('Y-m-d H:i:s'),
            $type,
            $message,
            $client_ip ? " | IP: {$client_ip}" : "",
            $data ? "数据: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : ""
        );
        file_put_contents(self::$logFile, $log, FILE_APPEND);
    }
    
    private static function ensureLogDirectory() {
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }
}

// 初始化日志系统
Logger::init();
