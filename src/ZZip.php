<?php

namespace zphpsoft\tools;

/**
 * PHP开发随手使用工具包 - 压缩解压篇
 * User: martinzhang
 * Date: 2018/4/22
 * Time: 11:39
 */
class ZZip
{

    /**
     * 解压zip文件
     * @param string $zipFile 待解压的zip文件
     * @param string $saveDir 解压到的目录
     * @return                成功返回true; 失败返回false;
     * @author martinzhang
     */
    public static function unZip($zipFile, $saveDir)
    {
        if (!file_exists($zipFile) || !ZIs::isZip($zipFile)) {
            return false;
        }
        if (!file_exists($saveDir)) {
            mkdir($saveDir, 0777, true);
        }
        if (!file_exists($saveDir) || !is_writeable($saveDir)) {
            return false;
        }

        $zip = new \ZipArchive();
        $res = $zip->open($zipFile);
        if ($res == true) {
            $zip->extractTo($saveDir); //解压到$saveDir文件夹下（$saveDir没有会自动创建）
        }
        $zip->close();

        if (file_exists($saveDir)) {
            return true;
        } else {
            return false;
        }

    }


}
