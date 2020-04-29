<?php

namespace zphpsoft\tools;

/**
 * PHP开发随手使用工具包 - 网络请求篇
 * User: martinzhang
 * Date: 2018/4/22
 * Time: 11:39
 */
class ZHttp
{

    /**
     * 以GET方式请求远程资源
     * @param string $url  待请求的远程资源
     * @param array $param 参数(数组类型)
     * @param int $timeout 设置最大超时时间(单位:秒)
     * @return mixed
     * @author martinzhang
     */
    public static function curlGet($url, $param = [], $timeout = 20)
    {
        $url = trim($url);
        if (empty($url)) {
            return ['http_code' => 1, 'getinfo' => 'url地址不能为空', 'data' => ''];
        }
        if (!is_array($param)) {
            return ['http_code' => 1, 'getinfo' => 'params参数必须为数组类型', 'data' => ''];
        }
        if (!empty($param)) {
            //下面将关联数组参数转换为以&符连接的URI参数
            $param2String = http_build_query($param);
            if (strpos($url, '?') > 0) {
                $url = rtrim($url, '&') . '&' . $param2String;
            } else {
                $url = rtrim($url, '&') . '?' . $param2String;
            }
        }

        //HTTP请求头信息
        $httpHeader[] = 'Content-Type: application/json;charset=utf-8';
        $httpHeader[] = 'User-Agent: Apache/2.4.6 (Unix)';

        //模拟用户使用的浏览器
        $useragent = 'Mozilla/5.0 (Windows NT 6.1; rv:22.0) Gecko/20100101 Firefox/50.0';

        //执行CURL请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);                    //设置需要获取的URL地址，也可以在curl_init()函数中设置
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);      //设置HTTP头信息
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);            //设置最大超时时间(单位：秒)
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); //头部要送出'Expect: '
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //强制使用IPV4协议解析域名
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);        //模拟用户使用的浏览器
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);         //使用自动跳转
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        //跳过对证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);        //跳过从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);         //设置将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。true返回(不输出)；false不返回(直接输出)
        $data = curl_exec($ch);                                 //执行
        $getinfo = curl_getinfo($ch);                           //获取一个cURL连接资源句柄的信息
        $curl_errno = curl_errno($ch);                          //错误号
        curl_close($ch);                                        //关闭
        return ['http_code' => $getinfo['http_code'], 'getinfo' => $getinfo, 'data' => $data];
    }


    /**
     * 以POST方式请求远程资源
     * @param string $url 待请求的远程资源
     * @param array|string $param 参数，格式：一维数组、普通字符串、字符流
     * @param int $timeout 设置最大超时时间(单位:秒)
     * @return mixed
     * @author martinzhang
     *
     * @使用示例：
     *
     * 1.上传文件(注: PHPv5.5+ 用CURLFile()类, 而不再用@了)
     *   上传文件时$param必须是一维数组，如下：
     *   $param = ['name'=>'zhang3', 'age'=>22, 'uploadFile'=>(new \CURLFile(realpath('/tmp/xiaole.jpg')))];
     *
     *   调用方式： $res = ZHttp::curlPost($url, $param);
     *
     *
     * 2.传递二维数组参数
     *   使用 $param = http_build_query($paramArr); 构造一个字符串参数，使$param以字符类型进行传参，如下：
     *       $param = "id=10&userInfo[name]=zhang3&userInfo[cls]=cls3";
     *
     *   调用方式： $res = ZHttp::curlPost($url, $param);
     *
     *   服务端取数组userInfo值的方式: $_POST['userInfo']
     *
     *
     * 3.上传文件的同时使用二维数组参数
     *   上传文件时$param必须是一维数组，如下：
     *   $param = ['name'=>'zhang3', 'age'=>22, 'uploadFile'=>(new \CURLFile(realpath('/tmp/xiaole.jpg')))];
     *
     *   当$param为数组时，要传递二维参数只能通过$url附带，将数组参数附着在$url后面，服务端使用$_GET获取数组参数
     *   使用 $param4url = http_build_query($paramArr); 构造一个字符串参数，拼接$url，如下：
     *   $url = "$url?$param4url";
     *   $url = "$url?id=10&userInfo[name]=zhang3&userInfo[cls]=cls3";
     *
     *   调用方式： $res = ZHttp::curlPost($url, $param);
     *
     *   服务端取数组userInfo值的方式: $_GET['userInfo']
     *
     *
     * 4.以字符流的形式上传文件
     *   $filepath = '/tmp/xiaole.jpg';
     *
     *   方法1：生成$param参数值(字符流)
     *   $param = file_get_contents($filepath);
     *
     *   方法2：生成$param参数值(字符流)
     *   $fp    = fopen($filepath, 'r');
     *   $param = fread($fp, filesize($filepath));
     *   fclose($fp);
     *
     *   调用方式： $res = ZHttp::curlPost($url, $param);
     *
     *   当然服务端接收用：$postStr = file_get_contents('php://input', 'r');
     *
     */
    public static function curlPost($url, $param, $timeout = 20)
    {
        $url = trim($url);
        if (empty($url)) {
            return ['http_code' => 1, 'getinfo' => 'url地址不能为空', 'data' => ''];
        }

        //HTTP请求头信息
        if (is_array($param)) {
            $httpHeader = [];
            $param = $param;
        } elseif (is_string($param)) {
            $httpHeader = ['Accept: application/json, text/xml, text/javascript, */*', 'Content-Type: application/x-www-form-urlencoded',];
            $param = trim($param, '?& ');
        }

        //模拟用户使用的浏览器
        $useragent = 'Mozilla/5.0 (Windows NT 6.1; rv:22.0) Gecko/20100101 Firefox/50.0';

        //执行CURL请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);                    //设置需要获取的URL地址，也可以在curl_init()函数中设置
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);      //设置HTTP头信息
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);            //设置最大超时时间(单位：秒)
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); //头部要送出'Expect: '
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //强制使用IPV4协议解析域名
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);        //模拟用户使用的浏览器
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);         //使用自动跳转
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        //跳过对证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);        //跳过从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_POST, true);                   //设置启用时会发送一个常规的POST请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);           //设置POST参数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);         //设置将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。true返回(不输出)；false不返回(直接输出)
        $data = curl_exec($ch);                                 //执行
        $getinfo = curl_getinfo($ch);                           //获取一个cURL连接资源句柄的信息
        $curl_errno = curl_errno($ch);                          //错误号
        curl_close($ch);                                        //关闭
        return ['http_code' => $getinfo['http_code'], 'getinfo' => $getinfo, 'data' => $data];
    }


}
