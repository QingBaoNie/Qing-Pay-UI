<?php
// **商户信息**
$merchant_id = "用户id";
$api_key = trim("用户密钥"); 
$notify_url = "https://域名/pay/notify.php"; 
$return_url = "https://域名/success.html"; 
$payment_url = "https://易支付网关/mapi.php"; 


$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : "";
$client_ip = $_SERVER['REMOTE_ADDR']; 

if ($amount <= 0 || empty($payment_method)) {
    die(json_encode(["code" => 0, "msg" => "参数错误"]));
}


$order_id = date("YmdHis");

// **请求参数**
$params = [
    "pid"          => $merchant_id,
    "type"         => $payment_method,
    "out_trade_no" => $order_id,
    "notify_url"   => $notify_url,
    "return_url"   => $return_url,
    "name"         => "在线付款",
    "money"        => number_format($amount, 2, '.', ''),
    "clientip"     => $client_ip,
    "sign_type"    => "MD5"
];

// **计算签名**
$params["sign"] = getSign($params, $api_key);

// **发送请求**
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $payment_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);


$result = json_decode($response, true);


$log_content = "API返回数据: " . print_r($result, true) . "\n";
file_put_contents("log.txt", $log_content, FILE_APPEND);


if ($result["code"] == 1) {
    echo json_encode([
        "code" => 1,
        "msg" => "支付成功",
        "qrcode" => $result["qrcode"] ?? "",
        "payurl" => $result["payurl"] ?? "",
        "out_trade_no" => $order_id,
        "trade_no" => $result["trade_no"] ?? "", 
        "money" => number_format($amount, 2, '.', ''), 
        "payment_method" => $payment_method, 
        "pay_time" => date("Y-m-d H:i:s") 
    ]);
} else {
    echo json_encode(["code" => 0, "msg" => $result["msg"]]);
}


function getSign($params, $api_key) {
    ksort($params);
    reset($params);
    
    $sign_string = "";
    foreach ($params as $key => $value) {
        if ($key != "sign" && $key != "sign_type" && $value !== '') {
            $sign_string .= "$key=$value&";
        }
    }

    $sign_string = rtrim($sign_string, "&");
    $sign_string .= $api_key;

    return md5($sign_string);
}
?>
