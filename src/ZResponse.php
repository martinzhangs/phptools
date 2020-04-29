<?php

namespace zphpsoft\tools;

/**
 * PHP开发随手使用工具包 - 响应客户端篇
 * User: martinzhang
 * Date: 2016/4/22
 * Time: 11:39
 */
class ZResponse
{

    /**
     * 响应客户端
     * @param  array $arrResponse 需要返回的数据
     * @return json string
     * @author martinzhang
     *
     * 使用示例：
     * $reData = ['data'=>['jsapiParams'=>'abc我的测试']];
     * $reData = ['errcode'=>0, 'errmsg'=>'成功', 'data'=>['jsapiParams'=>'abc我的测试']];
     * ZResponse::response($reData);
     */
    public static function response($arrResponse = [])
    {
        //声明响应头为json格式
        header('Content-Type:application/json; charset=utf-8');

        //响应参数进行合法性校验
        if (!is_array($arrResponse)) {
            die(json_encode(['errcode' => 1, 'errmsg' => '响应结果应该为数组类型', 'data' => (object)[]], JSON_UNESCAPED_UNICODE));
        }

        //初始化$result
        $result = ['errcode' => 0, 'errmsg' => 'ok', 'data' => []];

        //合并数组，若包含相同的键，那么最末一个值将覆盖前面的值
        $result = array_merge($result, $arrResponse);
        if (empty($result['data'])) {
            $result['data'] = (object)[];
        }

        //响应
        die(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 响应“成功”消息
     * @param array $data 待返回的数据
     * @return json string
     * @author martinzhang
     */
    public static function responseOK($data = [])
    {
        self::response(['data' => $data]);
    }

    /**
     * 响应“失败”消息
     * @param string $errmsg 待返回失败消息内容
     * @return json string
     * @author martinzhang
     */
    public static function responseError($errmsg = '请求失败')
    {
        self::response(['errcode' => 1, 'errmsg' => $errmsg]);
    }

    
}
