<?php

/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 用户分享
 *
 */

namespace app\api\controller\v1_0_0;

use think\Validate,
    app\api\model\v1_0_0\UserShare as UserShareModel,
    app\api\controller\ApiCommon;

class UserShare extends ApiCommon
{
    /**
     * User: this
     * Date: 2021/3/11
     * Time: 14:38
     * 用户分享
     */
    public function userShare($post)
    {
        //基础验证
        $validate = new Validate([
            'token' => 'require|length:32',
            'place_id' => 'require',
        ]);

        //错误信息
        $validate->message([
            'token.require' => '用户标识 不能为空',
            'token.length' => '用户标识 不能为空',
            'place_id.require' => '场所标识 不能为空',
        ]);

        //校验
        if(!$validate->check($post['post_data'])) {
            jsonCrypt($validate->getError());
        }

        //实例化模型
        $model = new UserShareModel();

        //调用model
        $result = $model->userShare($post);

        //返回数据
        jsonCrypt(200, $result, $post['app_crypt']['app_crypt']);
    }
}