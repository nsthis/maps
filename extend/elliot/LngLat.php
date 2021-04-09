<?php

/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Title: 经纬度
 * Create Date: 2021-01-09
 */

namespace elliot;

class LngLat
{
    /**
     * User: this
     * Date: 2021/1/9
     * Time: 10:36
     * 计算两个经纬度之间的距离
     * return km
     * lat1 纬度1
     * lat2 纬度2
     * lng1 经度1
     * lng2 经度2
     */
    public static function get_two_point_distance($lat1, $lat2, $lng1, $lng2, $km = true)
    {
        $radLat1 = deg2rad($lat1);//deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137;
        if(!$km) {
            $s = $s * 1000;
        }

        return round($s,2);//返回公里数
    }

    /**
     * 根据起点坐标和终点坐标测距离
     * @param  [array]   $from 	[起点坐标(经纬度),例如:array(118.012951,36.810024)]
     * @param  [array]   $to 	[终点坐标(经纬度)]
     * @param  [bool]    $km        是否以公里为单位 false:米 true:公里(千米)
     * @param  [int]     $decimal   精度 保留小数位数
     * @return [string]  距离数值
     */
    public static function get_distance($from,$to,$km=true,$decimal=2){
        sort($from);
        sort($to);
        $EARTH_RADIUS = 6370.996; // 地球半径系数

        $distance = $EARTH_RADIUS*2*asin(sqrt(pow(sin( ($from[0]*pi()/180-$to[0]*pi()/180)/2),2)+cos($from[0]*pi()/180)*cos($to[0]*pi()/180)* pow(sin( ($from[1]*pi()/180-$to[1]*pi()/180)/2),2)))*1000;

        if($km){
            $distance = $distance / 1000;
        }

        return round($distance, $decimal);
    }

    /**
     * User: this
     * Date: 2021/3/11
     * Time: 14:00
     * 根据经纬度获取中心点坐标
     * date[0][0] 纬度
     * data[0][1] 经度
     */
    public static function GetCenterFromDegrees($data)
    {

        if (!is_array($data)) return FALSE;

        $num_coords = count($data);

        $X = 0.0;
        $Y = 0.0;
        $Z = 0.0;

        foreach ($data as $coord){

            $lat = deg2rad((float)$coord[0]);
            $lon = deg2rad((float)$coord[1]);

            $a = cos($lat) * cos($lon);
            $b = cos($lat) * sin($lon);
            $c = sin($lat);

            $X += $a;
            $Y += $b;
            $Z += $c;
        }

        $X /= $num_coords;
        $Y /= $num_coords;
        $Z /= $num_coords;

        $lon = atan2($Y, $X);
        $hyp = sqrt($X * $X + $Y * $Y);
        $lat = atan2($Z, $hyp);

        $result['lng'] = rad2deg($lon);
        $result['lat'] = rad2deg($lat);

        return $result;
    }

}