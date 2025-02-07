<?php

$merchant_key = "商户密钥"; 


file_put_contents("log.txt", "收到支付平台回调: " . json_encode($_GET) . "\n", FILE_APPEND);


$params = $_GET;

// **计算签名**
function verifySign($params, $merchant_key) {
    ksort($params); // 按照 ASCII 升序排序
    $sign_string = "";
    foreach ($params as $key => $value) {
        if ($key != "sign" && $key != "sign_type" && !empty($value)) {
            $sign_string .= "$key=$value&";
        }
    }
    $sign_string = rtrim($sign_string, "&") . $merchant_key; // 拼接密钥
    return md5($sign_string);
}


$sign = $_GET['sign'] ?? "";
$calculated_sign = verifySign($params, $merchant_key);

if ($sign !== $calculated_sign) {
    file_put_contents("log.txt", "【错误】支付回调签名验证失败: " . json_encode($params) . "\n", FILE_APPEND);
    die("fail"); 
}


if ($_GET['trade_status'] === "TRADE_SUCCESS") {
    $order_id = $_GET['out_trade_no']; 
    $trade_no = $_GET['trade_no']; 
    $amount = $_GET['money']; 
    $payment_type = $_GET['type']; 

  
    file_put_contents("log.txt", "【成功】订单号=$order_id, 交易号=$trade_no, 金额=$amount, 支付方式=$payment_type\n", FILE_APPEND);

    
    
    echo "success"; 
} else {
    file_put_contents("log.txt", "【失败】支付状态错误: " . json_encode($params) . "\n", FILE_APPEND);
    echo "fail"; 
}
?>
