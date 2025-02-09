<?php
class IpUtil {
    public static function getClientIp() {
        // 检查是否有 CDN 转发的真实用户 IP
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // 一般代理
            'HTTP_X_REAL_IP',        // Nginx 代理
            'HTTP_CLIENT_IP',        // 一般代理
            'REMOTE_ADDR'            // 直连 IP
        ];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if ($key === 'HTTP_X_FORWARDED_FOR') {
                    // 取第一个 IP（真实客户端 IP）
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                // 验证 IP 格式
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
}
