<?php
/**
 * Created by PhpStorm.
 * User: this
 * Date: 2019-07-23
 * Time: 15:50
 */

namespace wechat;

use elliot\HttpRequest,
    think\Db,
    think\cache\driver\Redis;

class WxSchedulePush
{
    /**
     * User: This
     * Date: 2020/5/11
     * Time: 15:52
     * Title: 微信模板消息推送(已废弃)
     */
    static public function wxSchedulePush($open_id, $form_id, $template_id, $data_arr, $page = null)
    {
        $wechat = config('wechat');
        $appid = $wechat['appid'];
        $app_secret = $wechat['app_secret'];
        $post_data = [
            "touser"           => $open_id,
            //用户的 openID，可用过 wx.getUserInfo 获取
            "template_id"      => $template_id,
            //小程序后台申请到的模板编号
            "page"             => $page,
            //点击模板消息后跳转到的页面，可以传递参数
            "form_id"          => $form_id,
            //第一步里获取到的 formID
            "data"             => $data_arr,
//            "emphasis_keyword" => "keyword2.DATA"
            //需要强调的关键字，会加大居中显示
        ];


        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".self::getAccessToken($appid, $app_secret);
        //这里替换为你的 appID 和 appSecret
        $data = json_encode($post_data, true);
        //将数组编码为 JSON
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type:application/json',
                //header 需要设置为 JSON
                'content' => $data,
                'timeout' => 60
                //超时时间
            )
        );
        $context = stream_context_create( $options );
        $result = file_get_contents( $url, false, $context );
        $result = json_decode($result, true);
        if($result['errcode'] == '0') {

            return true;
        }
        return false;
    }

    // getAccessToken.php
    static private function getAccessToken ($appid, $app_secret)
    {


        $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$app_secret;
        $html = file_get_contents($url);
        $output = json_decode($html, true);
        $access_token = $output['access_token'];
        return $access_token;
    }

    /**
     * 2  * Created by PhpStorm.
     * 3  * User: 86156
     * 4  * Date: 2020/1/3
     * 5  * Time: 10:23
     * 6  微信统一消息推送
     * open_id： 用户open_id
     * template_id: 模板id
     * data_arr： 模板内容
     * return: true/false
     */
    static public function wxSchedulePushTwo($open_id, $template_id, $data_arr, $page = null)
    {
        $access_token = getAccessToken();
        if(!$access_token) {
            return false;
        }
        $post_data = [
            //用户的 openID，可用过 wx.getUserInfo 获取
            "touser"           => $open_id,
            //小程序后台申请到的模板编号
            "template_id"      => $template_id,
            //点击模板消息后跳转到的页面，可以传递参数
            "page"             => $page,
            //第一步里获取到的 formID
            //"form_id"          => $form_id,
            //"emphasis_keyword" => "keyword2.DATA"
            "data"             => $data_arr,
            //需要强调的关键字，会加大居中显示
        ];
        $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=".$access_token['access_token'];
        //这里替换为你的 appID 和 appSecret
        $data = json_encode($post_data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        //将数组编码为 JSON
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type:application/json',
                //header 需要设置为 JSON
                'content' => $data,
                'timeout' => 60
                //超时时间
            )
        );
        $context = stream_context_create( $options );
        $result = file_get_contents( $url, false, $context );
        $result = json_decode($result, true);
        writeLogs('wechat_msg', '小程序消息推送数据:', $result);
        writeLogs('wechat_msg', '小程序消息推送结果:', $result);
        if($result['errcode'] == '0') {
            return true;
        }
        return false;
    }

    /**
     * User: this
     * Date: 2020/8/7
     * Time: 16:49
     * 微信公众号推送
     */
    static public function gzhMsgPush($community_id, $msg_data)
    {
        //获取access_token
        $config = Config();
        $redis = new Redis($config['redis']['common_data']);
        $access_token = $redis->get($community_id . '_access_token');
        //检测access_token是是否存在或者过期
        if(!$access_token) {
            //获取公众号app_id、app_secret
            $wechat_config = Db::name('wechat_config')
                ->field('gzh_app_id, gzh_app_secret')
                ->where([
                    ['community_id', '=', $community_id]
                ])
                ->find();
            $data['grant_type'] = 'client_credential';
            $data['appid'] = $wechat_config['gzh_app_id'];
            $data['secret'] = $wechat_config['gzh_app_secret'];
            //获取access_token
            $url = 'https://api.weixin.qq.com/cgi-bin/token';
            $http_request = HttpRequest::httpRequest($url, $data, '', 'get', 'https');
            if($http_request['result']) {
                $http_request['msg'] = json_decode($http_request['msg'], true);
                if($http_request['msg'] && isset($http_request['msg']['access_token'])) {
                    $access_token = $http_request['msg']['access_token'];
                    //存入redis
                    $redis->set($community_id . '_access_token', $access_token, 7200);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        //请求地址
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
        //将数组编码为 JSON
        $message = json_encode($msg_data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $http_request = HttpRequest::httpRequest($url, $message, '', 'post', 'https');
        //检查接口是否调通
        if(!$http_request['result']) {
            return false;
        }

        $http_request['msg'] = json_decode($http_request['msg'], true);
        writeLogs('wechat_msg', '公众号消息推送结果:', $http_request['msg']);
        //检测是否为json字符串
        if(!$http_request['msg']) {
            return false;
        }
        //检测是否发送成功
        if($http_request['msg']['errcode'] != '0') {
            return false;
        }
        return true;
    }

    /**
     * User: this
     * Date: 2020/12/9
     * Time: 14:05
     * 获取access_token
     */
    public function accessToken($community_id)
    {
        //获取access_token
        $config = Config();
        $redis = new Redis($config['redis']['common_data']);
        $access_token = $redis->get($community_id . '_access_token');
        //检测access_token是是否存在或者过期
        if(!$access_token) {
            //获取公众号app_id、app_secret
            $wechat_config = Db::name('wechat_config')
                ->field('gzh_app_id, gzh_app_secret')
                ->where([
                    ['community_id', '=', $community_id]
                ])
                ->find();
            $data['grant_type'] = 'client_credential';
            $data['appid'] = $wechat_config ? $wechat_config['gzh_app_id'] : $config['wechat']['gzh']['app_id'];
            $data['secret'] = $wechat_config ? $wechat_config['gzh_app_secret'] : $config['wechat']['gzh']['app_secret'];
            
            //获取access_token
            $url = 'https://api.weixin.qq.com/cgi-bin/token';
            $http_request = HttpRequest::httpRequest($url, $data, '', 'get', 'https');

            if($http_request['result']) {
                $http_request['msg'] = json_decode($http_request['msg'], true);
                if($http_request['msg'] && isset($http_request['msg']['access_token'])) {
                    $access_token = $http_request['msg']['access_token'];
                    //存入redis
                    $redis->set($community_id . '_access_token', $access_token, 7200);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return $access_token;
    }

    /**
     * User: this
     * Date: 2020/12/9
     * Time: 14:10
     * 获取跳转小程序所需api_ticket
     */
    public function getApiTicket($community_id)
    {

        $config = Config();
        $redis = new Redis($config['redis']['common_data']);

        //从redis中获取api_ticket
        $api_ticket = $redis->get($community_id . '_api_ticket');
        //检测access_token是是否存在或者过期
        if(!$api_ticket) {
            //获取access_token
            $access_token = $this->accessToken($community_id);
            if(!$access_token) {
                jsonCrypt('access_token 获取失败!');
            }
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket";
            $data['access_token'] = $access_token;
//            $data['type'] = 'wx_card';
            $data['type'] = 'jsapi';
//            $data['offset_type'] = 1;

            $http_request = HttpRequest::httpRequest($url, $data, '', 'get', 'https');
            if($http_request['result']) {
                $http_request['msg'] = json_decode($http_request['msg'], true);
                if($http_request['msg'] && isset($http_request['msg']['ticket'])) {
                    //存入redis
                    $redis->set($community_id . '_api_ticket', $http_request['msg']['ticket'], 7200);
                    $api_ticket = $http_request['msg']['ticket'];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return $api_ticket;
    }
}