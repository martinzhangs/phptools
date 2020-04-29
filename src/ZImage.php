<?php

namespace zphpsoft\tools;

/**
 * PHP开发随手使用工具包 - 图片篇
 * User: martinzhang
 * Date: 2016/4/22
 * Time: 11:39
 */
class ZImage
{
    /**
     * 等比缩放
     * @param string $srcImg 必选  源图片(完整路径，支持gif,jpg,png格式图片)
     * @param string $newPath 必选  缩放后图片存储目录(如果不存在自动创建)
     * @param string $newFileName 必选  缩放后图片名(含不含".扩展名"均可)
     * @param int $maxWidth 必选  缩放后图片宽(单位:像素)
     * @param int $maxHeight 必选  缩放后图片高(单位:像素)
     * @param int $percent 可选  缩放百分比，取值范围:[0.01~1] (注：如果此参数也指定，将与指定缩放宽高比较取"较小值"的缩放比)
     * @param int $bakSrc 可选  是否备份源文件，y:备份；n:否则为不备份；如原文件名:abc.jpg 则备份文件名为:abc~.jpg
     * @param string $padding 可选  是否填充白色：    y:填充；n:否则为不填充；
     * @return bool               返回缩放结果，结构如下：
     * Array
     * (
     *     [errcode] => 0
     *     [errmsg] => ok
     *     [data] => Array
     *             (
     *                [saveName] => abc.jpg ---------------------------> 存储文件名
     *                [fullPath] => /wwwroot/upload/banner/abc.jpg ----> 存储文件全路径
     *             )
     * )
     *
     * 用法举例：
     *
     * //存储路径
     * $uploadDir    = rtrim(\Yii::$app->getBasePath(),'/').'/'.\Yii::$app->params['uploadPath'];   //upload路径
     * $relativePath = \Yii::$app->params['imgpath'][$action];                                      //相对路径
     * $saveDir      = $uploadDir.$relativePath;                                                    //完整路径
     *
     * //执行缩放
     * $res = ZImage::resizeImgEq($srcImg, $saveDir, $newFileName, $maxWidth, $maxHeight);
     *
     * @author martinzhang
     */
    public static function resizeImgEq($srcImg, $saveDir, $newFileName, $maxWidth, $maxHeight, $percent = 1, $bakSrc = 'n', $padding = 'n')
    {
        if (!file_exists($srcImg)) {
            return ['errcode' => 51, 'errmsg' => '源文件不存在', 'data' => []];
        }

        //存储目录(如果不存在自动创建)
        $saveDir = rtrim($saveDir, '/');
        if (!file_exists($saveDir)) {
            $res = mkdir($saveDir, 0777, true);
            if (!$res) {
                return ['errcode' => 52, 'errmsg' => '存储目录创建失败(可能没有写权限)', 'data' => []];
            }
        }

        //源图文件基本信息参数
        $_pathinfo = pathinfo($srcImg);
        if (strtolower($bakSrc) == 'y') {
            //如果需要备份源图文件
            $newBaseName = str_replace('.' . $_pathinfo["extension"], '~.' . $_pathinfo["extension"], $_pathinfo["basename"]);
            copy($srcImg, $_pathinfo["dirname"] . '/' . $newBaseName);
        }
        list($src_width, $src_height, $src_type) = getimagesize($srcImg);    //获取源图文件大小、类型
        switch ($src_type) {
            case 1:
                //gif
                $src_img = imagecreatefromgif($srcImg);
                break;
            case 2:
                //jpg
                $src_img = imagecreatefromjpeg($srcImg);
                break;
            case 3:
                //png
                $src_img = imagecreatefrompng($srcImg);
                break;

            default:
                return ['errcode' => 53, 'errmsg' => '当前源图片格式不被支持', 'data' => []];
        }

        $scale = min($maxWidth / $src_width, $maxHeight / $src_height); //求较小的缩放比例
        if (!empty($percent)) {
            $scale = min($percent, $scale);                            //与指定的百分比求较小的绽放比例
        }
        $scale = min($scale, 1);
        $dst_width = floor($src_width * $scale);                    //新图片宽(目标图)
        $dst_height = floor($src_height * $scale);                    //新图片高(目标图)
        if (strtolower($padding) == 'y') {
            //如果需要填充白色，下面准备画一个正方形，计算边长
            if ($dst_width < $dst_height) {
                $dst_img_length = $dst_height;
                $dst_x = ($dst_img_length - $dst_width) / 2;
                $dst_y = 0;
            } else {
                $dst_img_length = $dst_width;
                $dst_x = 0;
                $dst_y = ($dst_img_length - $dst_height) / 2;
            }
            $dst_img = imagecreatetruecolor($dst_img_length, $dst_img_length);                                              //新图资源(目标图)
            $white = imagecolorallocate($dst_img, 255, 255, 255);                                                           //创建颜色-“白色”
            imagefilledrectangle($dst_img, 0, 0, $dst_img_length, $dst_img_length, $white);                                 //用白色矩形框填满整个画板
            imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, 0, 0, $dst_width, $dst_height, $src_width, $src_height); //执行拷贝
        } else {
            //不需要填充
            $dst_img = imagecreatetruecolor($dst_width, $dst_height);                                                       //新图资源(目标图)
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);           //执行拷贝
        }
        imagedestroy($src_img);
        //////////////////////////////////////////////////////////////////////////////////
        $newFileName = preg_replace(['/\.jpg$/i', '/\.jpeg$/i', '/\.gif$/', '/\.png$/i'], '', $newFileName);
        switch ($src_type) {
            case 1:
                //gif
                $newFileName = $newFileName . '.' . strtolower($_pathinfo["extension"]);
                $fullPath = $saveDir . '/' . $newFileName;
                $save = imagegif($dst_img, $fullPath);
                break;
            case 2:
                //jpg
                $newFileName = $newFileName . '.' . strtolower($_pathinfo["extension"]);
                $fullPath = $saveDir . '/' . $newFileName;
                $save = imagejpeg($dst_img, $fullPath, 100);
                break;
            case 3:
                //png
                $newFileName = $newFileName . '.' . strtolower($_pathinfo["extension"]);
                $fullPath = $saveDir . '/' . $newFileName;
                $save = imagepng($dst_img, $fullPath);
                break;
        }

        imagedestroy($dst_img);
        if ($save) {
            return ['errcode' => 0, 'errmsg' => 'ok', 'data' => ['saveName' => $newFileName, 'fullPath' => $fullPath]];
        } else {
            return ['errcode' => 54, 'errmsg' => $save, 'data' => ['saveName' => '', 'fullPath' => '']];
        }
    }


    /**
     * 非等比缩放
     * @param string $srcImg 必选  源图片(完整路径，支持gif,jpg,png格式图片)
     * @param string $saveDir 必选  缩放后图片存储目录(如果不存在自动创建)
     * @param string $newFileName 必选  缩放后图片名(含不含".扩展名"均可)
     * @param int $maxWidth 必选  缩放后图片宽(单位:像素)
     * @param int $maxHeight 必选  缩放后图片高(单位:像素)
     * @return bool               返回缩放结果，结构如下：
     * Array
     * (
     *     [errcode] => 0
     *     [errmsg] => ok
     *     [data] => Array
     *             (
     *                [saveName] => ok.jpg ---------------------------> 存储文件名
     *                [fullPath] => /wwwroot/upload/banner/ok.jpg ----> 存储文件全路径
     *             )
     * )
     *
     * 用法举例：
     *
     * //存储路径
     * $uploadDir    = rtrim(\Yii::$app->getBasePath(),'/').'/'.\Yii::$app->params['uploadPath'];    //upload路径
     * $relativePath = \Yii::$app->params['imgpath'][$action];                                       //相对路径
     * $saveDir      = $uploadDir.$relativePath;                                                     //完整路径
     *
     * //执行缩放
     * $res = ZImage::resizeImgNEq($srcImg, $saveDir, $newFileName, $maxWidth, $maxHeight);
     *
     * @author martinzhang
     */
    public static function resizeImgNEq($srcImg, $saveDir, $newFileName, $maxWidth, $maxHeight)
    {
        if (!file_exists($srcImg)) {
            return ['errcode' => 51, 'errmsg' => '源文件不存在', 'data' => []];
        }

        //存储目录(如果不存在自动创建)
        $saveDir = rtrim($saveDir, '/');
        if (!file_exists($saveDir)) {
            $res = mkdir($saveDir, 0777, true);
            if (!$res) {
                return ['errcode' => 52, 'errmsg' => '存储目录创建失败(可能没有写权限)', 'data' => []];
            }
        }

        //源图文件基本信息参数
        $_pathinfo = pathinfo($srcImg);
        list($src_width, $src_height, $src_type) = getimagesize($srcImg);    //获取源图文件大小、类型
        switch ($src_type) {
            case 1:
                //gif
                $src_img = imagecreatefromgif($srcImg);
                break;
            case 2:
                //jpg
                $src_img = imagecreatefromjpeg($srcImg);
                break;
            case 3:
                //png
                $src_img = imagecreatefrompng($srcImg);
                break;

            default:
                return ['errcode' => 53, 'errmsg' => '当前源图片格式不被支持', 'data' => []];
        }

        $dst_width = min($maxWidth, $src_width);                                                                //新图片宽(目标图)
        $dst_height = min($maxHeight, $src_height);                                                             //新图片高(目标图)

        //不需要填充
        $dst_img = imagecreatetruecolor($dst_width, $dst_height);                                               //新图资源(目标图)
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);   //执行拷贝

        imagedestroy($src_img);
        //////////////////////////////////////////////////////////////////////////////////
        $newFileName = preg_replace(['/\.jpg$/i', '/\.jpeg$/i', '/\.gif$/', '/\.png$/i'], '', $newFileName);
        switch ($src_type) {
            case 1:
                //gif
                $newFileName = $newFileName . '.' . strtolower($_pathinfo["extension"]);
                $fullPath = $saveDir . '/' . $newFileName;
                $save = imagegif($dst_img, $fullPath);
                break;
            case 2:
                //jpg
                $newFileName = $newFileName . '.' . strtolower($_pathinfo["extension"]);
                $fullPath = $saveDir . '/' . $newFileName;
                $save = imagejpeg($dst_img, $fullPath, 100);
                break;
            case 3:
                //png
                $newFileName = $newFileName . '.' . strtolower($_pathinfo["extension"]);
                $fullPath = $saveDir . '/' . $newFileName;
                $save = imagepng($dst_img, $fullPath);
                break;
        }

        imagedestroy($dst_img);
        if ($save) {
            return ['errcode' => 0, 'errmsg' => 'ok', 'data' => ['saveName' => $newFileName, 'fullPath' => $fullPath]];
        } else {
            return ['errcode' => 54, 'errmsg' => $save, 'data' => ['saveName' => '', 'fullPath' => '']];
        }
    }


    /**
     * 图片裁切
     * @param string $srcImg 源图片(全路径，支持gif,jpg,png格式图片)
     * @param string $saveDir 裁切后图片存储目录(如果不存在自动创建)
     * @param string $newFileName 裁切后图片名(不含.扩展名)
     * @param string $x 源图起始位置x
     * @param string $y 源图起始位置y
     * @param string $w 裁切出的宽度w(单位:像素)
     * @param string $h 裁切出的高度h(单位:像素)
     * @return bool               返回裁切结果，结构如下：
     * Array
     * (
     *     [errcode] => 0
     *     [errmsg] => ok
     *     [data] => Array
     *             (
     *                [saveName] => mycut.jpg ------------------------> 存储文件名
     *                [fullPath] => /wwwroot/mycut.jpg ---------------> 存储文件全路径
     *             )
     * )
     * @author martinzhang
     */
    public static function cutImg($srcImg, $saveDir, $newFileName, $x, $y, $w, $h)
    {
        if (!file_exists($srcImg)) {
            return ['errcode' => 51, 'errmsg' => '源文件不存在', 'data' => []];
        }

        //存储目录(如果不存在自动创建)
        $saveDir = rtrim($saveDir, '/');
        if (!file_exists($saveDir)) {
            $res = mkdir($saveDir, 0777, true);
            if (!$res) {
                return ['errcode' => 52, 'errmsg' => '存储目录创建失败(可能没有写权限)', 'data' => []];
            }
        }

        list($src_width, $src_height, $src_type) = getimagesize($srcImg);            //获取源图文件大小、类型
        switch ($src_type) {
            case 1:
                //gif
                $src_img = imagecreatefromgif($srcImg);
                break;
            case 2:
                //jpg
                $src_img = imagecreatefromjpeg($srcImg);
                break;
            case 3:
                //png
                $src_img = imagecreatefrompng($srcImg);
                break;

            default:
                return ['errcode' => 53, 'errmsg' => '当前源图片格式不被支持', 'data' => []];
        }
        $w = min($w, $src_width - $x);
        $h = min($h, $src_height - $y);
        $dst_img = imagecreatetruecolor($w, $h);                                //新图资源(目标图)
        imagecopyresampled($dst_img, $src_img, 0, 0, $x, $y, $w, $h, $w, $h);    //执行拷贝
        imagedestroy($src_img);
        ////////////////////////////////////////////////////////////////////////////////////
        switch ($src_type) {
            case 1:
                //gif
                $newFileName = $newFileName . '.gif';
                $fullPath = $saveDir . '/' . $newFileName;
                $save = imagegif($dst_img, $fullPath);
                break;
            case 2:
                //jpg
                $newFileName = $newFileName . '.jpg';
                $fullPath = $saveDir . '/' . $newFileName;
                $save = imagejpeg($dst_img, $fullPath, 100);
                break;
            case 3:
                //png
                $newFileName = $newFileName . '.png';
                $fullPath = $saveDir . '/' . $newFileName;
                $save = imagepng($dst_img, $fullPath);
                break;

        }

        imagedestroy($dst_img);
        if ($save) {
            return ['errcode' => 0, 'errmsg' => 'ok', 'data' => ['saveName' => $newFileName, 'fullPath' => $fullPath]];
        } else {
            return ['errcode' => 54, 'errmsg' => $save, 'data' => ['saveName' => '', 'fullPath' => '']];
        }

    }


}
