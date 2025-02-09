<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/IpUtil.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/OrderFile.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/RequestValidator.php';

$order_id = $_GET['order_id'] ?? "";
$client_ip = IpUtil::getClientIp();

// 记录查询请求流水日志
Logger::log("支付", "查询订单", [
    "order_id" => $order_id
], $client_ip);

// 参数验证
$validationResult = RequestValidator::validateQuery($order_id);
if (!$validationResult['valid']) {
    Logger::log("支付", "查询参数错误", [
        "order_id" => $order_id,
        "error" => $validationResult['msg']
    ], $client_ip);
    die(json_encode([
        "trade_status" => "error",
        "message" => $validationResult['msg']
    ]));
}

// 检查订单是否存在
$orderData = OrderFile::getData($order_id);
if (!$orderData) {
    Logger::log("支付", "订单不存在", [
        "order_id" => $order_id,
        "client_ip" => $client_ip
    ], $client_ip);
    die(json_encode([
        "trade_status" => "error",
        "message" => "订单不存在"
    ]));
}

// 检查订单状态
$statusResult = OrderFile::checkStatus($order_id);

// 记录查询结果
Logger::log("支付", "查询完成", [
    "order_id" => $order_id,
    "order_data" => $orderData,
    "status" => $statusResult,
    "client_ip" => $client_ip
], $client_ip);

// 根据订单状态设置响应
echo json_encode([
    "data" => [
        "order_id" => $orderData['order_id'],      // 商户订单号
        "money" => $orderData['money'],            // 订单金额
        "type" => $orderData['type'],              // 支付方式
        "create_time" => $orderData['create_time'],    // 订单创建时间
        "pay_time" => $orderData['notify_time'] ?? '',  // 支付完成时间（回调通知时间）
        "trade_status" => $statusResult['success'] ? "PAID" : "UNPAID",  // 统一使用PAID/UNPAID状态
        "payment_status" => [
            "success" => $statusResult['success'],
            "message" => $statusResult['message']
        ]
    ]
]);
?>
