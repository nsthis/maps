<?php

/**
 * Author: This
 * Email: yuehuaxw@gmail.com
 * Date: 2021-03-11
 * Title: 排序
 */

namespace elliot;

class Sort
{
    /**
     * User: this
     * Date: 2021/3/23
     * Time: 15:31
     * 对指定键值排序
     */
    function arraySort($array, $keys, $type = 'asc')
    {
        if(!is_array($array)||empty($array)||!in_array(strtolower($type),array('asc','desc'))) return '';

        $keysvalue=array();

        foreach($array as $key=>$val){
            $val[$keys]=str_replace('-','',$val[$keys]);
            $val[$keys]=str_replace(' ','',$val[$keys]);
            $val[$keys]=str_replace(':','',$val[$keys]);
            $keysvalue[] =$val[$keys];
        }

        //key值排序
        asort($keysvalue);

        //指针重新指向数组第一个
        reset($keysvalue);

        foreach($keysvalue as $key=>$vals){
            $keysort[]=$key;
        }
        $keysvalue=array();
        $count=count($keysort);
        if(strtolower($type)!='asc'){
            for($i=$count-1;$i>=0;$i--){
                $keysvalue[]=$array[$keysort[$i]];
            }
        }else{
            for($i=0;$i<$count;$i++){
                $keysvalue[]=$array[$keysort[$i]];
            }
        }
        return $keysvalue;
    }
}