<?php

namespace app\common\service;

use think\Exception;

class NetworkService {
    public static function curlGet($url, $headers=[]) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if($headers){
            if(isset($headers['user_agent'])){
                curl_setopt($ch, CURLOPT_USERAGENT, $headers['user_agent']);
            }
            if(isset($headers['refer'])){
                curl_setopt($ch, CURLOPT_REFERER, $headers['refer']);
            }
            curl_setopt($ch, CURLOPT_HEADER, $headers); //设置header
        }
        // 返回最后的Location
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorMessage = serialize(curl_error($ch));
            \think\facade\Log::error("访问网络[{$url}]出错(GET)：【{$errorMessage}】");
            throw new Exception("访问网络[{$url}]出错(GET)" . $errorMessage);
        }
        $http_code  = curl_getinfo($ch,CURLINFO_HTTP_CODE );
        curl_close($ch);
        if($http_code!=200){
            \think\facade\Log::error("访问网络[{$url}]出错(GET)：【{$result}】");
            throw new Exception("访问网络[{$url}]出错(GET)" . $result);
        }
        return $result;
    }


    /**
     * @param $url
     * @param string $dataStr 提交
     * @param array $headers 头部信息
     * @param string $cert 证书
     * @param string $certKey 证书秘钥
     * @return bool|mixed
     */
    public static function curlPost($url, $dataStr, $headers = [], $cert = '', $certKey = '') {
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL, $url);  //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, $headers); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //证书默认格式为PEM
        if ($cert && $certKey) {
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $cert);

            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $certKey);
        }

        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式

        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorMessage = serialize(curl_error($ch));
            \think\facade\Log::error("访问网络[{$url}]出错(POST)：【{$errorMessage}】" . var_export($dataStr, 1));
            return false;
        }
        curl_close($ch);
        return $result;
    }
}