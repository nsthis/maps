<?php
/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 配置
 *
 */


namespace app\api\model\v1_0_0;

use think\Config;
use think\Db,
    think\Model,
    think\cache\driver\Redis;

class Conf extends Model
{
    /**
     * User: this
     * Date: 2021/4/9
     * Time: 08:47
     * 获取配置
     */
    public function getConf()
    {
        $result = [];
        $query = Db::name('conf')
                ->field('key, value')
                ->select();
        if($query) {
            foreach ($query as $key => $value) {
                $result[$value['key']] = $value['value'];
            }
        }

        return $result;
    }
}