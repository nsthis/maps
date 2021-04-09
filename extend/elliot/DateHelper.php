<?php

namespace  elliot;
class DateHelper
{
    /**
     * 获取今天是几号
     *  
     *  */
    public static function getDateIsToday()
    {
        return date('d');
    }
    /**
     * 获取今天的开始时间
     */
    public static function getTodayStartTime()
    {
        return strtotime(date('Y-m-d', time()));
    }
    /**
     * 获取今天的结束时间
     */
    public static function getTodayEndTime()
    {
        return strtotime(date('Y-m-d', time()))+86400-1;
    }
    /**
     * 获取昨天的开始时间
     */
    public static function getYesterdayStartTime()
    {
        return strtotime(date('Y-m-d', strtotime("-1 day")));
    }
    /**
     * 获取昨天的结束时间
     */
    public static function getYesterdayEndTime()
    {
        return strtotime(date('Y-m-d', strtotime("-1 day")))+86400-1;
    }
    
    /* *************************************************** */
    /* 周  */
    /* *************************************************** */
    
    
    /**
     * 获取今天是本周周几 
     * 周日是 0 周一到周六是 1 - 6 
     */
    public static function getDayOfTheWeek()
    {
        return $w=date('w');
    }
    /**
     * 获取本周的开始时间
     */
    public static function getThisWeekStartTime()
    {
        return $thisWeekStartTime=self::getTodayStartTime()-self::getDayOfTheWeek()*60*60*24;
    }
    /**
     * 获取本周的结束时间
     */
    public static function getThisWeekEndTime()
    {
        return $thisWeekEndTime=self::getTodayStartTime()+(7-self::getDayOfTheWeek())*60*60*24-1;
    }
    /**
     * 获取前几天的开始时间
     */
    public static function getDayStartTime($days)
    {
        $days = $days-1;
        return strtotime(date('Y-m-d', strtotime("-$days day")));
    }
    /**
     * 获取最近4周的开始时间
     */
    public static function getWeekStartTime($weeks)
    {
        if ($weeks==0){
            return  strtotime(date('Y-m-d',(time()-((date('w')==0?7:date('w'))-1)*24*3600)));
        }else {
            return  strtotime(date('Y-m-d',strtotime('-'.$weeks.' week last monday', time())));
        }
         
    }
    /* 获取 */
    
    
    /* *************************************************** */
    /* 月份 */
    /* *************************************************** */
    
    
    /* 
     * 获取当月月份
     *  
     *  */
    public static function getMonth()
    {
        return $month=date('m');
    }
    /* 
     * 获取月的总计天数
     *  */
    public static function getMonthTotalDay($status=1)
    {
        if($status==1)
        {
            //获取本月时间
            $month=date('m');
            $year=date('Y');
        }
        if($status==2)
        {
            //获取上月信息
            $data=self::getPreMonthMessage();
            $month=$data['lastmonth'];
            $year=$data['lastyear'];
        }
     //   return $monthTotalDay=cal_days_in_month(CAL_GREGORIAN,$month,$year);
        return $monthTotalDay=date('t',strtotime($year.'-'.$month));
    }
    
    /**
     * 获取本月的开始时间
     */
    public static function getMonthStartTime()
    {
        return $monthStart = strtotime(date("Y-m-01",strtotime(date('Y-m',time()))));
    }
    /**
     * 获取本月的结束时间
     */
    public static function getMonthEndTime()
    {
        return mktime(23,59,59,date('m'),date('t'),date('Y'));
    }
    /**
     * 获取上个月的开始时间
     */
    public static function getPreMonthStartTime()
    {
        return strtotime(date('Y-m-01', strtotime("-1 month")));
    }
    /**
     * 获取上个月的结束时间
     */
    public static function getPreMonthEndTime()
    {
       $lastMonthLastDay=self::getPreMonth();
       return strtotime($lastMonthLastDay.' 23:59:59');
    }
    
