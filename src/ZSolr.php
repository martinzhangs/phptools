<?php

namespace zphpsoft\tools;


/**
 * PHP开发随手使用工具包 - Solr篇
 * User: martinzhang
 * Date: 2018/4/22
 * Time: 11:39
 */
class ZSolr
{

    /**
     * 格式化与solr相关的时间格式
     * @param string $time 待格式化的时间字符串
     * @return mixed
     */
    public static function fmtSolrTime($time)
    {
        if (strpos($time, 'T') > 0) {
            return str_replace('Z', '', str_replace('T', ' ', $time));
        } else {
            return str_replace(' ', 'T', $time) . 'Z';
        }
    }

    /**
     * 将普通数组查询简单格式化为Solr查询语法 - 用普通AND连接
     * @param  arrayy $where 必选  待格式化的数组查询格式
     * @return string             返回格式化后的字符
     * @author martinzhang
     */
    public static function fmtSolrQuery4AND($where)
    {
        if (empty($where)) {
            return '*:*';
        }
        $tmpQuey = '';
        foreach ($where as $key => $value) {
            if (trim($value) == '') {
                continue 1;
            }
            if (is_integer($value)) {
                $tmpQuey .= " $key:$value AND";
            } else {
                $tmpQuey .= " $key:\"$value\" AND";
            }
        }
        return trim($tmpQuey, 'AND');
    }

    /**
     * 将普通数组查询简单格式化为Solr查询语法 - 用普通OR连接
     * @param  arrayy $where 必选  待格式化的数组查询格式
     * @return string             返回格式化后的字符
     * @author martinzhang
     */
    public static function fmtSolrQuery4OR($where)
    {
        if (empty($where)) {
            return '*:*';
        }
        $tmpQuey = '';
        foreach ($where as $key => $value) {
            if (trim($value) == '') {
                continue 1;
            }
            if (is_integer($value)) {
                $tmpQuey .= " $key:$value OR";
            } else {
                $tmpQuey .= " $key:\"$value\" OR";
            }
        }
        return trim($tmpQuey, 'OR');
    }

    /**
     * 将普通数组查询简单格式化为Solr查询语法 - 用普通OR连接(适用于多ids的in条件)
     * @param  arrayy $arrIds 必选  待格式化的数组或字符串查询格式ids
     * @param  string $idName 必选  需要查询的字段名
     * @return string              返回格式化后的字符
     * @author martinzhang
     */
    public static function fmtSolrQuery4idIN($arrIds, $idName)
    {
        $arrIds = self::fmtArrayFilter($arrIds);
        $solrQuery = '';
        foreach ($arrIds as $id) {
            $solrQuery .= " $idName:$id OR";
        }
        return trim($solrQuery, 'OR');
    }


}
