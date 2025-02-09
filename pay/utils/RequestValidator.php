<?php
class RequestValidator {
    // 创建订单参数验证
    public static function validateCreateOrder($money, $type, $device) {
        if (empty($money) || !is_numeric($money) || $money <= 0) {
            return ["valid" => false, "msg" => "金额参数错误"];
        }
        
        if (empty($type) || !in_array($type, ['alipay', 'wxpay'])) {
            return ["valid" => false, "msg" => "支付方式错误"];
        }
        
        if (!in_array($device, ['pc', 'mobile', 'qq', 'wechat', 'alipay'])) {
            $device = 'pc';
        }
        
        return ["valid" => true, "device" => $device];
    }
    
    // 回调通知参数验证
    public static function validateNotify($params, $merchant_id) {
        $required_params = ['pid', 'trade_no', 'out_trade_no', 'type', 'name', 'money', 'trade_status', 'sign', 'sign_type'];
        
        foreach ($required_params as $param) {
            if (!isset($params[$param]) || $params[$param] === '') {
                return ["valid" => false, "msg" => "缺少必要参数:{$param}"];
            }
        }
        
        if ($params['pid'] != $merchant_id) {
            return ["valid" => false, "msg" => "商户ID不匹配"];
        }
        

        if (!preg_match('/^\d{1,4}(\.\d{1,2})?$/', $params['money'])) {
            return ["valid" => false, "msg" => "金额格式错误"];
        }
        
        return ["valid" => true];
    }
    
    // 查询订单参数验证
    public static function validateQuery($order_id) {
        if (empty($order_id)) {
            return ["valid" => false, "msg" => "订单号不能为空"];
        }
        return ["valid" => true];
    }
}
