<?php

/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 用户分享
 *
 */

namespace app\api\model\v1_0_0;

use think\Model,
    think\Db;

class UserShare extends Model
{
    /**
     * User: this
     * Date: 2021/3/11
     * Time: 14:38
     * 用户分享
     */
    public function userShare($post)
    {
        $time = date('Y-m-d H:i:s', time());

        //组合存储数组
        $save_data['app_user_id'] = $post['user_info']['app_user_id'];
        $save_data['place_id'] = $post['post_data']['place_id'];
        $save_data['created_at'] = $time;

        $save_result = Db::name('user_share')->insert($save_data);
        if(($save_result === false) || ($save_result == 0)) {
            jsonCrypt(103);
        }
        return [];
    }
}