<?php
$merchant_key = "商户密钥"; // 替换成你的支付密钥
$log_file = "log.txt";


$order_id = $_GET['order_id'] ?? "";


$log_content = file_get_contents($log_file);
$is_paid = strpos($log_content, "【成功】订单号=$order_id") !== false;

echo json_encode(["status" => $is_paid ? "success" : "pending"]);
?>
