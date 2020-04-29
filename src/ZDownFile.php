<?php

namespace zphpsoft\tools;

/**
 * PHP开发随手使用工具包 - 文件下载篇
 * User: martinzhang
 * Date: 2018/4/22
 * Time: 11:39
 */
class ZDownFile
{
    /**
     * 下载远程文件
     * @param  string $fileUrl 远程文件URL
     * @param  string $saveDir 本地存储目录(如果不存在自动创建)
     * @param  string $saveName 保存文件名(如不提供则按日期格式保存)
     * @return array            返回下载结果，返回示例：
     * Array
        (
            [errcode] => 0
            [errmsg] => ok
            [size] => 4990
            [saveName] => 20200429140006-06270-4710.png
            [fullPath] => /wwwroot/zz2.d/20200429140006-06270-4710.png
        )
     * @author martinzhang
     */
    public static function downloadFile($fileUrl, $saveDir, $saveName = '')
    {
        try {
            //存储目录(如果不存在自动创建)
            if (!file_exists($saveDir)) {
                $res = mkdir($saveDir, 0777, true);
                if (!$res) {
                    throw new \Exception("存储目录创建失败(可能没有写权限)", 601);
                }
            } else {
                if (!is_writable($saveDir)) {
                    throw new \Exception("存储目录没有写权限", 601);
                }
            }

            //抓取远程文件
            $con = file_get_contents($fileUrl);
            if (empty($con)) {
                throw new \Exception("未获取到远程文件内容", 601);
            }

            //写入本地文件
            if ($saveName == '') {
                //由系统自动创建生成文件名
                list($usec, $sec) = explode(' ', microtime());
                $saveNameNew = date('YmdHis') . '-' . substr($usec, 2, 5) . '-' . mt_rand(1000, 9999) . '.ZZZ';
            } else {
                //用户自主指定文件名
                $saveNameNew = $saveName;
            }
            $fullPath = rtrim($saveDir, '/') . '/' . $saveNameNew;
            $len = file_put_contents($fullPath, $con);
            if (empty($len)) {
                throw new \Exception("写入本地文件内容长度为0", 601);
            }

            //重命名扩展名
            if ($saveName == '') {
                //由系统自动创建生成文件名
                $ExtMap = [
                    'image/jpg' => 'jpg', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/pjpeg' => 'jpg', 'image/gif' => 'gif', 'image/bmp' => 'bmp', 'image/x-png' => 'png', 'image/x-ms-bmp' => 'bmp',
                    'application/x-shockwave-flash' => 'swf',
                    'video/mp4' => 'mp4', 'video/x-ms-wmv' => 'wmv', 'video/mpeg' => 'mpeg', 'video/x-ms-asf' => 'asf', 'video/x-msvideo' => 'avi', 'video/avi' => 'avi',
                ];
                $type = finfo_file(finfo_open(FILEINFO_MIME), $fullPath);
                if ($type) {
                    list($mime,) = explode(';', $type);
                    if (isset($ExtMap[$mime])) {
                        $saveNameNew = str_replace('.ZZZ', '.' . $ExtMap[$mime], $saveNameNew);
                        $fullPathNew = rtrim($saveDir, '/') . '/' . $saveNameNew;
                        $rename = rename($fullPath, $fullPathNew);
                        if ($rename) {
                            $fullPath = $fullPathNew;
                        }
                    }

                }

            }

            return ['errcode' => 0, 'errmsg' => 'ok', 'size' => $len, 'saveName' => $saveNameNew, 'fullPath' => $fullPath];

        } catch (\Exception $e) {
            return ['errcode' => 1, 'errmsg' => $e->getMessage(), 'size' => 0, 'saveName' => '', 'fullPath' => ''];
        }

    }


}
