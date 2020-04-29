<?php

namespace zphpsoft\tools;

/**
 * PHP开发随手使用工具包 - 文件上传篇
 * User: martinzhang
 * Date: 2018/4/22
 * Time: 11:39
 */
class ZUpFile
{

    /**
     * 上传单个文件
     * 客户端上传表单名需设置为字符类型，如: <input name="upfile" type="file" />
     * @param string $saveDir 文件保存目录(如果不存在自动创建)
     * @param string $inputName input表单控件名
     * @param string $filetype 上传的文件类型(可取值:image|flash|media)
     * @param number $maxFileSize 允许文件上传最大体积(单位:MB)
     * @param bool $isRename 上传后是否按时间格式重命名文件(默认:true是)，若需要保留原文件名可设置为false；
     * @return array 返回单个上传文件的结果，返回示例：
     * Array
     * (
     *    [errcode] => 0
     *    [errmsg] => ok
     *    [size] => 39261
     *    [name] => 20160601170040-53582-1556
     *    [ext] => .jpg
     *    [saveName] => 20160601170040-53582-1556.jpg
     *    [fullPath] => /wwwroot/yltest/static/upload/pickphoto/20160601170040-53582-1556.jpg
     * )
     *
     * 用法举例：
     *
     * //存储路径
     * $relativeDir = \Yii::$app->params['imgPath']['banner'];       //图片保存目录"相对路径"
     * $saveDir = $this->staticPath.'/'.$relativeDir;                //图片保存目录"绝对路径"
     *
     * //执行上传
     * $upFile = UploadFile::UploadFile($saveDir, 'cover', $filetype='image', $maxFileSize=10, $isRename=true);
     *
     * @author martinzhang
     */
    public static function UploadFile($saveDir, $inputName, $filetype = 'image', $maxFileSize = 10, $isRename = true)
    {
        if (!is_string($_FILES[$inputName]['name'])) {
            return ['errcode' => 51, 'errmsg' => '单文件上传，客户端上传表单控件名需设置为字符类型', 'size' => 0, 'name' => '', 'ext' => '', 'saveName' => '', 'fullPath' => ''];
        }

        //存储目录(如果不存在自动创建)
        $saveDir = rtrim($saveDir, '/');
        if (!file_exists($saveDir)) {
            $res = mkdir($saveDir, 0777, true);
            if (!$res) {
                return ['errcode' => 52, 'errmsg' => '存储目录创建失败(可能没有写权限)', 'size' => 0, 'name' => '', 'ext' => '', 'saveName' => '', 'fullPath' => ''];
            }
        } else {
            if (!is_writable($saveDir)) {
                return ['errcode' => 52, 'errmsg' => '存储目录没有写权限', 'size' => 0, 'name' => '', 'ext' => '', 'saveName' => '', 'fullPath' => ''];
            }
        }

        //允许上传的文件类型
        $uptypes = array(
            'image' => array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/gif'),
            'flash' => array('application/x-shockwave-flash'),
            'media' => array('video/mp4', 'video/x-ms-wmv', 'video/mpeg', 'video/x-ms-asf', 'video/x-msvideo', 'video/avi'),
        );

        $upfile_name = $_FILES[$inputName]['name'];         //上传的文件名
        $upfile_type = $_FILES[$inputName]['type'];         //上传的文件类型
        $upfile_tmp_name = $_FILES[$inputName]['tmp_name']; //文件上传到临时文件夹地址
        $upfile_error = $_FILES[$inputName]['error'];       //上传后的错误信息(0表示成功上传到临时文件夹)
        $upfile_size = $_FILES[$inputName]['size'];         //上传的文件大小(字节)


        //是否存在临时文件
        if (!is_uploaded_file($upfile_tmp_name)) {
            return ['errcode' => 53, 'errmsg' => "{$upfile_name} 临时文件不存在", 'size' => $upfile_size, 'name' => '', 'ext' => '', 'saveName' => '', 'fullPath' => ''];
        }

        //检查文件大小
        if ($upfile_size > $maxFileSize * 1048576) {
            return ['errcode' => 54, 'errmsg' => "{$upfile_name} 文件太大：" . sprintf('%.2f', ($upfile_size / 1048576)) . 'MB', 'size' => $upfile_size, 'name' => '', 'ext' => '', 'saveName' => '', 'fullPath' => ''];
        }

        //检查文件类型
        if (!in_array($upfile_type, $uptypes[$filetype])) {
            return ['errcode' => 55, 'errmsg' => "{$upfile_name} 文件类型不符", 'size' => $upfile_size, 'name' => '', 'ext' => '', 'saveName' => '', 'fullPath' => ''];
        }

        //拼装上传后文件保存路径+文件名
        $pinfo = pathinfo($upfile_name);                                                //上传的文件信息dirname|basename|extension|filename
        $ext = isset($pinfo['extension']) ? '.' . strtolower($pinfo['extension']) : '';    //上传 .扩展名
        if ($isRename == true) {
            //重新按时间命名
            list($usec, $sec) = explode(' ', microtime());
            $name = date('YmdHis') . '-' . substr($usec, 2, 5) . '-' . mt_rand(1000, 9999);
        } else {
            //按源文件命名
            $name = $pinfo['filename'];
        }
        $saveName = $name . $ext;                  //上传后 文件名.扩展名
        $fullPath = $saveDir . '/' . $saveName;    //上传后 文件保存完整路径(含文件名)

        //移动文件
        if (!move_uploaded_file($upfile_tmp_name, $fullPath)) {
            return ['errcode' => 56, 'errmsg' => "{$upfile_name}移动文件出错", 'size' => $upfile_size, 'name' => '', 'ext' => '', 'saveName' => $saveName, 'fullPath' => ''];
        }

        //成功
        return ['errcode' => 0, 'errmsg' => 'ok', 'size' => $upfile_size, 'name' => $name, 'ext' => $ext, 'saveName' => $saveName, 'fullPath' => $fullPath];

    }


