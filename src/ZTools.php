<?php

namespace zphpsoft\tools;

/**
 * PHP开发随手使用工具包 - 常用篇
 * User: martinzhang
 * Date: 2016/4/22
 * Time: 11:39
 */
class ZTools
{
    /**
     * 调试、打印输出变量
     * @param mixed $res
     */
    public static function p($res)
    {
        if (is_string($res)) {
            echo $res . '<br />';
        } elseif (is_array($res)) {
            echo '<pre>';
            print_r($res);
            echo '</pre>';
        } else {
            echo '<pre>';
            var_dump($res);
            echo '</pre>';
        }
    }

    /**
     * 将字符或数组过滤掉空值，并去重，格式化为数组
     * @param array|stirng $var 必选  待格式化的字符串或数组
     * @param string $delimiter 可选  如果$var是字符串,指定使用哪种分隔符分隔(默认按逗号分隔)
     * @uses                     示例  $var='1,2,,3,3,,,4,,'; ---> [1,2,3,4]
     * @return array             返回格式化后的数组
     * @author martinzhang
     */
    public static function fmtArrayFilter($var, $delimiter = ',')
    {
        if (is_string($var) || is_numeric($var)) {
            $tmpArr = explode($delimiter, $var);
            $tmpArr = array_map(function($v) {
                return trim($v);    //去除空格
            }, $tmpArr);
            return array_unique(array_filter($tmpArr, function($v) {
                if (trim($v) || is_numeric($v)) {
                    return true;
                } else {
                    return false;
                }
            }));

        } elseif (is_array($var)) {
            return array_unique(array_filter($var, function($v) {
                if (trim($v) || is_numeric($v)) {
                    return true;
                } else {
                    return false;
                }
            }));

        } else {
            return [];
        }
    }

    /**
     * 移除一维数组内的"空字符串"和"null值"(不过滤0)
     * @param  array $arr 必选 待过滤的数组
     * @return array           过滤好的数组
     * @author martinzhang
     */
    public static function rmArrayEmpty($arr)
    {
        if (!is_array($arr)) {
            return [];
        }

        foreach ($arr as $k => $v) {
            if (trim($v) === '') {
                unset($arr[$k]);    //移除
            }
        }
        return $arr;
    }

    /**
     * 将多维数组中所有"null值"替换为"空字符串值"
     * @param  array $arr 待处理的数组
     * @return array       返回替换后的新数组
     * @author martinzhang
     */
    public static function replaceNull2Empty($arr)
    {
        foreach ($arr as &$val) {
            if ($val == null) {
                $val = '';
            } elseif (is_string($val)) {
                $val = trim($val);
            }
            if (is_array($val) && !empty($val)) {
                $val = self::replaceNull2Empty($val);
            }
        }
        return $arr;
    }

    /**
     * 将字符或数组过滤掉空值去重后，再将元素值转换为"字符类型"或"整型"
     * @param  array|string $var 必须 待格式化的字符串或数组
     * @param  string $action 可选 要将为数组元素值类型，可取值: string(默认)|int
     * @return array                  返回格式化后的数组
     * @author martinzhang
     */
    public static function convArray2StringOrInteger($var, $action = 'str')
    {
        $var = self::fmtArrayFilter($var);
        $returnArr = [];
        if (empty($var)) {
            return [];
        }
        if ($action == 'str' || $action == 'string') {
            foreach ($var as $val) {
                $returnArr[] = (string)$val;
            }
        } elseif ($action == 'int' || $action == 'integer') {
            foreach ($var as $val) {
                $returnArr[] = (integer)$val;
            }
        } else {
            $returnArr = $var;
        }
        unset($var);
        return $returnArr;
    }

