<?php

/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 查询位置
 *
 */

namespace app\api\model\v1_0_0;

use think\Model,
    think\Db,
    elliot\LngLat,
    elliot\Sort,
    tx\Request,
    app\api\model\v1_0_0\Place;

class SearchAddr extends Model
{
    /**
     * User: this
     * Date: 2021/3/11
     * Time: 15:46
     * 获取中心点位置
     */
    public function searchPointAddr($post)
    {

        $time = date('Y-m-d H:i:s', time());

        //组合获取中心点数组
        $lng_lat = [];

        //组合用户位置数据
        $user_sadr_data = [];

        //组合场所数组
        $place_save_data = [];

        //定义场所数组
        $place_info = [];

        //检测是否传入商圈交通工具
        if(empty($post['post_data']['mode'])) {
            $post['post_data']['mode'] = 'driving';
        }

        //定义返回数组
        $result = [];
        $result['uf_distance'] = 0;
        $result['list'] = [];

        $user_sadr_data[0]['app_user_id'] = $post['user_info']['app_user_id'];
        $user_sadr_data[0]['lng'] = $post['post_data']['user_lng'];
        $user_sadr_data[0]['lat'] = $post['post_data']['user_lat'];
        $user_sadr_data[0]['addr_name'] = $post['post_data']['user_addr_name'];
        $user_sadr_data[0]['type'] = 1;
        $user_sadr_data[0]['created_at'] = $time;

        foreach ($post['post_data']['list'] as $key => $value) {
            //组合获取中心点数据
            $lng_lat[$key][0] = $value['search_lat'];
            $lng_lat[$key][1] = $value['search_lng'];

            $user_sadr_data[$key +1]['app_user_id'] = $post['user_info']['app_user_id'];
            $user_sadr_data[$key +1]['lng'] = $value['addr_name'];
            $user_sadr_data[$key +1]['lat'] = $value['search_lat'];
            $user_sadr_data[$key +1]['addr_name'] = $value['addr_name'];
            $user_sadr_data[$key +1]['type'] = 2;
            $user_sadr_data[$key +1]['created_at'] = $time;

            //计算用户距离朋友的距离
            $result['uf_distance'] += LngLat::get_two_point_distance(
                $post['post_data']['user_lat'],
                $value['search_lat'],
                $post['post_data']['user_lng'],
                $value['search_lng']
                );
        };

        //将用户检索地址存储
//        Db::name('user_sadr')->insertAll($user_sadr_data);

        //组合获取中心点数据
        $lng_lat_count = count($lng_lat);
        $lng_lat[$lng_lat_count + 1][0] = $post['post_data']['user_lat'];
        $lng_lat[$lng_lat_count + 1][1] = $post['post_data']['user_lng'];

        //获取中心点坐标
        $lng_lat_result = LngLat::GetCenterFromDegrees($lng_lat);

        if(!$lng_lat_result) {
            jsonCrypt(500);
        }

        //检测是否传入商圈范围
        if(empty($post['post_data']['search_rang'])) {
            $post['post_data']['search_rang'] = 3000;
        }

        //获取中心点周边列表
        $center_data['keyword'] = urlencode('购物');
        $boundary = "{$lng_lat_result['lat']},{$lng_lat_result['lng']},{$post['post_data']['search_rang']}";
        $center_data['boundary'] = "nearby($boundary)";
        $center_data['filter'] = "category=" . urlencode('购物中心');
        $center_data['orderby'] = "_distance";
        $request = new Request();

        $center_result = $request->centerSearch($center_data);

        if(empty($center_result['data'])) {
            return $result;
        }

        $post['post_data']['driving_to'] = '';
        //获取矩阵对应to的经纬
        foreach ($center_result['data'] as $key => $value) {
            $post['post_data']['driving_to'] .= ';' . $value['location']['lat'] . ',' . $value['location']['lng'];
            $center_result['data'][$key]['count_distance'] = 0;
            $center_result['data'][$key]['count_duration'] = 0;
        }
        $post['post_data']['driving_to'] = trim($post['post_data']['driving_to'], ';');

        $data['mode'] = 'driving';
        $data['from'] = $post['post_data']['user_lat'] . ',' . $post['post_data']['user_lng'];
        $data['to'] = $post['post_data']['driving_to'];
        $pts_result = $request->parameters($data);
//sleep(2);
//        $pts_result = $this->getDriving($request, $post);
//        //识别出行方式
//        switch ($post['post_data']['mode'])
//        {
//            //驾车
//            case 'driving':
//                $pts_result = $this->getDriving($request, $post);
//                break;
//            //步行
//            case 'walking':
//                $pts_result = $this->getWalking($request, $post);
//                break;
//            //自行车
//            case 'bicycling':
//                $pts_result = $this->getBicycling($request, $post);
//                break;
//            //公交车
//            case 'transit':
//                $pts_result = $this->getTransit($request, $post);
//                break;
//            default:
//                jsonCrypt(501);
//                break;
//        }
        //组合商圈经纬
        $pts_data['to'] = '';

        if(empty($pts_result['result']['rows'])) {
            return $result;
        }

        $result['list'] = $center_result['data'];

        //实例化Place
        $place = new Place();

        //组合场所数组、当前查询数据总计距离和用时、分别距离及用时
        foreach ($result['list'] as $key => $value) {
            //检测当前地点是否已村子啊
            $place_info[$key] = $place->getPlaceInfo($value['id']);
            if(!$place_info[$key]) {
                //组合场所数据
                $place_save_data[$key]['id'] = $value['id'];
                $place_save_data[$key]['lng'] = $value['location']['lng'];
                $place_save_data[$key]['lat'] = $value['location']['lat'];
                $place_save_data[$key]['name'] = $value['title'];
                $place_save_data[$key]['address'] = $value['address'];
                $place_save_data[$key]['tel'] = $value['tel'];
                $place_save_data[$key]['type'] = $value['type'];
                $place_save_data[$key]['province'] = $value['ad_info']['province'];
                $place_save_data[$key]['city'] = $value['ad_info']['city'];
                $place_save_data[$key]['district'] = $value['ad_info']['district'];
                $place_save_data[$key]['adcode'] = $value['ad_info']['adcode'];
                $place_save_data[$key]['created_at'] = $time;
            }

            $result['list'][$key]['dd_info'] = '';

            foreach ($pts_result['result']['rows'] as $ke => $va) {
                if($ke == 0) {
                    $result['list'][$key]['dd_info'] = '距离你 ' . $this->checkKm($va['elements'][$key]['distance']) . ',约 ' . $this->checkTime($va['elements'][$key]['duration']);
                } else {
                    $result['list'][$key]['dd_info'] .= ';距离你朋友 ' . $this->checkKm($va['elements'][$key]['distance']) . ',约 ' . $this->checkTime($va['elements'][$key]['duration']);
                }
                $result['list'][$key]['count_distance'] += $va['elements'][$key]['distance'];
                $result['list'][$key]['count_duration'] += $va['elements'][$key]['duration'];

            }
        }
        if($place_save_data) {
            $place_save_data = array_values($place_save_data);
            //数据存储
            Db::name('place')->insertAll($place_save_data);
        }

        //实例化Sort
        $sort = new Sort();

        if(empty($post['post_data']['order_type'])) {
            $post['post_data']['order_type'] = 'count_duration';
        }

        //排序
        $result['list'] = $sort->arraySort($result['list'], $post['post_data']['order_type'], 'asc');

        return $result;

    }

