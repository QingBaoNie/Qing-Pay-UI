<?php
session_start();

// 验证请求合法性，改用POST方式
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id']) || !isset($_POST['money']) || !isset($_POST['type'])) {
    header('Location: /');
    exit;
}

// 验证订单状态
$order_id = $_POST['order_id'];
$money = $_POST['money'];
$type = $_POST['type'];
$pay_time = $_POST['pay_time'] ?? date('Y-m-d H:i:s');

// TODO: 这里可以添加数据库验证逻辑
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>支付成功!!!</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div id="wrapper">
        <div class="card">
            <div class="icon"></div>
            <h1>Hi, 订单支付成功!</h1>
            <p>Hi, the order payment is successful!!</p>
        </div>
        <div class="card">
            <ul>
                <li>
                    <span>订单号</span>
                    <span id="order-id"><?php echo htmlspecialchars($order_id); ?></span>
                </li>
                <li>
                    <span>支付方式</span>
                    <span id="payment-type"><?php echo htmlspecialchars($type); ?></span>
                </li>
                <li>
                    <span>支付金额</span>
                    <span id="payment-amount"><?php echo htmlspecialchars($money); ?></span>
                </li>
                <li>
                    <span>支付时间</span>
                    <span id="payment-time"><?php echo htmlspecialchars($pay_time); ?></span>
                </li>
            </ul>
        </div>
        <div class="card">
            <div class="cta-row">
                <button class="secondary" onclick="window.location.href='/'">返回首页</button>
                <button class="primary" onclick="window.location.href='https://t.me/QinGdo'">联系晴宝</button>
            </div>
        </div>
    </div>
    <script src="/assets/js/cursor.js"></script>
    <script src="/assets/js/app.js"></script></body>
</html>
