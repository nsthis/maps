<?php

/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 小程序相关接口
 *
 */

namespace app\api\model\v1_0_0;

use wechat\WxBizDataCrypt;

class Wechat
{
    /**
     * User: this
     * Date: 2020/6/15
     * Time: 14:28
     * 获取微信小程序open_id
     */
    public function getXcxOpenId($post)
    {
        //获取小程序配置
        $config = config();

        //定义返回值
        $result = [];

        $session_url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $config['wechat']['xcx']['app_id'] . '&secret=' . $config['wechat']['xcx']['app_secret'] . '&js_code=' . $post['post_data']['code'] . '&grant_type=authorization_code';

        $content = file_get_contents($session_url);

        $content = json_decode($content, true);

        writeLogs('wechat', 'open_id获取内容: ', $content);

        if (!$content) {
            jsonCrypt(102);
        }

        //检测是否存在错误信息
        if (isset($content['errorcode']) && $content['errorcode']) {
            jsonCrypt($content['errmsg']);
        }

        //检测是否获取到open_id
        if(!isset($content['openid']) || !$content['openid']) {
            jsonCrypt(300);
        }

//        //获取用户昵称、头像
//        $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$content['session_key']}&openid={$content['openid']}&lang=zh_CN";
//
//        $info_result = file_get_contents($info_url);
//
//        $info_result = json_decode($info_result, true);
//
//        writeLogs('wechat', '昵称、头像获取内容: ', $info_result);

        $result['openid'] = $content['openid'];
        $result['session_key'] = $content['session_key'];


        return $result;
    }

    /**
     * User: this
     * Date: 2019-07-16
     * Time: 14:12
     * 微信数据解析
     */
    public function wechatCodes($post)
    {
        //获取Config配置
        $config = Config();

        $pc = new WxBizDataCrypt($config['wechat']['xcx']['app_id'], $post['post_data']['session_key']);

        $errCode = $pc->decryptData($post['post_data']['encrypted_data'], $post['iv'], $data);
        if ($errCode != 0) {
            jsonCrypt(102);
        }

        if(!isset($data['phoneNumber']) || !$data['phoneNumber']) {
            jsonCrypt(301);
        }


        $result['mobile'] = $data['phoneNumber'];

//        return ($data . "\n");
        return $result;
    }
}