    /**
     * 按指定中文字数长度截取中英文混合字符串
     * @param  string     必须 待截取的字符串
     * @param  cnNum      必须 需要截取相当于cnNum个汉字个数的长度
     * @param  start      可选 起始截取位置(默认0，即从开头开始截取)
     * @param  ifEllipsis 可选 返回的字符串末尾是否用 .. 点点连接：true是(默认)；false否；
     * @param  charset    可选 字符编码：支持 utf8(默认) 和 gbk
     * @return            返回截取后的字符串
     * @author martinzhang
     */
    public static function mbSubstrMixStr($string, $cnNum, $start = 0, $ifEllipsis = true, $charset = 'utf8')
    {
        $start = $start == '' ? 0 : $start;
        $ifEllipsis = $ifEllipsis == true ? true : false;
        $charset = $charset == '' ? 'utf8' : $charset;
        $code2Len = array('gbk' => 2, 'utf8' => 3);           //编码对应每汉字长度(单位：字节)
        $lenString = strlen($string);                         //字符串总长度(单位：字节) 注：原来用 mb_strlen($string)
        $LenSplit = $cnNum * $code2Len[strtolower($charset)]; //需求截取长度(单位：字节)
        $spaceLen = $cnNum * 2;                               //需求占位(注：每个英文字符为1个占位，每个汉字为2个占位)
        if ($lenString <= $LenSplit) {
            return $string;
        } else {
            for ($i = -1; $i <= $spaceLen; $i++) {
                $len = $cnNum + $i;                              //本轮需要截取字符个数
                $str = mb_substr($string, $start, $len, $charset);
                if (strtolower($charset) == 'utf8') {
                    //如果是utf8编码
                    @preg_match_all("/([\x{4e00}-\x{9fa5}｛｝￥¨±·×÷ˇˉ‐—―‖‘’“”…ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ∈∏∑√∝∞∠∥∧∨∩∪∫∮∴∵∶∷∽≈≌≠≡≤≥≮≯⊙⊥⌒①②③④⑤⑥⑦⑧⑨⑩⑪⑫⑬⑭⑮⑯⑰⑱⑲⑳⑴⑵⑶⑷⑸⑹⑺⑻⑼⑽⑾⑿⒀⒁⒂⒃⒄⒅⒆⒇⒈⒉⒊⒋⒌⒍⒎⒏⒐⒑⒒⒓⒔⒕⒖⒗⒘⒙⒚⒛❶❷❸❹❺❻❼❽❾❿、。〃々〈〉《》「」『』【】〔〕〖〗〝〞㈠㈡㈢㈣㈤㈥㈦㈧㈨㈩︰︴︵︶︷︸︹︺︻︼︽︾︿﹀﹁﹂﹃﹄﹉﹊﹋﹌﹍﹎﹏﹑﹔﹕﹖﹙﹚﹛﹜﹟﹠﹡﹢﹣﹤﹦﹨﹩﹪﹫！＂＇（）＋，－／：；＜＝＞？［］＿｀｜～￣§×ΘΨ‖‥…№℡←↑→↓↖↗↘↙√∮⊕⊙⊿╱╲▁▂▃▄▅▆▇█▏▔▕■□▲△▼▽◆◇○◎●◢◣◤◥☀☂☃★☆☉☋☍☎☏☑☒☜☞☠☪☭☽☾♀♂♝♞♪♭✁✈✌✍✎✐✔✖✘✙✚✪✲❀❂❈❉❤❦❧〄〠㈜㈱㉿㊙㊚㊛㊣㊤㊥㊦㊧㊨囍]){1}/u", $str, $arrCh);
                    $currentCNs = count($arrCh[0]);              //本轮截取到的中文字符个数
                } else {
                    //如果是gbk编码
                    @preg_match_all("/([\x80-\xff｛｝￥¨±·×÷ˇˉ‐—―‖‘’“”…ⅠⅡⅢⅣⅤⅥⅦⅧⅨⅩ∈∏∑√∝∞∠∥∧∨∩∪∫∮∴∵∶∷∽≈≌≠≡≤≥≮≯⊙⊥⌒①②③④⑤⑥⑦⑧⑨⑩⑪⑫⑬⑭⑮⑯⑰⑱⑲⑳⑴⑵⑶⑷⑸⑹⑺⑻⑼⑽⑾⑿⒀⒁⒂⒃⒄⒅⒆⒇⒈⒉⒊⒋⒌⒍⒎⒏⒐⒑⒒⒓⒔⒕⒖⒗⒘⒙⒚⒛❶❷❸❹❺❻❼❽❾❿、。〃々〈〉《》「」『』【】〔〕〖〗〝〞㈠㈡㈢㈣㈤㈥㈦㈧㈨㈩︰︴︵︶︷︸︹︺︻︼︽︾︿﹀﹁﹂﹃﹄﹉﹊﹋﹌﹍﹎﹏﹑﹔﹕﹖﹙﹚﹛﹜﹟﹠﹡﹢﹣﹤﹦﹨﹩﹪﹫！＂＇（）＋，－／：；＜＝＞？［］＿｀｜～￣§×ΘΨ‖‥…№℡←↑→↓↖↗↘↙√∮⊕⊙⊿╱╲▁▂▃▄▅▆▇█▏▔▕■□▲△▼▽◆◇○◎●◢◣◤◥☀☂☃★☆☉☋☍☎☏☑☒☜☞☠☪☭☽☾♀♂♝♞♪♭✁✈✌✍✎✐✔✖✘✙✚✪✲❀❂❈❉❤❦❧〄〠㈜㈱㉿㊙㊚㊛㊣㊤㊥㊦㊧㊨囍]){1}/", $str, $arrCh);
                    $currentCNs = floor(count($arrCh[0]) / 2);   //本轮截取到的中文字符个数(注：gbk有别于utf8)
                }
                $chrSpace = $len - $currentCNs;                  //本轮截取包含非中文字符个数(非中文字符占位数)
                $currentSpaces = $currentCNs * 2 + $chrSpace;    //本轮截取后得到总占位数
                $diffSpace = $spaceLen - $currentSpaces;         //仍缺少的占位数
                if ($ifEllipsis == true && $diffSpace <= 3) {
                    $dot4space = array(1 => '', 2 => '..', 3 => '...');
                    $str .= $dot4space[$diffSpace];              //连接 点点点
                    if ($diffSpace <= 3) {
                        break 1;
                    }
                } else {
                    if ($diffSpace <= 1) {
                        break 1;
                    }
                }
            }
            return $str;
        }
    }

