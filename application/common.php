<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


/**
 * User: this
 * Date: 2019-07-16
 * Time: 14:12
 * Api接口json数据返回(1.0)
 */
function jsonCrypt($code = 200, $data = [], $app_crypt = null, $return_type = 1){
    //检测code是否为整数
    $config = config();
    $config_code = $config['api_code'];
    if(!isset($config_code[$code]) || $code == 0) {
        $arr['code'] = 0;
        if($data) {
            $arr['msg'] = $data;
        } else {
            $arr['msg'] = $code;
        }
        $arr['data'] = [];
    } else {
        $arr['code'] = $code;
        $arr['msg'] = $config_code[$code];
        $arr['data'] = $data;
    }

    //如果加密盐是真的
    if($app_crypt) {
        //调用加密
        $crypt = new \elliot\Crypt();
        $arr['data'] = $crypt->encrypt128(json_encode($arr['data'],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), $app_crypt);
    }

    //返回数据存储日志
    writeLogs('api_post', '返回数据', $arr);

    exit(json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

}


/**
 * User: this
 * Date: 2020/6/11
 * Time: 10:47
 *获取 access_token
 */
function getAccessToken()
{
    //查看access_token文件是否存在

    $config = Config();
    //检测redis中是否存在access_token
    $redis = new think\cache\driver\Redis($config['redis']['common_data']);
    $result_msg = $redis->get('xcx_access_token');
    if(!$result_msg) {
        $http_request = new elliot\HttpRequest();
        $data['grant_type'] = 'client_credential';
        $data['appid'] = $config['wechat']['xcx']['app_id'];
        $data['secret'] = $config['wechat']['xcx']['app_secret'];
        $url = 'https://api.weixin.qq.com/cgi-bin/token';
        $result = $http_request::httpRequest($url, $data, '', 'get', 'https');

        if(!$result['result']) {

            return false;
        }
        $result_msg = json_decode($result['msg'], true);
        if(isset($result_msg['errcode'])) {

            return false;
        }
        $redis->set('xcx_access_token', $result_msg, 7200);
    }
    return $result_msg;
}

/**
 * Created by PhpStorm.
 * User: yangk
 * Date: 2020/8/25
 * Time: 9:10
 * 获取IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return string
 */
function get_client_ip($type = 0, $adv = false)
{
    $type = $type ? 1 : 0;
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 随机生成16位字符串
 * @return string 生成的字符串
 */
function get_random_str($length = 16) {

    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}


/**
 * Created by PhpStorm.
 * User: yangk
 * Date: 2020/8/25
 * Time: 8:55
 * 打印日志
 * @param array $data 数据
 * @param string $message 信息
 * @param string $filename 文件路径
 * @return bool
 */
function writeLogs($file_name, $message = '', $data = array())
{
    $time = date('Y-m-d H:i:s', time());
    $day = date('Ymd', time());
    $dir_name =  RUNTIME_PATH . "write_log/$day/";
    $file_name = "$dir_name/$file_name.log";

    //检测文件夹是否存在
    if(!file_exists($dir_name))
    {
        //创建文件夹
        mkdir($dir_name,0777,true);
    }
    $log_str = "[{$time}]" . PHP_EOL;
    if($message){
        $log_str .= "*** message *** : $message" . PHP_EOL;
    }
    if(gettype($data) == 'array')
    {
        $data = json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }
    $log_str .= $data . PHP_EOL;
    file_put_contents($file_name, $log_str,FILE_APPEND);
    return true;
}