    /**
     * 上传多个文件(可一次性上传一个或多个文件)
     * 客户端上传表单名需设置为数组类型，如: <input name="upfile[]" type="file" />
     * @param string $saveDir 文件保存目录(如果不存在自动创建)
     * @param string $inputName input表单控件名
     * @param string $filetype 上传的文件类型(可取值:image|flash|media)
     * @param number $maxFileSize 允许文件上传最大体积(单位:MB)
     * @param bool $isRename 上传后是否按时间格式重命名文件(默认:true是)，若需要保留原文件名可设置为false；
     * @return array 返回各个上传文件的结果，如下
     * Array
     * (
     *    [errcode] => 0
     *    [errmsg] => ok
     *    [list] => Array
     *       (
     *           [0] => Array
     *              (
     *                   [errcode] => 0
     *                   [errmsg] => iitax.jpg 上传成功
     *                   [size] => 39261
     *                   [name] => 20190412083935-97159-3659
     *                   [ext] => .jpg
     *                   [saveName] => 20190412083935-97159-3659.jpg
     *                   [fullPath] => /wwwroot/zhang/ddnm_appapi/static/upload/icon/user/20190412083935-97159-3659.jpg
     *               )
     *
     *           [1] => Array
     *               (
     *                   [errcode] => 0
     *                   [errmsg] => j-10.jpg 上传成功
     *                   [size] => 131486
     *                   [name] => 20190412083935-97284-6022
     *                   [ext] => .jpg
     *                   [saveName] => 20190412083935-97284-6022.jpg
     *                   [fullPath] => /wwwroot/zhang/ddnm_appapi/static/upload/icon/user/20190412083935-97284-6022.jpg
     *                )
     *
     *           [2] => Array
     *                (
     *                   [errcode] => 53
     *                   [errmsg] =>  临时文件不存在
     *                   [size] => 0
     *                   [name] =>
     *                   [ext] =>
     *                   [saveName] => j-10.jpg
     *                   [fullPath] =>
     *                 )
     *         )
     * )
     *
     * @author martinzhang
     */
    public static function UploadFiles($saveDir, $inputName, $filetype = 'image', $maxFileSize = 10, $isRename = true)
    {
        if (!is_array($_FILES[$inputName]['name'])) {
            return ['errcode' => 51, 'errmsg' => '多文件上传，客户端上传表单控件名需设置为数组类型', 'list' => []];
        }

        //存储目录(如果不存在自动创建)
        $saveDir = rtrim($saveDir, '/');
        if (!file_exists($saveDir)) {
            $res = mkdir($saveDir, 0777, true);
            if (!$res) {
                return ['errcode' => 52, 'errmsg' => '存储目录创建失败(可能没有写权限)', 'list' => []];
            }
        } else {
            if (!is_writable($saveDir)) {
                return ['errcode' => 52, 'errmsg' => '存储目录没有写权限', 'list' => []];
            }
        }

        //允许上传的文件类型
        $uptypes = array(
            'image' => array('image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif', 'image/bmp', 'image/x-png'),
            'flash' => array('application/x-shockwave-flash'),
            'media' => array('video/mp4', 'video/x-ms-wmv', 'video/mpeg', 'video/x-ms-asf', 'video/x-msvideo', 'video/avi', 'application/octet-stream'),
        );

        $resBox = array();
        $count = count($_FILES[$inputName]['name']);
        for ($i = 0; $i < $count; $i++) {
            $upfile_name = $_FILES[$inputName]['name'][$i];         //上传的文件名
            $upfile_type = $_FILES[$inputName]['type'][$i];         //上传的文件类型
            $upfile_tmp_name = $_FILES[$inputName]['tmp_name'][$i]; //文件上传到临时文件夹地址
            $upfile_error = $_FILES[$inputName]['error'][$i];       //上传后的错误信息(0表示成功上传到临时文件夹)
            $upfile_size = $_FILES[$inputName]['size'][$i];         //上传的文件大小(字节)

            //是否存在临时文件
            if (!is_uploaded_file($upfile_tmp_name)) {
                $resBox[] = ['errcode' => 53, 'errmsg' => "{$upfile_name} 临时文件不存在", 'size' => $upfile_size, 'name' => '', 'ext' => '', 'saveName' => $saveName, 'fullPath' => ''];
                continue 1;
            }

            //检查文件大小
            if ($upfile_size > $maxFileSize * 1048576) {
                $resBox[] = ['errcode' => 54, 'errmsg' => "{$upfile_name} 文件太大：" . sprintf('%.2f', ($upfile_size / 1048576)) . 'MB', 'size' => $upfile_size, 'name' => '', 'ext' => '', 'saveName' => $saveName, 'fullPath' => ''];
                continue 1;
            }

            //检查文件类型
            if (!in_array($upfile_type, $uptypes[$filetype])) {
                $resBox[] = ['errcode' => 55, 'errmsg' => "{$upfile_name} 文件类型不符", 'size' => $upfile_size, 'name' => '', 'ext' => '', 'saveName' => $saveName, 'fullPath' => ''];
                continue 1;
            }

            //拼装上传后文件保存路径+文件名
            $pinfo = pathinfo($upfile_name);                                                //上传的文件信息dirname|basename|extension|filename
            $ext = isset($pinfo['extension']) ? '.' . strtolower($pinfo['extension']) : ''; //上传 .扩展名
            if ($isRename == true) {
                //重新按时间命名
                list($usec, $sec) = explode(' ', microtime());
                $name = date('YmdHis') . '-' . substr($usec, 2, 5) . '-' . mt_rand(1000, 9999);
            } else {
                //按源文件命名
                $name = $pinfo['filename'];
            }
            $saveName = $name . $ext;                  //上传后 文件名.扩展名
            $fullPath = $saveDir . '/' . $saveName;    //上传后 文件保存完整路径(含文件名)

            //移动文件
            if (!move_uploaded_file($upfile_tmp_name, $fullPath)) {
                $resBox[] = ['errcode' => 56, 'errmsg' => "{$upfile_name}移动文件出错", 'size' => $upfile_size, 'name' => $name, 'ext' => $ext, 'saveName' => $saveName, 'fullPath' => ''];
                continue 1;
            }

            //成功
            $resBox[] = ['errcode' => 0, 'errmsg' => "{$upfile_name} 上传成功", 'size' => $upfile_size, 'name' => $name, 'ext' => $ext, 'saveName' => $saveName, 'fullPath' => $fullPath];
            continue 1;

        }
        return ['errcode' => 0, 'errmsg' => 'ok', 'list' => $resBox];

    }


