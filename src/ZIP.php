<?php

namespace zphpsoft\tools;


/**
 * PHP开发随手使用工具包 - IP网段地址解析
 * User: martinzhang
 * Date: 2018/4/22
 * Time: 11:39
 */
class ZIP
{
    /**
     * 判断给定字符串是否为IPv4地址
     * @param  string $ip 待验证的字符串
     * @return boolean      是ip地址返回true；不是ip地址返回false；
     * @author martinzhang
     */
    public static function isIPv4($ip)
    {
        if (preg_match('/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/', $ip)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 将IPv4转换为数值型
     * @param string $ip 待转换的IPv4地址
     * @return int       返回转换后的数值
     * @author martinzhang
     */
    public static function getIP2long($ip)
    {
        if (!self::isIPv4($ip)) {
            return 0;
        }
        $ip2long = ip2long($ip);
        if ($ip2long < 0 || $ip2long == false) {
            $ip2long = sprintf("%u", ip2long($ip));
        }
        if ($ip2long < 0 || $ip2long == false) {
            @list($ip1, $ip2, $ip3, $ip4) = explode('.', $ip);
            $ip2long = $ip1 * pow(256, 3) + $ip2 * pow(256, 2) + $ip3 * pow(256, 1) + $ip4;
        }

        return $ip2long;
    }

    /**
     * 获取IP网段地址详情
     * @param string $ip_mask IPv4地址与子网掩码合写格式，如: 172.16.41.225/24 或 172.16.41.225/255.255.255.0
     * @return array          返回格式化后的IPv4详情，返回示例：
     * Array
     * (
     *   [mask] => 255.255.248.0 ------------------> 子网掩码
     *   [net] => 172.16.40.0 ---------------------> 网络地址
     *   [broadcast] => 172.16.47.255 -------------> 广播地址
     *   [beginIP] => 172.16.40.1 -----------------> 首个可用IP
     *   [beginIPlong] => 2886739969 --------------> 首个可用IP(数值型)
     *   [endIP] => 172.16.47.254 -----------------> 末个可用IP
     *   [endIPlong] => 2886742014 ----------------> 末个可用IP(数值型)
     * )
     * @author martinzhang
     */
    public static function getIPdetail($ip_mask)
    {
        $ip_maskArr = explode('/', trim($ip_mask));
        if (count($ip_maskArr) != 2 || !self::isIPv4($ip_maskArr[0]) || (!is_numeric($ip_maskArr[1]) && !self::isIPv4($ip_maskArr[1]))) {
            return ['mask' => '', 'net' => '', 'broadcast' => '', 'beginIP' => '', 'beginIPlong' => '', 'endIP' => '', 'endIPlong' => ''];
        }

        $ip_binstr = self::ipstr2binstr($ip_maskArr[0]);    //ip地址32位二进制符
        $mask_binstr = self::mask2binstr($ip_maskArr[1]);   //子网掩码32位二进制符

        //计算子网掩码
        if (!is_numeric($ip_maskArr[1])) {
            $mask_ipstr = $ip_maskArr[1];
        } else {
            $mask_ipstr = self::binstr2ipstr($mask_binstr);
        }

        //计算网络地址
        $net_binstr = $ip_binstr & $mask_binstr;            //ip地址与子网掩码进行"逻辑与"运算得到网络地址
        $net_ipstr = self::binstr2ipstr($net_binstr);       //换算出网络地址的数字形式

        //计算广播地址
        $mask_binstr_rev = str_replace(['0', '1', 'z'], ['z', '0', '1'], $mask_binstr);   //先将掩码二进制符取"反"
        $broadcast_binstr = $ip_binstr | $mask_binstr_rev;                                //然后与ip地址进行"逻辑或"运算得到广播地址
        $broadcast_ipstr = self::binstr2ipstr($broadcast_binstr);

        //计算第一个可用IP及最后一个可用IP
        @list($netip1, $netip2, $netip3, $netip4) = explode('.', $net_ipstr);
        @list($broadcastip1, $broadcastip2, $broadcastip3, $broadcastip4) = explode('.', $broadcast_ipstr);

        //第一个可用IP
        $ipBegin4 = min($broadcastip4, $netip4 + 1);
        $beginIP = "$netip1.$netip2.$netip3.$ipBegin4";
        $beginIPlong = self::getIP2long($beginIP);

        //最后一个可用IP
        $ipEnd4 = max($ipBegin4, $broadcastip4 - 1);
        $endIP = "$broadcastip1.$broadcastip2.$broadcastip3.$ipEnd4";
        $endIPlong = self::getIP2long($endIP);


        return ['mask' => $mask_ipstr, 'net' => $net_ipstr, 'broadcast' => $broadcast_ipstr, 'beginIP' => $beginIP, 'beginIPlong' => $beginIPlong, 'endIP' => $endIP, 'endIPlong' => $endIPlong];
    }

    /**
     * 将IPv4地址转换为32位二进制字符串
     * @param string $ipstr 待转换的IPv4地址
     * @return string       返回转换后的32位二进制字串，如 172.16.41.225 转为 10101100000100000010100111100001
     * @author martinzhang
     */
    private static function ipstr2binstr($ipstr)
    {
        if (!is_string($ipstr)) {
            return '';
        }

        $ipArr = explode('.', $ipstr);
        if (count($ipArr) != 4) {
            return '';
        }

        $binstr = '';
        foreach ($ipArr as $number) {
            $decbin = decbin($number);                            //将数字转换为二进制字串
            $binstr .= str_pad($decbin, 8, '0', STR_PAD_LEFT);    //将不足8位的前补0
        }
        return $binstr;
    }

    /**
     * 将32位二进制字符串转换为IPv4地址
     * @param string $binstr 待转换的32位二进制字符串
     * @return string        返回转换后的IPv4地址 如：11111111111111111111100000000000 转为 255.255.248.0
     * @author martinzhang
     */
    private static function binstr2ipstr($binstr)
    {
        $binstr = trim($binstr);
        if (empty($binstr)) {
            return '';
        } elseif (!is_string($binstr)) {
            return '';
        } elseif (strlen($binstr) != 32) {
            return '';
        }

        $ipArr = [];
        for ($i = 0; $i < 4; $i++) {
            $ip = substr($binstr, $i * 8, 8);  //每8位切一份
            $ipArr[] = bindec($ip);            //将切出的二进制串转换为十进制数字
        }
        $ipstr = implode('.', $ipArr);
        return $ipstr;
    }

    /**
     * 将子网掩码转为二进制字符串
     * @param  mix $mask 待转化的子网掩码(数字或ip地址均可)
     * @return string        返回转换的二进制字符 如: 24 转为 11111111111111111111111100000000
     *                                            255.255.255.192 转为 11111111111111111111111111000000
     * @author martinzhang
     */
    private static function mask2binstr($mask)
    {
        if (is_numeric($mask)) {
            if ($mask < 0 || $mask > 32) {
                return '';
            }
            $binstr = str_repeat('1', $mask) . str_repeat('0', 32 - $mask);

        } elseif (self::isIPv4($mask)) {
            $binstr = self::ipstr2binstr($mask);
        } else {
            $binstr = '';
        }

        return $binstr;
    }

}
