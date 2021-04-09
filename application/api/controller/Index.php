<?php

/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 统一入口
 *
 */

namespace app\api\controller;

use think\Request,
    think\Validate,
    think\Log,
    think\Db,
    think\cache\driver\Redis,
    elliot\Crypt,
    elliot\DateHelper;

class Index
{

    /**
     * Index constructor.
     * @param Request|null $request
     */
    public function __construct(Request $request = null)
    {

        $post = input('post.');

        writeLogs('api_post', '接收数据:', $post);

        $this->cmd($post);
        parent::__construct($request);
    }

    /**
     * User: this
     * Date: 2020/6/12
     * Time: 16:26
     * 防克隆
     */
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function cmd($data)
    {

        //规则验证
        $validate = new Validate([
            'data'              => 'require',
            'sign'              => 'require',
            'app_id'            => 'require'
        ]);

        $validate->message([
            'data.require'      => '数据 不能为空',
            'hash.require'      => '签名 不能为空',
            'app_id.require'    => '应用标识 不能为空'
        ]);
        if(!$validate->check($data)) {
            jsonCrypt( $validate->getError());
        }
        //获取app的加密盐
        //获取redis的config配置
        $config = Config();
        $redis = new Redis($config['redis']['common_data']);

        //检测请求ip是否被禁止
        $ip = get_client_ip();
        $redis_ip = $redis->get($ip);
        if($redis_ip && $redis_ip >= 10) {
            jsonCrypt(113);
        }

        $post_data['app_crypt'] = $redis->get($data['app_id']);

        //如果redis不存在
        if(!$post_data['app_crypt']) {
            $post_data['app_crypt'] = Db::name('app_crypt')
                ->field('id as app_crypt_id, app_id, app_crypt, is_lock as app_crypt_lock, type as app_crypt_type')
                ->where([
                    ['app_id', '=', $data['app_id']]
                ])
                ->find();
            if(!$post_data['app_crypt']) {
                //记录ip及错误次数
                $redis_ip = $redis_ip ? $redis_ip + 1 : 1;
                $redis->set($ip, $redis_ip, 86400);
                jsonCrypt(105);
            }
            //将数据存到redis中
            $redis->set($data['app_id'], $post_data['app_crypt']);
        }

        //检测是否已被锁定
        if($post_data['app_crypt']['app_crypt_lock'] != 1) {
            jsonCrypt(0, '禁止对接');
        }

        //对数据解密
        $crypt = new Crypt();
        $decrypted = $crypt->decrypt128($data['data'], $post_data['app_crypt']['app_crypt'], $data['sign']);

        if(!$decrypted) jsonCrypt(106);

        $decrypted = json_decode($decrypted, true);

        //基础数据验证
        $validate_data = new Validate([
            'version'           => 'require',
            'api_url'           => 'require'
        ]);
        $validate_data->message([
            'version.require'   => '请输入版本号',
            'api_url.require'   => '请输入指令'
        ]);
        if(!$validate_data->check($decrypted)) {
            jsonCrypt( $validate_data->getError());
        }

        //检测版本是否存在
        $version = str_replace('.', '_', $decrypted['version']);
        if(!is_dir(__DIR__ . '/' . $version)) jsonCrypt(0, 'Version Not Found');

        //处理指令
        $url = explode('/', $decrypted['api_url']);
        $url_count = count($url);
        if($url_count != 2) jsonCrypt(0, 'api_url 格式不正确');

        //检测类中是否存在下划线
        $url[0] = explode('_', $url[0]);
        foreach ($url[0] as $key => $value) {
            $url[0][$key] = strtolower($value);
            $url[0][$key] = ucfirst($url[0][$key]);
        }
        $url[0] = implode('', $url[0]);

        //检测文件是否存在
        $controller_file =  __DIR__ . '/' . $version . '/' . $url[0] . '.php';
        if(!file_exists($controller_file)) jsonCrypt(0, 'Controller File Not Found');

        //检验类是否存在
        $controller_name = 'app\\api\\controller\\' . $version . '\\'. $url[0];
        if(!class_exists($controller_name)) jsonCrypt(0, 'Controller Not Found');

        //检测方法是否存在
        $controller = new $controller_name;
        if(!method_exists($controller, $url[1])) jsonCrypt(0, 'Method Not Found');

        //检测是否存在token
        if(isset($decrypted['token']) && $decrypted['token'])
        {
            $post_data['user_info'] = $this->getTokenInfo($decrypted['token']);
        }

        //数据拼接
        $post_data['post_data'] = $decrypted;

        //方法调用
        $controller->{$url[1]}($post_data);
    }

    /**
     * User: this
     * Date: 2020/6/26
     * Time: 14:49
     * 获取token信息
     */
    private function getTokenInfo($token)
    {
        //检测token是否有效
        $config = Config();
        $redis = new Redis($config['redis']['user_login']);
        $app_user_id = $redis->get($token);

        if(!$app_user_id) {
            jsonCrypt(201);
        }
        return $app_user_id;
    }

}