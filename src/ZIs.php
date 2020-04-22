<?php

namespace zphpsoft\tools;


/**
 * PHP开发随手使用工具包 - 判断篇
 * User: martinzhang
 * Date: 2016/4/22
 * Time: 11:39
 */
class ZIs
{

    /**
     * 判断给定字符串是否为手机号码
     * @param  string $mobile 待判断的字符串
     * @return bool           是手机号码返回true; 不是手机号码返回false;
     * @author martinzhang
     */
    public static function isMobile($mobile)
    {
        if (!is_numeric($mobile) || strlen($mobile) != 11) {
            return false;
        }
        if (preg_match('/^1[2-9]\d{9}$/', $mobile)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断给定字符串是否为邮箱地址
     * @param  string $email 待判断的字符串
     * @return bool          是邮箱返回true; 不是邮箱返回false;
     * @author martinzhang
     */
    public static function isEmail($email)
    {
        if (strpos($email, '@') < 1) {
            return false;
        }
        if (preg_match('/^\w+([-.]+\w+)?@\w+([-.]+\w+)?\.\w+(\.\w+)?/', $email)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断给定字符串是否为http(s)地址
     * @param  string $url 待判断的字符串
     * @return bool          是http(s)返回true; 不是返回false;
     * @author martinzhang
     */
    public static function isHttp($url)
    {
        if (strpos(strtolower(trim($url)), 'http') !== 0) {
            return false;
        }
        if (preg_match('/^https?:\/\/.+/i', $url)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断给定字符串是否为月份(1970-01或1970/01)
     * @param  string $date 待判断的字符串
     * @return bool         是月份返回true; 不是月份返回false;
     * @author martinzhang
     */
    public static function isMonth($date)
    {
        if (preg_match('/^\d{4}(-|\/)(0?[0-9]|1[012])$/', $date)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断给定字符串是否为日期(1970-01-01或1970/01/01)
     * @param  string $date 待判断的字符串
     * @return bool         是日期返回true; 不是日期返回false;
     * @author martinzhang
     */
    public static function isDate($date)
    {
        if (preg_match('/^\d{4}(-|\/)(0?[0-9]|1[012])\1(0?[0-9]|[12][0-9]|3[01])$/', $date)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断给定字符串是否为时间(12:30:33 或12:30)
     * @param string $time 待判断的字符串
     * @return bool 是时间返回true; 不是时间返回false;
     * @author martinzhang
     */
    public static function isTime($time)
    {
        if (preg_match('/^(0?[0-9]|[1][0-9]|2[0-3]):(0?[0-9]|[1-5][0-9])(:(0?[0-9]|[1-5][0-9]))?$/', $time)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断给定字符串是否为“日期+时间”(1970-01-01 12:30:33  或 1970-01-01 12:30  或 1970/01/01 12:30:33)
     * @param  string $datetime 待判断的字符串
     * @return bool             是日期+时间返回true; 不是日期+时间返回false;
     * @author martinzhang
     */
    public static function isDateTime($datetime)
    {
        if (preg_match('/^\d{4}(-|\/)(0?[0-9]|1[012])\1(0?[0-9]|[12][0-9]|3[01])\s+(0?[0-9]|[1][0-9]|2[0-3]):(0?[0-9]|[1-5][0-9])(:(0?[0-9]|[1-5][0-9]))?$/', $datetime)) {
            return true;
        } else {
            return false;
        }
    }

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
     * 判断给定字符串是否含有中文字符
     * @param  string $str 待判断的字符串
     * @return bool        含中文字符返回true; 不含中文字符返回false;
     * @author martinzhang
     */
    public static function isCN($str)
    {
        if (preg_match("/[\x{4e00}-\x{9fa5}]/u", $str)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断是否为图片文件
     * @param  string $filepath 待验证的文件路径
     * @return boolean            是图片返回true；不是图片返回false；
     * @author martinzhang
     */
    public static function isImage($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        $type = finfo_file(finfo_open(FILEINFO_MIME), $filepath);
        if ($type) {
            list($mime,) = explode(';', $type);
            if (in_array($mime, ['image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif', 'image/bmp', 'image/x-png', 'image/x-ms-bmp'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 判断是否为图片文件(按文件名方式验证)
     * @param  string $filepath 待验证的文件路径
     * @return boolean            是图片返回true；不是图片返回false；
     * @author martinzhang
     */
    public static function isImageByfilename($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        if (function_exists('exif_imagetype')) {
            $imgInfo = exif_imagetype($filepath);
            if (in_array($imgInfo, [1, 2, 3, 6])) {
                return true;
            } else {
                return false;
            }
        } elseif (function_exists('getimagesize')) {
            $imgInfo = getimagesize($filepath);
            if (in_array($imgInfo[2], [1, 2, 3, 6])) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * 判断是否为JPG图片文件
     * @param  string $filepath 待验证的文件路径
     * @return boolean            是JPG图片返回true；不是JPG图片返回false；
     * @author martinzhang
     */
    public static function isJPG($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        $type = finfo_file(finfo_open(FILEINFO_MIME), $filepath);
        if ($type) {
            list($mime,) = explode(';', $type);
            if (in_array($mime, ['image/jpg', 'image/jpeg'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 判断是否为PNG图片文件
     * @param  string $filepath 待验证的文件路径
     * @return boolean            是PNG图片返回true；不是PNG图片返回false；
     * @author martinzhang
     */
    public static function isPNG($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        $type = finfo_file(finfo_open(FILEINFO_MIME), $filepath);
        if ($type) {
            list($mime,) = explode(';', $type);
            if (in_array($mime, ['image/png'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 判断是否为GIF图片文件
     * @param  string $filepath 待验证的文件路径
     * @return boolean            是GIF图片返回true；不是GIF图片返回false；
     * @author martinzhang
     */
    public static function isGIF($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        $type = finfo_file(finfo_open(FILEINFO_MIME), $filepath);
        if ($type) {
            list($mime,) = explode(';', $type);
            if (in_array($mime, ['image/gif'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 判断是否为语音文件
     * @param  string $filepath 待验证的文件路径
     * @return boolean            是语音返回true；不是语音返回false；
     * @author martinzhang
     */
    public static function isVoice($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        $type = finfo_file(finfo_open(FILEINFO_MIME), $filepath);
        if ($type) {
            list($mime,) = explode(';', $type);
            if (in_array($mime, ['audio/amr', 'audio/mpeg', 'audio/x-mpeg'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 判断是否为视频文件
     * @param  string $filepath 待验证的文件路径
     * @return boolean            是视频返回true；不是视频返回false；
     * @author martinzhang
     */
    public static function isVideo($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        $type = finfo_file(finfo_open(FILEINFO_MIME), $filepath);
        if ($type) {
            list($mime,) = explode(';', $type);
            if (in_array($mime, ['video/mp4', 'video/x-ms-wmv', 'video/mpeg', 'video/x-ms-asf', 'video/x-msvideo', 'video/avi', 'application/octet-stream'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 判断是否为.zip压缩文件
     * @param  string $filepath 待验证的文件路径
     * @return boolean            是zip压缩文件返回true；不是zip压缩文件返回false；
     * @author martinzhang
     */
    public static function isZip($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        $type = finfo_file(finfo_open(FILEINFO_MIME), $filepath);
        if ($type) {
            list($mime,) = explode(';', $type);
            if (in_array($mime, ['application/zip'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 判断是否为.rar压缩文件
     * @param  string $filepath 待验证的文件路径
     * @return boolean            是rar压缩文件返回true；不是rar压缩文件返回false；
     * @author martinzhang
     */
    public static function isRar($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        $type = finfo_file(finfo_open(FILEINFO_MIME), $filepath);
        if ($type) {
            list($mime,) = explode(';', $type);
            if (in_array($mime, ['application/x-rar'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 判断是否为线上(生产)服务器
     * @return bool              线上服务器返回true; 非线上服务器返回false;
     * @author martinzhang
     */
    public static function isOnLineServer()
    {
        /**
         * 注，需在线上服务器 /usr/local/php/etc/php.ini 文件末尾添加变量声明，格式如下：
         *
         * [zzx_defined]
         * ;声明当前服务器为线上生产服务器
         * is_production = 1
         */
        if (get_cfg_var('is_production') == '1') {
            return true;
        } else {
            return false;
        }
    }


}
