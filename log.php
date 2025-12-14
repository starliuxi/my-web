<?php
// 强制禁用缓存和压缩
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Content-Type: image/png');

// 中国时区 + 超时控制
date_default_timezone_set('Asia/Shanghai');
set_time_limit(3);

// 增强版IP获取（兼容移动网络）
function getRealIp() {
    $ip = '';
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) break;
        }
    }
    return $ip ?: 'unknown';
}

// 自动创建日志目录
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
    @file_put_contents("$logDir/.htaccess", "Deny from all");
}

// 记录数据（包含设备检测）
$isMobile = preg_match('/iPhone|Android|Mobile|iPad/i', $_SERVER['HTTP_USER_AGENT'] ?? '');
$log = sprintf(
    "[%s] %-15s | %-6s | %s | %s\n",
    date('Y-m-d H:i:s'),
    getRealIp(),
    $isMobile ? 'MOBILE' : 'PC',
    $_SERVER['HTTP_REFERER'] ?? ($_GET['from'] ?? 'direct'),
    substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 120) // 截断过长UA
);

// 原子化写入（三重重试机制）
$logFile = "$logDir/ip_" . date('Y-m-d') . ".txt";
for ($i = 0; $i < 3; $i++) {
    if (@file_put_contents($logFile, $log, FILE_APPEND | LOCK_EX)) break;
    usleep(200000); // 200ms延迟后重试
}

// 返回1x1透明PNG
echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');