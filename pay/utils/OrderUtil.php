<?php
class OrderUtil {
    /**
     * 生成订单号
     */
    public static function generateOrderId($ip) {
        list($msec, $sec) = explode(" ", microtime());
        $msecTime = substr($msec, 2, 3);
        $timeStr = date("YmdHis", $sec);
        $ipParts = explode('.', $ip);
        $ipCode = str_pad($ipParts[3], 3, '0', STR_PAD_LEFT);
        $random = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
        return $timeStr . $msecTime . $ipCode . $random;
    }
}
