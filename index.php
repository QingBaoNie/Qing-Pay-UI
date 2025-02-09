<?php
// 引入配置加载器
require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/utils/EnvLoader.php';
// 加载配置
EnvLoader::load();
// 读取支付配置
$pay_config = require_once $_SERVER['DOCUMENT_ROOT'] . '/pay/config/pay_config.php';
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qing🚀 - 在线收款</title>
    
    <!-- 引入外部资源 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- 引入自定义CSS和JS -->
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen px-4">
    <div class="bg-white shadow-xl rounded-lg p-6 max-w-md w-full fade-in">
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-4">💰 Qing - 在线收款 🚀</h2>

        <div class="space-y-2">
            <label class="block text-gray-700 font-medium flex items-center">
                <span class="text-blue-500 text-lg">💵</span> <span class="ml-2">选择支付金额</span>
            </label>
            <select id="amount-select" class="w-full h-10 p-2 border rounded-md bg-gray-100 focus:ring-green-500 focus:border-green-500 transition">
                <option value="10">¥10</option>
                <option value="20">¥20</option>
                <option value="30">¥30</option>
                <option value="40">¥40</option>
                <option value="50">¥50</option>
                <option value="60">¥60</option>
                <option value="70">¥70</option>
                <option value="80">¥80</option>
                <option value="90">¥90</option>
                <option value="100">¥100</option>
                <option value="custom">其他</option>
            </select>
            <input type="number" id="custom-amount" class="w-full h-10 p-2 border rounded-md bg-gray-100 focus:ring-green-500 focus:border-green-500 transition hidden" placeholder="请输入自定义金额">
        </div>

        <div class="mt-4">
            <label class="block text-gray-700 font-medium flex items-center">
                <span class="text-blue-500 text-lg">💳</span> <span class="ml-2">选择支付方式</span>
            </label>
            <div class="flex gap-2 mt-2">
                <button id="wxpay-btn" class="h-10 flex-1 rounded-md bg-green-500 text-white hover:bg-green-600 focus:ring-2 focus:ring-green-500 selected">微信支付</button>
                <button id="alipay-btn" class="h-10 flex-1 rounded-md bg-blue-500 text-white hover:bg-blue-600 focus:ring-2 focus:ring-blue-500">支付宝</button>
            </div>
        </div>

        <button id="submit" class="w-full h-10 mt-4 bg-gray-900 text-white rounded-md hover:bg-gray-800 focus:ring-2 focus:ring-gray-900">前往付款</button>

        <p id="result" class="text-center text-red-500 mt-4 hidden"></p>

        <div id="qrcode-container" class="flex flex-col items-center mt-4 bg-gray-100 p-4 rounded-lg shadow-lg hidden">
            <p id="order-info" class="text-gray-700 text-lg font-medium"></p>
            <div id="qr-loader" class="qr-loading"></div>
            <div id="qrcode" class="hidden mt-3"></div>
        </div>
    </div>
    
    <script src="/assets/js/cursor.js"></script>
    <script src="/assets/js/app.js"></script></body>
</html>
