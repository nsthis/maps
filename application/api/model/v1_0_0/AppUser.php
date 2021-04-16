<?php

/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 用户相关
 */

namespace app\api\model\v1_0_0;

use think\Model,
    think\Db,
    think\cache\driver\Redis;

class AppUser extends Model
{
    /**
     * User: this
     * Date: 2021/3/11
     * Time: 14:42
     * 登陆
     */
    public function xcxLogin($post)
    {
        $time = date('Y-m-d H:i:s', time());

        //开启事务
        Db::startTrans();
        try {

            //检测当前open_id 是否已注册
            $user_info = Db::name('app_user')
                ->field('id, nick_name, avatr')
                ->where([
                    ['open_id', '=', $post['post_data']['open_id']]
                ])
                ->find();

            //若没有注册则新增
            if(!$user_info) {
                //组合数据
                $save_data['open_id'] = $post['post_data']['open_id'];
                $save_data['mobile'] = !empty($post['post_data']['mobile']) ? $post['post_data']['mobile'] : NULL;
                $save_data['nick_name'] = !empty($post['post_data']['nick_name']) ? $post['post_data']['nick_name'] : NULL;
                $save_data['avatr'] = !empty($post['post_data']['avatr']) ? $post['post_data']['avatr'] : NULL;
                $save_data['avatr'] = !empty($post['post_data']['unionid']) ? $post['post_data']['unionid'] : NULL;
                $save_data['app_crypt_id'] = $post['app_crypt']['app_crypt_id'];
                $save_data['created_at'] = $time;

                //数据存储
                $save_result = Db::name('app_user')->insert($save_data);
                if(($save_result === false) || ($save_result == 0)) {
                    throw new \Exception(103);
                }

                $user_info['id'] = Db::name('app_user')->getLastInsID();
                $user_info['nick_name'] = $save_data['nick_name'];
                $user_info['avatr'] = $save_data['avatr'];
            }

            //获取config配置
            $config = Config();

            //连接redis
            $redis = new Redis($config['redis']['user_login']);

//            //获取当前用户所有未作废token
//            $user_token = Db::name('login_log')
//                ->field('id, token')
//                ->where([
//                    ['app_user_id', '=', $user_info['id']],
//                    ['is_delete', '=', 1]
//                ])
//                ->select();
//            if($user_token) {
//                //redis删除
//                foreach ($user_token as $key => $value) {
//                    $redis->rm($value['token']);
//                }
//
//                $token_id = array_column($user_token, 'id');
//
//                //token失效
//                $token_result = Db::name('login_log')
//                    ->where([
//                        ['id', 'in', $token_id]
//                    ])
//                    ->update([
//                        'is_delete' => 2
//                    ]);
//            }

            //获取token
            $token = md5(md5($time . mt_rand(00000, 99999) . $user_info['id']));

            //组合token数据
            $token_save_data['app_user_id'] = $user_info['id'];
            $token_save_data['lng'] = !empty($post['post_data']['lng']) ? $post['post_data']['lng'] : NULL;
            $token_save_data['lat'] = !empty($post['post_data']['lat']) ? $post['post_data']['lat'] : NULL;
            $token_save_data['token'] = $token;
            $token_save_data['created_at'] = $time;

            //token数据存储
            $token_save_result = Db::name('login_log')->insert($token_save_data);
            if(($token_save_result === false) || ($token_save_result == 0)) {
                throw new \Exception(400);
            }

            $redis_data['app_user_id'] = $user_info['id'];
            $redis->set($token, $redis_data);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            jsonCrypt($e->getMessage());
        }

        $result['token'] = $token;
        $result['avatr'] = $user_info['avatr'];
        $result['nick_name'] = $user_info['nick_name'];

        return $result;
    }
}