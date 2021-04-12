<?php
/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 配置
 *
 */

namespace app\api\controller\v1_0_0;

use think\Validate,
    app\api\model\v1_0_0\Conf as ConfModel,
    app\api\controller\ApiCommon;

class Conf extends ApiCommon
{
    /**
     * User: this
     * Date: 2021/4/9
     * Time: 08:45
     * 获取配置
     */
    public function getConf($post)
    {
        //实例化模型
        $model = new ConfModel();

        //调用model
        $result = $model->getConf();

        //返回数据
        jsonCrypt(200, $result, $post['app_crypt']['app_crypt']);
    }
}