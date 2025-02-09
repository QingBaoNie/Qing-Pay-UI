<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/OrderUtil.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/SignatureUtil.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/Logger.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/IpUtil.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/EnvLoader.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/OrderFile.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/HttpRequestUtil.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/RequestValidator.php';

// 加载配置
$config = require $_SERVER['DOCUMENT_ROOT'] . '/pay/config/pay_config.php';

// 获取请求参数
$money = isset($_POST['money']) ? trim($_POST['money']) : "";
$type = isset($_POST['type']) ? trim($_POST['type']) : "";
$param = isset($_POST['param']) ? trim($_POST['param']) : "";
$device = isset($_POST['device']) ? trim($_POST['device']) : "pc";
$client_ip = IpUtil::getClientIp();

// 参数验证
$validationResult = RequestValidator::validateCreateOrder($money, $type, $device);
if (!$validationResult['valid']) {
    die(json_encode(["code" => 0, "msg" => $validationResult['msg']]));
}
$device = $validationResult['device'];

// 生成订单号
$order_id = OrderUtil::generateOrderId($client_ip);

// 创建订单日志,检查是否重复
if (!OrderFile::create($order_id, $money, $type, $client_ip)) {
    die(json_encode(["code" => 0, "msg" => "订单号已存在"]));
}

// 记录流水日志
Logger::log("支付", "创建支付", [
    "order_id" => $order_id,
    "money" => $money,
    "type" => $type
], $client_ip);
// 构建支付参数
$params = [
    "pid" => $config['merchant_id'],
    "type" => $type,
    "out_trade_no" => $order_id,  // 
    "notify_url" => $config['notify_url'],
    "return_url" => $config['return_url'],
    "name" => $order_id,  // 直接使用订单号作为商品名称
    "money" => number_format(floatval($money), 2, '.', ''),
    "clientip" => $client_ip,
    "device" => $device,
    "param" => $param,
    "sign_type" => "MD5"
];

// 添加签名
$params["sign"] = SignatureUtil::generateSign($params, $config['api_key']);

// 发送支付请求
$response = HttpRequestUtil::sendPayRequest($config['payment_url'], $params);

// 处理错误
if($response === false || (isset($response['error']) && $response['error'])) {
    Logger::log("支付", "支付请求失败", [
        "order_id" => $order_id, 
        "error" => isset($response['error']) ? $response['error'] : "请求失败",
        "params" => $params
    ], $client_ip);
    die(json_encode(["code" => 0, "msg" => "请求支付接口失败"]));
}

// 确保response字段存在
$responseData = isset($response['response']) ? $response['response'] : '';
if(empty($responseData)) {
    Logger::log("支付", "响应数据为空", [
        "order_id" => $order_id,
        "response" => $response
    ], $client_ip);
    die(json_encode(["code" => 0, "msg" => "支付接口未返回数据"]));
}

// 解析响应数据
$result = json_decode($responseData, true);

// 处理错误
if($response['error']) {
    Logger::log("支付", "支付请求失败", [
        "order_id" => $order_id, 
        "error" => $response['error'],
        "params" => $params
    ], $client_ip);
    die(json_encode(["code" => 0, "msg" => "请求支付接口失败"]));
}

// 解析响应数据
$result = json_decode($response['response'], true);

if(json_last_error() !== JSON_ERROR_NONE || !is_array($result)) {
    $error_msg = json_last_error() !== JSON_ERROR_NONE ? 
                 "JSON解析错误: " . json_last_error_msg() : 
                 "返回数据格式错误";
    Logger::log("支付", "数据解析失败", [
        "order_id" => $order_id, 
        "error" => $error_msg,
        "response" => substr($response['response'], 0, 1000)
    ], $client_ip);
    die(json_encode(["code" => 0, "msg" => "接口返回数据异常"]));
}

// 处理业务响应
if (isset($result["code"]) && $result["code"] == 1) {
    Logger::log("支付", "订单创建成功", [
        "order_id" => $order_id,
        "trade_no" => $result["trade_no"] ?? "",
        "money" => $money,
        "type" => $type
    ], $client_ip);
    
    $response_data = [
        "code" => 1,
        "msg" => "订单创建成功",
        "order_id" => $order_id, 
        "trade_no" => $result["trade_no"] ?? "", 
        "money" => number_format(floatval($money), 2, '.', ''),
        "type" => $type,
        "pay_time" => date("Y-m-d H:i:s")
    ];
    
    // 根据返回数据类型添加支付链接
    if (!empty($result["payurl"])) {
        $response_data["payurl"] = $result["payurl"];
    } elseif (!empty($result["qrcode"])) {
        $response_data["qrcode"] = $result["qrcode"];
    } elseif (!empty($result["urlscheme"])) {
        $response_data["urlscheme"] = $result["urlscheme"];
    }
    
    echo json_encode($response_data);
} else {
    Logger::log("支付", "订单创建失败", [
        "order_id" => $order_id,
        "error" => $result["msg"] ?? "创建订单失败",
        "result" => $result
    ], $client_ip);
    echo json_encode(["code" => 0, "msg" => $result["msg"] ?? "创建订单失败"]);
}
?>
