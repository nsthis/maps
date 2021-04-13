<?php

namespace tx;

use elliot\HttpRequest;

class Request
{
    private $url = 'https://apis.map.qq.com';
    private $key = 'YRVBZ-MUWLD-RZ44T-H7NBA-7NNHJ-S5F62';


    /**
     * 地理位置搜索
     */

    public function centerSearch($data)
    {
        return $this->request($data, '/ws/place/v1/search', 'EXVBZ-VO6K5-UULIZ-QEHW3-NYPA3-5HBDN');
    }

    /**
     * 距离计算
     */
    public function parameters($data)
    {
        return $this->request($data, '/ws/distance/v1/matrix', 'H7PBZ-IITCX-2HF46-TGM3B-OW2Z2-FPF2X');
    }
    /**
     * User: This
     * Date: 2020/4/27
     * Time: 15:05
     * 网络请求
     */
    function request($data, $url_path, $key)
    {

        //调用接口所需数据
        $push_data = $data;
//        $push_data['key'] = $this->key;
        $push_data['key'] = $key;

        $url = $this->url . $url_path;
        //识别请求地址为http还是https
        $protocol = stripos($url, 'https') === true ? 'http' : 'https';

        $http_request = new HttpRequest();

        $result = $http_request->httpRequest($url, $push_data, '', 'get', $protocol);

        //数据打印日志
        writeLogs('tx_request', '接收数据', $push_data);

        //传输日志存储
        writeLogs('tx_request', '接收数据', $result);

        //识别接口是否调用成功
        if (!$result['result']) {
            return false;
        }
        //解json
        $result['msg'] = json_decode($result['msg'], true);

        if (!$result['msg']) {
            return false;
        }

        if ($result['msg']['status'] != 0) {
            return false;
        }

        //返回结果
        return $result['msg'];

    }
}