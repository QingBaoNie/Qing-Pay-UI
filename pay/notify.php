<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/IpUtil.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/OrderFile.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/SignatureUtil.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/RequestValidator.php';

// 加载配置
$config = require $_SERVER['DOCUMENT_ROOT'] . '/pay/config/pay_config.php';

// 获取客户端IP
$client_ip = IpUtil::getClientIp();

// 记录回调流水日志
Logger::log("回调", "收到回调请求", [
    "trade_status" => $_GET['trade_status'] ?? '',
    "order_id" => $_GET['out_trade_no'] ?? '',  
    "trade_no" => $_GET['trade_no'] ?? ''
], $client_ip);

// 验证必填参数
$validationResult = RequestValidator::validateNotify($_GET, $config['merchant_id']);
if (!$validationResult['valid']) {
    Logger::log('回调', $validationResult['msg'], $_GET, $client_ip);
    die("fail");
}

// 直接验证签名
$provided_sign = $_GET['sign'];
$calculated_sign = SignatureUtil::generateSign($_GET, $config['api_key']);

if ($provided_sign !== $calculated_sign) {
    // 签名验证失败记录流水日志和错误日志
    Logger::log("支付系统", "回调签名验证失败", [
        "order_id" => $_GET['out_trade_no'] ?? '',  
        "provided_sign" => $provided_sign,
        "calculated_sign" => $calculated_sign
    ], $client_ip);
    Logger::log('回调', '支付回调签名验证失败', $_GET, $client_ip);
    die("fail");
}


// 统一格式化金额为两位小数
$_GET['money'] = number_format(floatval($_GET['money']), 2, '.', '');

if ($_GET['trade_status'] === "TRADE_SUCCESS") {
    $order_id = $_GET['out_trade_no'];  
    $trade_no = $_GET['trade_no'];
    $notify_money = floatval($_GET['money'] ?? 0);
    $notify_time = $_GET['notify_time'] ?? date('Y-m-d H:i:s');
    
    // 获取订单数据,验证金额
    $orderData = OrderFile::getData($order_id);
    if ($orderData) {
        // 如果订单已经是支付状态,直接返回success
        if ($orderData['trade_status'] === OrderFile::STATUS_PAID) {
            Logger::log("支付系统", "订单已支付,忽略回调", [
                "order_id" => $order_id,
                "trade_no" => $trade_no
            ], $client_ip);
            echo "success";
            exit;
        }

        $order_money = floatval($orderData['money']);
        // 金额不一致
        if (abs($order_money - $notify_money) > 0.01) {
            OrderFile::updateStatus($order_id, OrderFile::STATUS_AMOUNT_MISMATCH);
            Logger::log("支付系统", "回调金额不匹配", [
                "order_id" => $order_id,
                "order_money" => $order_money,
                "notify_money" => $notify_money
            ], $client_ip);
            Logger::log('回调', '支付回调金额与订单金额不匹配', [
                'order_money' => $order_money,
                'notify_money' => $notify_money
            ], $client_ip);
            echo "success";  // 改为返回success,避免重复回调
            exit;
        }
    }
    // 如果订单不存在,记录通知金额
    else {
        $orderData = [
            'order_id' => $order_id,  // 使用order_id
            'money' => $notify_money
        ];
    }
    
    // 更新为已支付状态,增加notify_time参数
    OrderFile::updateStatus($order_id, OrderFile::STATUS_PAID, $trade_no, $notify_time);
    
    // 记录流水日志时也记录支付时间
    Logger::log("支付系统", "支付成功", [
        "order_id" => $order_id,  
        "trade_no" => $trade_no,
        "money" => $notify_money,
        "notify_time" => $notify_time,
        "trade_status" => OrderFile::STATUS_PAID
    ], $client_ip);
    
    echo "success";
} else {
    // 更新为异常状态
    $order_id = $_GET['out_trade_no'] ?? '';  
    OrderFile::updateStatus($order_id, OrderFile::STATUS_EXCEPTION);
    // 支付异常记录流水日志和错误日志
    Logger::log("支付系统", "支付状态异常", [
        "order_id" => $order_id,  
        "trade_status" => $_GET['trade_status'] ?? ''
    ], $client_ip);
    Logger::log('回调', '支付状态错误', $params, $client_ip);
    echo "fail";
}
?>