    /**
     * 以文件流的方式接收并保存单个文件
     * @param string $saveDir 文件保存目录(如果不存在自动创建)
     * @param string $filepath 客户端的原始文件名(含.扩展名)
     * @param string $binarydata 二进制流数据
     * @param bool $isRename 上传后是否按时间格式重命名文件(默认:true是)，若需要保留原文件名可设置为false；
     * @return array 返回单个上传文件的结果，如下
     * Array
     * (
     *    [errcode] => 0
     *    [errmsg] => ok
     *    [size] => 39261
     *    [name] => 20160601170040-53582-1556
     *    [ext] => .gif
     *    [saveName] => 20160601170040-53582-1556.gif
     *    [fullPath] => /wwwroot/yltest/static/upload/goodsCover/2016-06/01/20160601170040-53582-1556.gif
     * )
     *
     * @author martinzhang
     *
     * 客户端发送模拟示例
     * <?php
     * //待上传文件路径
     * $filepath = '/wwwroot/zhang/AFEA2085382B62A6DF7E52FCC5D4DB0E.png';
     *
     * //读取文件
     * //$fp = fopen($filepath, 'r');
     * //$content = fread($fp, filesize($filepath));
     * //close($fp);
     *
     * //读取文件
     * $content = file_get_contents($filepath);
     *
     * $url = "http://zdfe5123ae429a951fe8a368bc45bbae5.dajidi.zz/index/test/get?filepath=$filepath";
     * $res = ZHttp::curlPost($url, $content);
     * print_r($res);
     * ?>
     *
     * 服务端接收示例
     * <?php
     * $content  = file_get_contents('php://input','r');    //接收数据流
     * $filepath = $_GET['filepath'];                       //文件路径(通过GET方式获取传递来的文件名)
     *
     * //执行上传(保存数据)
     * $res = ZUpFile::UpFileByStream($saveDir, $filepath, $content);
     *
     * ?>
     */
    public static function UpFileByStream($saveDir, $filepath, $binarydata, $isRename = true)
    {
        //存储目录(如果不存在自动创建)
        $saveDir = rtrim($saveDir, '/');
        if (!file_exists($saveDir)) {
            $res = mkdir($saveDir, 0777, true);
            if (!$res) {
                return ['errcode' => 51, 'errmsg' => '存储目录创建失败(可能没有写权限)', 'size' => 0, 'name' => '', 'ext' => '', 'saveName' => '', 'fullPath' => ''];
            }
        } else {
            if (!is_writable($saveDir)) {
                return ['errcode' => 51, 'errmsg' => '存储目录没有写权限', 'size' => 0, 'name' => '', 'ext' => '', 'saveName' => '', 'fullPath' => ''];
            }
        }

        //允许上传的文件类型
        $upExts = array('.jpg', '.jpeg', '.png', '.pjpeg', '.gif', '.bmp', '.png', '.swf', '.mp4', '.wmv', '.mpeg');

        //拼装上传后文件保存路径+文件名
        $pinfo = pathinfo($filepath);                                                    //上传的文件信息dirname|basename|extension|filename
        $ext = isset($pinfo['extension']) ? '.' . strtolower($pinfo['extension']) : '';  //上传 .扩展名
        if (!in_array(strtolower($ext), $upExts)) {
            return ['errcode' => 52, 'errmsg' => '文件扩展名不合法', 'size' => 0, 'name' => '', 'ext' => '', 'saveName' => '', 'fullPath' => ''];
        }

        if ($isRename == true) {
            //重新按时间命名
            list($usec, $sec) = explode(' ', microtime());
            $name = date('YmdHis') . '-' . substr($usec, 2, 5) . '-' . mt_rand(1000, 9999);
        } else {
            //按源文件命名
            $name = $pinfo['filename'];
        }
        $saveName = $name . $ext;                  //上传后 文件名.扩展名
        $fullPath = $saveDir . '/' . $saveName;    //上传后 文件保存完整路径(含文件名)

        //$fp = fopen($fullPath, 'w');
        //$res = fwrite($fp, $binarydata);
        //fclose($fp);
        $res = file_put_contents($fullPath, $binarydata);
        if ($res > 0) {
            //成功
            return ['errcode' => 0, 'errmsg' => 'ok', 'size' => $res, 'name' => $name, 'ext' => $ext, 'saveName' => $saveName, 'fullPath' => $fullPath];
        } else {
            //失败
            return ['errcode' => 53, 'errmsg' => '未写入文件', 'size' => 0, 'name' => $name, 'ext' => $ext, 'saveName' => '', 'fullPath' => ''];
        }
    }


}