    /**
     * 将手机号中间4位用星号替换
     * @param  int $mobile 手机号
     * @return string      返回替换后的手机号
     * @author martinzhang
     */
    public static function fmtMobile2Star($mobile)
    {
        return substr_replace($mobile, '****', 3, 4);
    }

    /**
     * 生成带前导0的编号
     * @param int $num 编号值
     * @param int $strlen 生成的编号部长度
     * @return string     返回生成的带前导0的编号(形如:00000001 00000026)
     * @author martinzhang
     */
    public static function createBianHao($num, $strlen = 8)
    {
        return sprintf("%'0{$strlen}s", $num);
    }

    /**
     * 密码加密存放规则
     * @param  string $password 待加密的明文密码
     * @return string            返回加密后的sha1密文(40位)
     * @author martinzhang
     */
    public static function pwdEncryptRule($password)
    {
        $password = trim($password);
        return sha1(md5($password) . '|' . sha1($password));
    }

    /**
     * 加密/解密
     * @param  string $Str 必须 待加(解)密字符串
     * @param  string $Pkey 必须 密钥
     * @param  string $Action 必须 动作行为 encode:加密  decode:解密
     * @return string         成功返回加密/解密后的字符串；失败返回false；
     * @author martinzhang
     */
    public static function zEnDeCode($Str, $Pkey, $Action)
    {
        if ($Str == '') {
            return 'Str is required';
        } elseif ($Pkey == '') {
            return 'Pkey is required';
        } elseif ($Action != 'encode' && $Action != 'decode') {
            return 'Action is required(encode|decode)';
        }
        try {
            if ($Action == 'encode') {
                //动作行为 encode:加密
                $Str = urlencode($Str);         //执行url编码
                $Str = base64_encode($Str);     //执行base64编码
            }
            /////////////////////////////////////////////////////////////////////////////////////////////
            $Pkey = md5($Pkey . 'length');      //对密钥进行二次加密(目的是将密钥转为得到一串字符串)
            $Pkey = strtoupper($Pkey);          //转为大写
            $StrLength = strlen($Str);          //待加解密字符长度(字节)
            $PkeyLength = strlen($Pkey);        //二次加密后的密钥长度(字节)
            $codedStr = '';                     //初始化返回加解密字符串
            $k = 0;                             //初始化对密钥取字符的索引下标
            for ($i = 0; $i < $StrLength; $i++) {
                //ord() 功能：将字符转为ASCII值。
                //chr() 功能：将待加(解)密中字符串，逐个字符对应的ASCII值与“最终密钥”值进行“位异或”运算，得到一个新的ASCII值
                //算法：
                //1.将待加(解)密中字符串，逐个字符对应的ASCII值与“对应密钥ASCII值”进行“位异或”运算，得到一个新的ASCII值
                //2.然后再转化此新ASCII值为字符，得到一个加密(或解密)字符，串成一个新字符串就是加密(或解密)的结果
                $k = $i % $PkeyLength;                    //取余作为下标值
                $eorVal = ord($Str[$i]) ^ ord($Pkey[$k]); //核心：将待加密字符串与密钥字符串逐个分别做“异或”计算，得到新的ASCII值
                $codedStr .= chr($eorVal);                //将新的ASCII值转为字符，并串起来
            }
            /////////////////////////////////////////////////////////////////////////////////////////////
            if ($Action == 'decode') {
                //动作行为 decode:解密
                $codedStr = base64_decode($codedStr);     //执行base64解码
                $codedStr = urldecode($codedStr);         //执行url解码
            }
            return $codedStr;

        } catch (Exception $e) {
            return false;
        }

    }