    /* 
     * 获取上月时间
     *  1 结束时间  2  开始时间
     *  */
    private  static function getPreMonth($status=1)
    {
        
        $data=self::getPreMonthMessage();
        $lastStartDay = $data['lastyear'] . '-' . $data['lastmonth'] . '-1';
        $lastEndDay = $data['lastyear'] . '-' . $data['lastmonth'] . '-' . date('t', strtotime($lastStartDay));

        switch ($status){
            case 1:
                return $lastEndDay;
                break;
            case 2:
                return $lastStartDay;
                break;
        }
    }
    /*
     * 获取上月的年和月信息
     *  */
    public  static function getPreMonthMessage()
    {
        $thismonth = date('m');
        $thisyear = date('Y');
        if ($thismonth == 1) {
            $lastmonth = 12;
            $lastyear = $thisyear - 1;
        } else {
            $lastmonth = $thismonth - 1;
            $lastyear = $thisyear;
        }
        return [
            'lastmonth'=>$lastmonth,
            'lastyear'=>$lastyear
            
        ];
    }

    /*
     * 获取今日为周几 0-6 0为周日
     *  */
    public static function getDateW()
    {
        return date("w");
    }

    /**
     * function：计算两个日期相隔多少年，多少月，多少天
     * param string $date1[格式如：2011-11-5]
     * param string $date2[格式如：2012-12-01]
     * return array array('年','月','日');
     */
    public static function checkMonthlyNum($date1, $date2)
    {
        $datetime1 = new \DateTime($date1);
        $datetime2 = new \DateTime($date2);
        $interval = $datetime1->diff($datetime2);
        $time['y']         = $interval->format('%Y');
        $time['m']         = $interval->format('%m');
        $time['d']         = $interval->format('%d');
        $time['h']         = $interval->format('%H');
        $time['i']         = $interval->format('%i');
        $time['s']         = $interval->format('%s');
        $time['a']         = $interval->format('%a');    // 两个时间相差总天数
        return $time;
    }

    /**
     * User: This
     * Date: 2020/5/19
     * Time: 17:40
     * 获取开始时间到结束时间的月份
     */
    public static function showMonthRange($start, $end)
    {
        $end = date('Ym', strtotime($end)); // 转换为月
        $range = [];
        $i = 0;
        do {
            $month = date('Ym', strtotime($start . ' + ' . $i . ' month'));
            $range[] = $month;
            $i++;
        } while ($month < $end);

        return $range;
    }

    /**
     * User: This
     * Date: 2020/4/27
     * Time: 15:11
     * 获取Unix毫秒时间戳
     */
    public static function msectime() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }

    /**
     * 对月进行加减
     * @param int $date 时间戳
     * @param string $algorithm 比如 +1 month 或者-1 month
     * @return string
     */
    public static function  calculateMonth(int $date, string $algorithm): string
    {
        $day = date('d', $date);
        $hst = date('H:i:s', $date);
        $newDateFirst = date('Y-m', strtotime('first day of ' . $algorithm, $date));
        $newDate = $newDateFirst . '-' . $day . ' ' . $hst;
        $newDateLast = date('Y-m-d', strtotime('last day of ' . $algorithm, $date)) . ' ' . $hst;
        return $newDateLast < $newDate ? $newDateLast : $newDate;

    }

    /**
     *
     * 获取指定年月的开始和结束时间戳
     *
     * @param int $year 年份
     * @param int $month 月份
     * @return array(开始时间,结束时间)
     */

    public static function getMonthBeginAndEnd($year = 0, $month = 0) {

        $year = $year ? $year : date('Y');

        $month = $month ? $month : date('m');

        $d = date('t', strtotime($year . '-' . $month));

        return [
            date('Y-m-d H:i:s', strtotime($year . '-' . $month)),
            date('Y-m-d H:i:s', mktime(23, 59, 59, $month, $d, $year))
        ];

    }


}