<?php
/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 场所
 *
 */

namespace app\api\model\v1_0_0;

use think\Config;
use think\Db,
    think\Model,
    think\cache\driver\Redis;

class Place extends Model
{
    /**
     * User: this
     * Date: 2021/3/30
     * Time: 09:41
     * 获取场所列表
     */
    public function getPlaceInfo($place_id)
    {
        //获取Config配置
        $config = Config();

        //实例化redis
        $redis = new Redis($config['redis']['place']);

        $result = $redis->get($place_id);

        if(!$result) {
            $result = Db::name('place')
                    ->field('*')
                    ->where([
                        ['id', '=', $place_id]
                    ])
                    ->find();
            if(!$result) {
                return false;
            }
            $result = $redis->set($place_id, $result);
        }
        return $result;
    }
}