    /**
     * User: this
     * Date: 2021/3/30
     * Time: 10:45
     * 计算公里/时间
     * value: 值
     * type: 1:公里 2:时间
     */
    private function checkKm($value)
    {

        $result = $value / 1000;
        $result = round($result, 2) . 'km';
        return $result;
    }

    /**
     * User: This
     * Date: 2020/4/20
     * Time: 14:45
     * stop_time  Unix时间戳
     * 计算停车时间
     * 返回
     */
    public function checkTime($value)
    {
        //现在时间减去入场时间(查看总计多少分钟)
        $num = ceil($value / 60);
        //如果小于60分钟
        if($num <= 60) {
            return $num . '分钟';
        } else {
            if((intval($num / 60)) <= 60) {
                return intval($num / 60) . '小时' . fmod(floatval($num),60) . '分钟';
            } else {
                $day = intval(($num / 60) / 24);
                $hours = intval((($num - ($day * 60 * 24)) / 60));
                return $day . '天' . $hours . '小时' . fmod(floatval($num),60) . '分钟';
            }
        }
    }

    /**
     * User: this
     * Date: 2021/4/8
     * Time: 16:39
     * 获取驾车耗时
     */
    private function getDriving($request, $post)
    {
        $request = new Request();
        $data['mode'] = 'driving';
        $data['from'] = $post['post_data']['user_lat'] . ',' . $post['post_data']['user_lng'];
        $data['to'] = $post['post_data']['driving_to'];
        //获取矩阵排列接口
        return $request->parameters($data);
    }
}