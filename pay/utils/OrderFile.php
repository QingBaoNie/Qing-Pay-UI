<?php

class OrderFile {
    // 订单状态常量
    const STATUS_CREATED = 'CREATED';           // 订单已创建
    const STATUS_PAID = 'PAID';                 // 已支付（支付成功）
    const STATUS_AMOUNT_MISMATCH = 'AMOUNT_MISMATCH';  // 回调的支付金额和订单金额不符
    const STATUS_EXCEPTION = 'EXCEPTION';        // 支付异常
    
    private static $orderDir;
    
    public static function init() {
        self::$orderDir = $_SERVER['DOCUMENT_ROOT'] . "/log/orders/";
        self::ensureOrderDirectory();
    }
    
    /**
     * 创建订单文件
     * @return bool 是否成功创建(false表示订单已存在)
     */
    public static function create($order_id, $money, $type, $client_ip) {
        $orderFile = self::getOrderFilePath($order_id);
        if (file_exists($orderFile)) {
            return false;
        }

        $orderData = [
            'order_id' => $order_id,
            'money' => $money,
            'type' => $type,
            'client_ip' => $client_ip,
            'create_time' => date('Y-m-d H:i:s'),
            'trade_status' => self::STATUS_CREATED
        ];

        file_put_contents($orderFile, json_encode($orderData, JSON_PRETTY_PRINT));
        Logger::log('订单', "创建订单文件: {$order_id}", $orderData);
        return true;
    }

    /**
     * 更新订单状态
     */
    public static function updateStatus($order_id, $status, $trade_no = '', $notify_time = '', $extra_data = []) {
        $orderData = self::getData($order_id);
        if ($orderData) {
            $orderData['trade_status'] = $status;
            if ($trade_no) {
                $orderData['trade_no'] = $trade_no;
            }
            if ($notify_time) {
                $orderData['notify_time'] = $notify_time;
            }
            // 合并额外数据
            if (!empty($extra_data)) {
                $orderData = array_merge($orderData, $extra_data);
            }
            return self::saveData($order_id, $orderData);
        }
        return false;
    }

    /**
     * 获取订单数据
     * @return array|null 订单数据,不存在返回null
     */
    public static function getData($order_id) {
        $orderFile = self::getOrderFilePath($order_id);
        if (!file_exists($orderFile)) {
            return null;
        }
        return json_decode(file_get_contents($orderFile), true);
    }

    /**
     * 检查订单支付状态
     */
    public static function checkStatus($order_id) {
        $orderData = self::getData($order_id);
        if (!$orderData) {
            return ['success' => false, 'message' => '订单不存在'];
        }
        
        switch ($orderData['trade_status']) {
            case self::STATUS_PAID:
                return ['success' => true, 'message' => '支付成功'];
            case self::STATUS_CREATED:
                return ['success' => false, 'message' => '等待支付'];
            case self::STATUS_AMOUNT_MISMATCH:
                return ['success' => false, 'message' => '支付金额不匹配'];
            case self::STATUS_EXCEPTION:
                return ['success' => false, 'message' => '支付异常'];
            default:
                return ['success' => false, 'message' => '未知状态'];
        }
    }

    /**
     * 保存订单数据
     * @param string $order_id 订单ID
     * @param array $orderData 订单数据
     * @return bool 保存是否成功
     */
    private static function saveData($order_id, $orderData) {
        $orderFile = self::getOrderFilePath($order_id);
        try {
            file_put_contents($orderFile, json_encode($orderData, JSON_PRETTY_PRINT));
            return true;
        } catch (Exception $e) {
            Logger::log('错误', "保存订单数据失败: {$order_id}", [
                'error' => $e->getMessage(),
                'data' => $orderData
            ]);
            return false;
        }
    }

    private static function getOrderFilePath($order_id) {
        return self::$orderDir . $order_id . '.json';
    }

    private static function ensureOrderDirectory() {
        if (!is_dir(self::$orderDir)) {
            mkdir(self::$orderDir, 0777, true);
        }
    }
}

// 初始化订单文件系统
OrderFile::init();
