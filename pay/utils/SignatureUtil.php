<?php
class SignatureUtil {
    /**
     * 计算签名 (MD5签名算法)
     * 1. 参数按ASCII码从小到大排序(a-z)
     * 2. sign、sign_type和空值不参与签名
     * 3. 将参数拼接成URL键值对格式(a=b&c=d)
     * 4. 拼接商户密钥KEY后进行MD5加密(32位小写)
     */
    public static function generateSign($params, $api_key) {
        // 按ASCII码排序
        ksort($params);
        reset($params);
        
        $sign_string = "";
        foreach ($params as $key => $value) {
            if ($key != "sign" && $key != "sign_type" && $value !== '') {
                $sign_string .= "$key=$value&";
            }
        }
        $sign_string = rtrim($sign_string, "&") . $api_key;
        return strtolower(md5($sign_string));
    }
}
