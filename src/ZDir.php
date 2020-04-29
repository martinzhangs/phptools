<?php

namespace zphpsoft\tools;

/**
 * PHP开发随手使用工具包 - 目录篇
 * User: martinzhang
 * Date: 2016/4/22
 * Time: 11:39
 */
class ZDir
{
    protected $bootFolder = '';    //查找起始根目录
    protected $filterFiles = [];   //返回过滤文件列表结果


    public function __construct($_bootFolder = null)
    {
        if (!empty($_bootFolder)) {
            $this->bootFolder = str_replace(array('\\', '//'), '/', rtrim($_bootFolder, '/,\\'));
        }
    }

    /**
     * 按文件类型过滤多级目录内的文件
     * @param  $bootFolder 遍历起始点
     * @param  $fileType   待过滤的文件类型，img:图片文件; zip:压缩文件; rar:压缩文件; 空值表示不过滤(获取所有文件)
     * @param  $ifInto     是否进入子目录递归查找，true:递归查找(默认); false:不递归查找;
     * @return array       返回过滤出的文件列表，返回示例：
     * Array
     * (
     *   [0] => /wwwroot/ztmpDir4upBigFile/yltest/demoDirTree/CCC/j10banner.jpg
     *   [1] => /wwwroot/ztmpDir4upBigFile/yltest/demoDirTree/CCC/CC66/rose.gif
     * )
     * @author martinzhang
     */
    public function getFilterFiles($bootFolder, $fileType, $ifInto = true)
    {
        $this->doFilterFile($bootFolder, $fileType, $ifInto);
        return $this->filterFiles;
    }

    /**
     * 执行按文件类型过滤多级目录内的文件
     * @param  $bootFolder 遍历起始点
     * @param  $fileType   待过滤的文件类型，img:图片文件; zip:压缩文件; rar:压缩文件; 空值表示不过滤(获取所有文件)
     * @param  $ifInto     是否进入子目录递归查找，true:递归查找(默认); false:不递归查找;
     * @return none        失败返回false; 文件不存在返回-1;
     * @author martinzhang
     */
    private function doFilterFile($bootFolder, $fileType, $ifInto)
    {
        if (!file_exists($bootFolder)) {
            return -1;
        }
        $bootFolder = str_replace(array('\\', '//'), '/', rtrim($bootFolder, '/,\\'));
        $fileType = trim($fileType);

        try {
            $fp = opendir($bootFolder);
            while (($filename = readdir($fp)) !== false) {
                if ($filename == '.' || $filename == '..') {
                    continue 1;     //隐藏文件  飘过~~~
                }
                $filePath = $bootFolder . '/' . $filename;
                if (is_dir($filePath) && $ifInto == true) {
                    //当前节点是目录
                    $this->doFilterFile($filePath, $fileType, $ifInto);    //递归调用

                } else {
                    //当前节点是文件
                    if ($fileType == 'img' && ZIs::isImage($filePath)) {
                        $this->filterFiles[] = $filePath;                //文件赋值
                    } elseif ($fileType == 'zip' && ZIs::isZip($filePath)) {
                        $this->filterFiles[] = $filePath;
                    } elseif ($fileType == 'rar' && ZIs::isRar($filePath)) {
                        $this->filterFiles[] = $filePath;
                    } elseif (empty($fileType)) {
                        $this->filterFiles[] = $filePath;
                    }
                }
            }
            closedir($fp);


        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 删除目录
     * @param $bootFolder 待清空的目录路径
     * @return none       成功返回true; 失败返回false; 目录不存在返回-1;
     * @author martinzhang
     */
    public function rmDir($bootFolder)
    {
        if (!file_exists($bootFolder)) {
            return -1;
        }

        //清空目录
        $this->clearDir($bootFolder);

        //删除最外层目录
        if (is_writeable(dirname($bootFolder)) && $this->isEmptyDir($bootFolder)) {
            rmdir($bootFolder);
        }

        if (!file_exists($bootFolder)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * 清空目录(清空目录内所有，最外层目录保留)
     * @param $bootFolder 待清空的目录路径
     * @return none       失败返回false; 目录不存在返回-1；
     * @author martinzhang
     */
    public function clearDir($bootFolder)
    {
        if (!file_exists($bootFolder)) {
            return -1;
        }
        $bootFolder = str_replace(array('\\', '//'), '/', rtrim($bootFolder, '/,\\'));

        try {
            $fp = opendir($bootFolder);
            while (($filename = readdir($fp)) !== false) {
                if ($filename == '.' || $filename == '..') {
                    continue 1; //隐藏文件  飘过~~~
                }
                $filePath = $bootFolder . '/' . $filename;
                if (is_dir($filePath)) {
                    //当前节点是目录
                    $this->clearDir($filePath);    //递归调用
                    if (is_writeable($bootFolder) && $this->isEmptyDir($filePath) == true) {
                        rmdir($filePath);
                    }

                } else {
                    //当前节点是文件
                    if (is_writeable($bootFolder)) {
                        unlink($filePath);
                    }
                }
            }
            closedir($fp);

        } catch (\Exception $e) {
            //echo $e->getMessage();
            return false;
        }
    }


    /**
     * 判断目录是否为空
     * @param _filepath 待判断的目录路径
     * @return  bool    true为空; false非空; 目录不存在返回-1
     * @author martinzhang
     */
    public function isEmptyDir($_filepath)
    {
        if (!file_exists($_filepath)) {
            return -1;
        }
        $_filepath = str_replace(array('\\', '//'), '/', rtrim($_filepath, '/,\\'));
        $listArr = scandir($_filepath);
        if ($listArr == ['.', '..']) {
            return true;
        } else {
            return false;
        }
    }


}