    /**
     * 过滤字符串中的emoji表情字符
     * @param  string $text 正常文本
     * @return string       返回过滤后的文本
     * @author martinzhang
     */
    public static function filterEmoji($text)
    {
        $text = preg_replace("/[\xf0-\xf7].{3}/i", "", $text);    //过滤掉emoji表情符
        return $text;
    }

    /**
     * 创建按时间命名的文件名
     * @return string 返回按时间命名的名称，如:20161010205944-84556-2543
     * @author martinzhang
     */
    public static function getTimeName()
    {
        list($usec, $sec) = explode(' ', microtime());
        return date('YmdHis') . '-' . substr($usec, 2, 5) . '-' . mt_rand(1000, 9999);
    }

    /**
     * 将一个文件名(或文件路径)的扩展名变小写
     * @param  string $filePath 文件名(或文件路径)
     * @return string           返回过滤后的文本
     * @author martinzhang
     */
    public static function ext2lower($filePath)
    {
        $pinfo = pathinfo($filePath);
        if (isset($pinfo['extension'])) {
            $ext = '.' . strtolower($pinfo['extension']);
        } else {
            $ext = '';
        }
        return $pinfo['dirname'] . '/' . $pinfo['filename'] . $ext;
    }

    /**
     * 重命名文件名(在一个文件名首部或末尾补加一段字符,起到更名效果)
     * @param  string $filePath 必须 文件路径
     * @param  string $appendStr 必须 待插入的字符串
     * @param  string $site 可选 插入位置 0:文件名首部；1:文件名尾部；
     * @return string            返回新的文件名路径
     * @author martinzhang
     */
    public static function instr2Filepath($filePath, $appendStr, $site = 1)
    {
        $pathInfo = pathinfo($filePath);
        $dirname = str_replace($pathInfo['basename'], '', $filePath);
        if ($site == 0) {
            //拼接到文件开头
            $name = $appendStr . $pathInfo['filename'];
        } else {
            //拼接到文件末尾
            $name = $pathInfo['filename'] . $appendStr;
        }
        $ext = isset($pathInfo['extension']) && !empty($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

        return $dirname . $name . $ext;
    }

    /**
     * 根据指定数值生成短标签(常用于生成短域名)
     * @param  $num  int 必须 指定的数字
     * @return string 失败返回false; 成功返回生成的短标签字符;
     * @author martinzhang
     */
    public static function createShortTag($num)
    {
        if (!is_numeric($num)) {
            return false;
        }

        //64进制
        $codeTable = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ0123456789=_';
        //$codeTable = 'abc';

        $bits = strlen($codeTable);
        $tail = $num % $bits;
        $tail = $codeTable[$tail];
        $preBits = floor($num / $bits) - 1;
        if ($preBits >= 0) {
            $prefixStr = self::createShortTag($preBits);
            $shortTag = $prefixStr . $tail;
        } else {
            $shortTag = $tail;
        }

        return $shortTag;
    }

    /**
     * 记入日志文件
     * @param  string $logfile 必须 待写入的日志文件路径
     * @param  string|array $data 必须 待写入的数据
     * @return int             成功写入文件的字节个数；失败返回负值 -1:目录创建失败  -2:目录无写权限
     * @author martinzhang
     */
    public static function write2LogFile($logfile, $data)
    {
        //取目录路径
        $dirname = pathinfo($logfile, PATHINFO_DIRNAME);

        //创建目录
        if (!file_exists($dirname)) {
            $res = mkdir($dirname, 0777, true);
            if (!$res) {
                return -1;    //目录创建失败
            }
        }

        if (!is_writable($dirname)) {
            return -2;    //目录无写权限
        }

        if (is_array($data) || is_object($data)) {
            $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return file_put_contents($logfile, date('Y-m-d H:i:s') . " " . $data . "\n\n", FILE_APPEND);
    }

    /**
     * 获取远程客户端ip地址
     * @return string 返回客户端ip地址
     * @author martinzhang
     */
    public static function getClientIP()
    {
        $client_ip = '';
        if (isset($_SERVER) && isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $client_ip = getenv('REMOTE_ADDR');
        }

        return $client_ip;
    }

    /**
     * 浏览器访问模式获取服务器IP地址
     * @return string 返回服务器IP地址
     * @author martinzhang
     */
    public static function getServerIP_4Browse()
    {
        $server_ip = '';
        if (isset($_SERVER)) {
            if (isset($_SERVER['SERVER_ADDR'])) {
                $server_ip = $_SERVER['SERVER_ADDR'];
            } elseif (isset($_SERVER['SERVER_NAME'])) {
                $server_ip = $_SERVER['SERVER_NAME'];
            } elseif (isset($_SERVER['HTTP_HOST'])) {
                $server_ip = $_SERVER['HTTP_HOST'];
            }
        }
        return $server_ip;
    }

    /**
     * 取出内容中所有 <img src=''... /> 中的src值
     * @param string $content 文章内容HTML
     * @param string $ignoreHosts 需要忽略的主机地址(如：$ignoreHosts='mmbiz.qpic.cn,img.qq.cn';忽略)
     * @return array 返回给定HTML中的所有图片src值，返回示例
     * Array
     * (
     *   [src4http] => Array
     *     (
     *       [0] => https://box.bdimg.com/static/fisp_static/common/img/searbox/log9876a.png
     *       [1] => http://mmbiz.qpic.cn/mmbiz_jpg/HQick1icWhSv0Fm19XQF2Z2K9tP0XFxpiaHVA9A/0
     *     )
     *
     *   [src4local] => Array
     *     (
     *       [0] => /static/upload/weixinMsg/20181219141934-69913-1612.jpg
     *       [2] => /static/upload/weixinMsg/20181219141957-06372-1301.jpg
     *     )
     *
     * )
     * @author martinzhang
     */
    public static function getImgsSrc($contentHTML, $ignoreHosts = '')
    {
        $contentHTML = html_entity_decode($contentHTML);
        $pattern = '/<img.*src\s*=\s*[\"\']\s*(.*)\s*[\"\'].*\/?>/Ui';
        $int = preg_match_all($pattern, $contentHTML, $matches);
        //print_r($matches); exit;
        if ($int == 0) {
            return ['src4http' => [], 'src4local' => []];
        }

        //过滤忽略的主机列表
        $ignoreHosts = self::fmtArrayFilter($ignoreHosts);

        $src4http = $src4local = [];
        foreach ($matches[1] as $img) {
            $img = trim($img);
            if (!in_array(parse_url($img, PHP_URL_HOST), $ignoreHosts)) {
                if (ZIs::isHttp($img)) {
                    $src4http[] = $img;
                } else {
                    $src4local[] = $img;
                }
            }
        }
        $src4http = self::fmtArrayFilter($src4http);
        $src4local = self::fmtArrayFilter($src4local);
        return ['src4http' => $src4http, 'src4local' => $src4local];
    }

    /**
     * 取出内容中所有背景样式 background:url(...) 的url值
     * @param string $content 文章内容HTML
     * @param string $ignoreHosts 需要忽略的主机地址(如：$ignoreHosts='mmbiz.qpic.cn,img.qq.cn';忽略)
     * @return array 返回给定HTML中的所有图片src值，返回示例
     * Array
     * (
     *   [url4http] => Array
     *     (
     *       [0] => https://box.bdimg.com/static/fisp_static/common/img/searchbox/logo6_88_1f9876a.png
     *       [1] => http://mmbiz.qpic.cn/mmbiz_jpg/HQick14Sv0Fm19XQF2Z2BNLwLHVmibD7eK9tP0XFxpiaHVA9A/0
     *     )
     *
     *   [url4local] => Array
     *     (
     *       [0] => /static/upload/weixinMsg/20181219141934-69913-1612.jpg
     *       [2] => /static/upload/weixinMsg/20181219141957-06372-1301.jpg
     *     )
     *
     * )
     * @author martinzhang
     */
    public static function getBgsUrl($contentHTML, $ignoreHosts = '')
    {
        $contentHTML = html_entity_decode($contentHTML);
        $pattern = '/url\s*\(\s*[\'\"]?\s*((?:http|\.|\/).*)\s*[\'\"]?\s*\)/Ui';
        $int = preg_match_all($pattern, $contentHTML, $matches);
        //print_r($matches); exit;
        if ($int == 0) {
            return ['url4http' => [], 'url4local' => []];
        }

        //过滤忽略的主机列表
        $ignoreHosts = self::fmtArrayFilter($ignoreHosts);

        $url4http = $url4local = [];
        foreach ($matches[1] as $img) {
            $img = trim($img);
            if (!in_array(parse_url($img, PHP_URL_HOST), $ignoreHosts)) {
                if (ZIs::isHttp($img)) {
                    $url4http[] = $img;
                } else {
                    $url4local[] = $img;
                }

            }
        }
        $url4http = self::fmtArrayFilter($url4http);
        $url4local = self::fmtArrayFilter($url4local);
        return ['url4http' => $url4http, 'url4local' => $url4local];
    }


}