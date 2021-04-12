<?php
/**
 *  Crpyt.php
 *  文件描述
 *  Created on 2020/4/16 9:04
 *  Created by This
 */

namespace app\api\controller;

use think\Validate,
    think\Controller,
    think\Db,
    think\facade\Config,
    elliot\Crypt as CrptyMethod,
    elliot\LngLat;

class Crypt extends Controller
{

    public function dataCrypt(CrptyMethod $crypt)
    {
        $data = [
            'version' => 'v1.0.0',
            'api_url' => 'search_addr/searchPointAddr',
            'token' => '379c707f7f2a53db6b889df3f752c188',
            'user_lat' => '36.656932',
            'user_lng' => '117.120311',
            'user_addr_name' => '测试地址',
            'search_rang' => '3000',
            'mode' => 'transit',
            'list' => [
                [
                    'search_lng' => '117.109489',
                    'search_lat' => '36.706937',
                    'addr_name' => '淮海·东城御景北区-南门'
                ]
            ]
        ];


//        $data = input('post.');
//        if(!$data) {
//            jsonCrypt(100);
//        }

//        $data['image_data'] = $this->base64EncodeImage("./uploads/1.jpg");
        $a = $crypt->encrypt128(json_encode($data, JSON_UNESCAPED_UNICODE), '4b3cd743d800d46c');
        echo '<pre>';
        var_dump($a);exit;
    }

    public function a()
    {
        $str = 'NDQ0MzMyMzEzOTQ2MTM1MiQOh30iptdwfT7TYOF0b2p3t9Nll9zFY8g4nnp0/R2ZDIKBbpfSfqdoYXXZbi6x936m8v6avCA/Tp+45ZXMf2FEF6A9VxMiDFkDT2tMBZufyJRmP6is6WDMmR1uBlOmjg==';

        $str = base64_decode($str);
        echo '<pre>';
        var_dump($str);
    }
}