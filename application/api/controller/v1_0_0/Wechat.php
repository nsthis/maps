<?php

/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 小程序相关接口
 *
 */

namespace app\api\controller\v1_0_0;

use think\Validate,
    app\api\model\v1_0_0\Wechat as WechatModel,
    app\api\controller\ApiCommon;

class Wechat extends ApiCommon
{
    /**
     * User: this
     * Date: 2020/6/15
     * Time: 14:25
     * 获取微信小程序open_id
     */
    public function getXcxOpenId($post)
    {
        //基础验证
        $validate = new Validate([
            'code' => 'require'
        ]);

        //错误信息
        $validate->message([
            'code.require' => 'code不能为空'
        ]);

        //校验
        if(!$validate->check($post['post_data'])) {
            jsonCrypt($validate->getError());
        }

        //实例化模型
        $model = new WechatModel();

        //调用model
        $result = $model->getXcxOpenId($post);

        //返回信息
        jsonCrypt(200, $result, $post['app_crypt']['app_crypt']);

    }

    /**
     * User: this
     * Date: 2020/6/15
     * Time: 15:07
     * 小程序数据解析
     */
    public function xcxDataParsing($post)
    {
        //基础验证
        $validate = new Validate([
            'encrypted_data' => 'require',
            'iv' => 'require',
            'session_key' => 'require'
        ]);

        //错误信息
        $validate->message([
            'encrypted_data.require' => 'encrypted_data 不能为空',
            'iv.require' => 'iv 不能为空',
            'session_key.require' => 'session_key 不能为空'
        ]);

        //校验
        if(!$validate->check($post['post_data'])) {
            jsonCrypt($validate->getError());
        }

        //实例化模型
        $model = new WechatModel();

        //调用model
        $result = $model->wechatCodes($post);

        //返回信息
        jsonCrypt(200, $result, $post['app_crypt']['app_crypt']);
    }
}