<?php
class HttpRequestUtil {
    /**
     * 发送支付请求
     * @param string $url 请求地址
     * @param array $params 请求参数
     * @return array [
     *    'success' => bool,   // 请求是否成功
     *    'code' => int,      // 返回状态码(1为成功)
     *    'msg' => string,    // 返回信息
     *    'trade_no' => string,  // 支付订单号
     *    'payurl' => string,    // 支付跳转URL(可选)
     *    'qrcode' => string,    // 二维码链接(可选)
     *    'urlscheme' => string, // 小程序跳转URL(可选)
     *    'error' => string      // 错误信息(如果有)
     * ]
     */
    public static function sendPayRequest($url, $params) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if($error) {
                return [
                    'error' => $error,
                    'response' => null
                ];
            }
            
            return [
                'error' => null,
                'response' => $response,
                'http_code' => $httpCode
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'response' => null
            ];
        }
    }
}
