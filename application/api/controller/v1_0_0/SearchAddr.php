<?php
/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 查询位置
 *
 */

namespace app\api\controller\v1_0_0;

use think\Validate,
    app\api\model\v1_0_0\SearchAddr as SearchAddrModel,
    app\api\controller\ApiCommon;

class SearchAddr extends ApiCommon
{
    /**
     * User: this
     * Date: 2021/3/11
     * Time: 15:46
     * 获取中心点位置
     */
    public function searchPointAddr($post)
    {
        //基础验证
        $validate = new Validate([
            'token' => 'require|length:32',
            'list' => 'require|array|length:1,4',
            'user_lng' => 'require|float',
            'user_lat' => 'require|float',
            'user_addr_name' => 'require'
        ]);

        //错误信息
        $validate->message([
            'token.require' => 'token 不能为空',
            'token.length' => 'token 格式有误',
            'list.require' => '列表 不能为空',
            'list.array' => '列表 格式有误',
            'list.length' => '列表 长度有误',
            'user_lng.require' => '当前用户经度 不能为空',
            'user_lng.float' => '当前用户经度 格式有误',
            'user_lat.require' => '当前用户纬度 不能为空',
            'user_lat.float' => '当前用户纬度 格式有误',
            'user_addr_name.require' => '用户当前地址名称 不能为空',
        ]);

        //校验
        if(!$validate->check($post['post_data'])) {
            jsonCrypt($validate->getError());
        }

        //轮询校验
        foreach ($post['post_data']['list'] as $key => $value) {
            $validate = new Validate([
                'search_lng' => 'require|float',
                'search_lat' => 'require|float',
                'addr_name' => 'require'
            ]);
            $validate->message([
                'search_lng.require' => '查询经度 不能为空',
                'search_lng.float' => '查询经度 格式有误',
                'search_lat.require' => '查询经度 不能为空',
                'search_lat.float' => '查询经度 格式有误',
                'addr_name.require' => '地址名称 不能为空',
            ]);
            if(!$validate->check($value)) {
                jsonCrypt("列表: $value" . $validate->getError());
            }
        }

        //实例化模型
        $model = new SearchAddrModel();

        //调用model
        $result = $model->searchPointAddr($post);

        //返回数据
        jsonCrypt(200, $result, $post['app_crypt']['app_crypt']);
    }
}