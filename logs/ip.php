<?php
// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 安全验证 - 简单密码保护
$valid_password = '110'; // 改成你的密码
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != 'admin' || $_SERVER['PHP_AUTH_PW'] != $valid_password) {
    header('WWW-Authenticate: Basic realm="Logs Access"');
    header('HTTP/1.0 401 Unauthorized');
    die('未授权访问');
}

// 定义日志目录
$logDir = __DIR__;
$logFiles = glob($logDir.'/ip_*.txt');

// 按日期排序日志文件
usort($logFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

// 处理查看请求
$currentLog = isset($_GET['log']) ? basename($_GET['log']) : '';
$logContent = '';
if ($currentLog && in_array($logDir.'/'.$currentLog, $logFiles)) {
    $logContent = file_get_contents($logDir.'/'.$currentLog);
}

// 处理删除请求
if (isset($_POST['delete']) && in_array($logDir.'/'.$_POST['delete'], $logFiles)) {
    unlink($logDir.'/'.$_POST['delete']);
    header('Location: view_logs.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>访问日志</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .log-files { float: left; width: 200px; }
        .log-content { margin-left: 220px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .delete-btn { color: red; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
       
        
        <div class="log-files">
            <h3>日志列表</h3>
            <ul>
                <?php foreach ($logFiles as $file): ?>
                    <?php $filename = basename($file); ?>
                    <li>
                        <a href="?log=<?= urlencode($filename) ?>"><?= $filename ?></a>
                        (<?= round(filesize($file)/1024, 2) ?>KB)
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="delete" value="<?= $filename ?>">
                            <button type="submit" class="delete-btn" onclick="return confirm('确定删除此日志文件？')">×</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="log-content">
            <?php if ($currentLog): ?>
                <h3> <?= $currentLog ?></h3>
                <div style="margin-bottom: 20px;">
                    <a href="download.php?file=<?= urlencode($currentLog) ?>" download>下载此日志</a>
                </div>
                <table>
                    <tr>
                        <th>时间 IP</th>
                        <th>用户端</th>
                        <th>URL</th>
                        <th>用户代理</th>
                    </tr>
                    <?php foreach (array_reverse(explode("\n", trim($logContent))) as $line): ?>
                        <?php if (!empty($line)): ?>
                            <?php $parts = explode(' | ', $line); ?>
                            <tr>
                                <td><?= htmlspecialchars($parts[0] ?? '') ?></td>
                                <td><?= htmlspecialchars($parts[1] ?? '') ?></td>
                                <td><?= htmlspecialchars($parts[2] ?? '') ?></td>
                                <td><?= htmlspecialchars($parts[3] ?? '') ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
               
            <?php endif; ?>
        </div>
    </div>
</body>
</html>