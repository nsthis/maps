<?php
namespace gd;

use elliot\HttpRequest;

class Request
{
    private $url = 'https://restapi.amap.com';

    private $key = '42a0934da32bc9482d9b50cc0c39a034';

    /**
     * User: this
     * Date: 2021/4/10
     * Time: 00:11
     * 经纬度转换
     */
    public function convert($data)
    {
        return $this->request($data, '/v3/assistant/coordinate/convert');
    }
    /**
     * User: this
     * Date: 2021/4/9
     * Time: 23:40
     * 周边搜索
     */
    public function centerSearch($data)
    {
        return $this->request($data, '/v3/place/around');
    }

    //矩阵测距（驾车）
    public function distance($data)
    {
        return $this->request($data, '/v3/distance');
    }

    //步行测距
    public function walking($data)
    {
        return $this->request($data, '/v3/direction/walking');
    }

    //骑行测距
    public function bicycling($data)
    {
        return $this->request($data, '/v4/direction/bicycling', 2);
    }

    //公交耗时
    public function integrated($data)
    {
        return $this->request($data, '/v3/direction/transit/integrated');
    }

    //逆地理编码
    public function regeo($data)
    {
        return $this->request($data, '/v3/geocode/regeo');
    }
    /**
     * User: This
     * Date: 2020/4/27
     * Time: 15:05
     * 网络请求
     */
    function request($data, $url_path, $request_type = 1)
    {

        //调用接口所需数据
        $push_data = $data;
        $push_data['key'] = $this->key;

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

        //识别请求类型
        switch ($request_type)
        {
            case 2:
                if (empty($result['msg']['data'])) {
                    return false;
                }
                break;
            default:
                if (empty($result['msg']['infocode']) || ($result['msg']['infocode'] != 10000)) {
                    return false;
                }
                break;
        }

        //返回结果
        return $result['msg'];

    }
}