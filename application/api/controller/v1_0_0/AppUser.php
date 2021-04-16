<?php

/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 用户相关
 *
 */

namespace app\api\controller\v1_0_0;

use think\Validate,
    app\api\model\v1_0_0\AppUser as AppUsertModel,
    app\api\controller\ApiCommon;

class AppUser extends ApiCommon
{
    /**
     * User: this
     * Date: 2021/3/11
     * Time: 14:38
     * 小程序登陆
     */
    public function xcxLogin($post)
    {
        //基础验证
        $validate = new Validate([
            'open_id' => 'require',
            'unionid' => 'require',
//            'nick_name' => 'require',
//            'avatr' => 'require'
//            'lng' => 'require|float',
//            'lat' => 'require|float'
        ]);

        //错误信息
        $validate->message([
            'open_id.require' => 'open_id 不能为空',
            'unionid.require' => 'unionid 不能为空',
//            'nick_name.require' => '昵称 不能为空',
//            'avatr.require' => '头像 不能为空',
//            'lng.require' => '经度 不能为空',
//            'lng.float' => '经度 格式有误',
//            'lat.require' => '纬度 不能为空',
//            'lat.float' => '纬度 格式有误'
        ]);

        //校验
        if(!$validate->check($post['post_data'])) {
            jsonCrypt($validate->getError());
        }

        //实例化模型
        $model = new AppUsertModel();

        //调用model
        $result = $model->xcxLogin($post);

        //返回数据
        jsonCrypt(200, $result, $post['app_crypt']['app_crypt']);
    }
}