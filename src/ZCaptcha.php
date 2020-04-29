<?php

namespace zphpsoft\tools;

/**
 * PHP开发随手使用工具包 - 验证码
 * User: martinzhang
 * Date: 2016/4/22
 * Time: 11:39
 */
class ZCaptcha
{
    /**
     * 生成随机字串
     * @param int $length 指定生成随机码长度
     * @return string 返回生成的随机码
     */
    public static function createRandText($length = 4)
    {
        $str = "abcdefghijkmnpqrstuvwxyz23456789";
        $randText = "";
        for ($i = 0; $i < $length; $i++) {
            $randNum = mt_rand(0, 31);
            $randText .= $str[$randNum];
        }
        return $randText;
    }

    /**
     * 生成随机字串图片(普通小图)
     * @param string $randText 随机字串
     * @param int $width 图片宽度
     * @param int $height 图片长度
     * @retun binary
     */
    public static function createRandImage1($randText, $width, $height)
    {
        //创建图片，定义颜色值
        $im = imagecreate($width, $height);
        $black = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 200), mt_rand(0, 200));
        $gray = imagecolorallocate($im, 118, 151, 199);
        $bgcolor = imagecolorallocate($im, 235, 236, 237);

        //画背景
        imagefilledrectangle($im, 0, 0, $width, $height, $bgcolor);

        //画边框
        imagerectangle($im, 0, 0, $width - 1, $height - 1, $gray);

        //在画布上随机生成大量点，起干扰作用;
        for ($i = 0; $i < 90; $i++) {
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $black);
        }

        //划干扰线条
        for ($i = 0; $i <= 3; $i++) {
            $line_x1 = mt_rand(0, $width * 0.6);         //起始座标x
            $line_y1 = mt_rand(0, $height * 0.6);        //起始座标y
            $line_x2 = mt_rand($width * 0.2, $width);    //终点座标x
            $line_y2 = mt_rand($height * 0.2, $height);  //终点座标y
            $line_color = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 200), mt_rand(0, 200));
            imageline($im, $line_x1, $line_y1, $line_x2, $line_y2, $line_color);
        }

        //将字符随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
        $strx = mt_rand(1, 6);
        for ($i = 0; $i < strlen($randText); $i++) {
            $strpos = mt_rand(0, 7);
            imagestring($im, 6, $strx, $strpos, $randText[$i], $black);
            $strx += mt_rand(8, 15);
        }

        //设置文件头
        Header("Content-type: image/png");
        imagepng($im);
        imagedestroy($im);
    }

    /**
     * 生成随机字串图片(仿Google)
     * @param string $randText 随机字串
     * @param string $ttf TTF字体文件路径(本机绝对路径)
     * @retun binary
     */
    public static function createRandImage2($randText, $ttf)
    {
        $im_x = 160;    //图片长
        $im_y = 40;     //图片高
        $im = imagecreatetruecolor($im_x, $im_y);
        $text_c = ImageColorAllocate($im, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
        $tmpC0 = mt_rand(100, 255);
        $tmpC1 = mt_rand(100, 255);
        $tmpC2 = mt_rand(100, 255);
        $buttum_c = ImageColorAllocate($im, $tmpC0, $tmpC1, $tmpC2);

        imagefill($im, 16, 13, $buttum_c);

        for ($i = 0; $i < strlen($randText); $i++) {
            $tmp = substr($randText, $i, 1);
            $array = array(-1, 1);
            $p = array_rand($array);
            $an = $array[$p] * mt_rand(1, 10);    //角度
            $size = 28;
            imagettftext($im, $size, $an, 15 + $i * $size, 35, $text_c, $ttf, $tmp);
        }

        $distortion_im = imagecreatetruecolor($im_x, $im_y);

        imagefill($distortion_im, 16, 13, $buttum_c);
        for ($i = 0; $i < $im_x; $i++) {
            for ($j = 0; $j < $im_y; $j++) {
                $rgb = imagecolorat($im, $i, $j);
                if ((int)($i + 20 + sin($j / $im_y * 2 * M_PI) * 10) <= imagesx($distortion_im) && (int)($i + 20 + sin($j / $im_y * 2 * M_PI) * 10) >= 0) {
                    imagesetpixel($distortion_im, (int)($i + 10 + sin($j / $im_y * 2 * M_PI - M_PI * 0.1) * 4), $j, $rgb);
                }
            }
        }

        //加入干扰象素;
        $count = 160;    //干扰像素的数量
        for ($i = 0; $i < $count; $i++) {
            $randcolor = ImageColorallocate($distortion_im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($distortion_im, mt_rand() % $im_x, mt_rand() % $im_y, $randcolor);
        }

        $rand = mt_rand(5, 30);
        $rand1 = mt_rand(15, 25);
        $rand2 = mt_rand(5, 10);
        for ($yy = $rand; $yy <= +$rand + 2; $yy++) {
            for ($px = -80; $px <= 80; $px = $px + 0.1) {
                $x = $px / $rand1;
                if ($x != 0) {
                    $y = sin($x);
                }
                $py = $y * $rand2;

                imagesetpixel($distortion_im, $px + 80, $py + $yy, $text_c);
            }
        }

        //设置文件头
        Header("Content-type: image/jpeg");

        //以PNG格式将图像输出到浏览器或文件;
        ImagePNG($distortion_im);

        //销毁一图像,释放与image关联的内存;
        ImageDestroy($distortion_im);
        ImageDestroy($im);
    }

}
