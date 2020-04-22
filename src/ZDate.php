<?php

namespace zphpsoft\tools;

/**
 * PHP开发随手使用工具包 - 日期时间篇
 * User: martinzhang
 * Date: 2016/4/22
 * Time: 11:39
 */
class ZDate
{
    /**
     * 格式化标准时间为短时间样式
     * @param  max $time 待格式化的时间(可以是10位整数的时间戳，也可以是形如 YYYY-mm-dd hh:ii::ss 之类的标准格式时间)
     * @param  int $revise 可选 时间修正值(当两服务器时间不步时)，正负值均可: +时间(后推时间)；-时间(前移时间)；单位：秒
     * @return string      返回自定义格式时间
     * @author martinzhang
     */
    public static function fmtTime2Short($time, $revise = 0)
    {
        if ($time == '') {
            return false;
        }
        if ($revise == '' || !is_numeric($revise)) {
            $revise = 0;
        }
        if (!is_numeric($time)) {
            $unixtime = strtotime($time) + $revise;
        } else {
            $unixtime = $time + $revise;
        }
        $today0time = strtotime(date('Y-m-d 00:00:00')); //今天0点时间戳
        $yesterday0time = $today0time - 86400;           //昨天0点时间戳
        $blank = time() - $unixtime;                     //“帖子时间” 与 “当前时间差”(单位：秒)
        $blank = max($blank, 1);
        switch ($blank) {
            case $blank < 60:
                $format = floor($blank / pow(60, 0)) . '秒前';
                break;
            case $blank < 60 * 60 * 1:
                $format = floor($blank / pow(60, 1)) . '分钟前';
                break;
            case $blank < 60 * 60 * 1 && $unixtime >= $today0time:
                $format = floor($blank / pow(60, 2)) . '小时前';
                break;
            case $blank >= 60 * 60 * 1 && $unixtime >= $today0time:
                $format = '今天 ' . date('H:i', $unixtime);
                break;
            case $unixtime >= $yesterday0time && $unixtime < $today0time:
                $format = '昨天 ' . date('H:i', $unixtime);
                break;
            case $blank < time() - mktime(0, 0, 0, 1, 1, date('Y')):
                $format = date('n月j日 H:i', $unixtime);
                break;

            default:
                $format = date('Y-m-d H:i', $unixtime);
        }
        return $format;
    }

    /**
     * 获取两个日期之间的月份个数(两头包含)
     * @param  string $date1 时间1
     * @param  string $date2 时间2
     * @return int            返回月份个数，如:2017-10-29 -- 2018-11-06 包含共14个月
     * @author martinzhang
     */
    public static function countMonths($date1, $date2)
    {
        $date1 = date('Y-m', strtotime($date1));
        $date2 = date('Y-m', strtotime($date2));
        list($year1, $month1) = explode('-', $date1);
        list($year2, $month2) = explode('-', $date2);
        return abs($year2 - $year1) * 12 + ($month2 - $month1) + 1;
    }

    /**
     * 将长度为14位的时间戳，格式化成标准时间
     * @param  string $time14Len 长度为14位的时间戳(如: 20181225235809)
     * @return array              返回格式化后的标准时间，如: 2018-12-25 23:58:09
     * @author martinzhang
     */
    public static function fmt14LenToStdtime($time14Len)
    {
        if (!is_numeric($time14Len) || strlen($time14Len) != 14) {
            return '';
        }

        $Y = substr($time14Len, 0, 4);
        $m = substr($time14Len, 4, 2);
        $d = substr($time14Len, 6, 2);
        $H = substr($time14Len, 8, 2);
        $i = substr($time14Len, 10, 2);
        $s = substr($time14Len, 12, 2);

        $stdTime = "$Y-$m-$d $H:$i:$s";
        return $stdTime;
    }

    /**
     * 将标准日期格式化成JS标准日期(js的月份是从0开始计数，如 0月 1月 2月...11月)
     * @param  string $fmtDate 长度为10位的标准日期(如: 2018-06-25)
     * @return string           返回月份-1后的JS日期(如: 2018-5-25)
     * @author martinzhang
     */
    public static function stdDate2JSDate($stdDate)
    {
        $Y = date('Y', strtotime($stdDate));
        $M = date('n', strtotime($stdDate)) - 1;
        $D = date('j', strtotime($stdDate));
        return "$Y-$M-$D";
    }

    /**
     * 将视频播放长度秒数格式化显示为 分'秒“ 格式
     * @param  int $length 秒数
     * @return array       格式化后的时间单位
     * @author martinzhang
     */
    public static function fmtVideoLen($length)
    {
        $length = $length == '' ? 0 : $length;
        $min = floor($length / 60);
        $sec = $length % 60;
        return ['min' => $min, 'sec' => $sec, 'lenstr' => "{$min}'{$sec}\""];
    }


}