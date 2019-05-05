<?php

/*
|--------------------------------------------------------------------------
| 助手函数库
|--------------------------------------------------------------------------
|
| 1. 尽量使用系统自带函数；
| 2. 尽量使用 function_exists() 函数，防止重名导致的致命错误；
| 3. 函数命名统一使用小写字母下划线，如 random_string()；
|
*/


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

if (! function_exists('gender_name')) {
    /**
     * 性别转换函数 1代表男生 2代表女生 其他表示保密
     */
    function gender_name($num=0) 
    {
        switch ($num) {
            case '1':
                return '男';
                break;
    
            case '2':
                return '女';
                break;

            default:
                return '保密';
                break;
        }
    }
}

if (! function_exists('rand_code')) {
    /**
     *  获取随机数
     * @param  integer $length 生成的位数
     * @param  string $type 生成的类型 0:随机数据  2:获取小写英文数据  3:获取大写英文数据 4:获取特殊符号数据
     * @return mixed
     */
    function rand_code($length = 6, $type = 0)
    {
        $arr = array(
            1 => "0123456789",
            2 => "abcdefghijklmnopqrstuvwxyz",
            3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
            4 => "~@#$%^&*(){}[]|"
        );
        if ($type == 0) {
            array_pop($arr);
            $string = implode("", $arr);
        } elseif ($type == "-1") {
            $string = implode("", $arr);
        } else {
            $string = $arr[$type];
        }
        $count = strlen($string) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $string[rand(0, $count)];
        }
        return $code;
    }
}

if (!function_exists('xml_to_array')) {
    /**
     * xml转数组
     *
     * @param string $xml 输入xml 数据
     * @return array
     */
    function xml_to_array($xml)
    {
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }
}

if (!function_exists('get_client_ip')) {
    /**
     * 获取客户端IP地址
     *
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    function get_client_ip($type = 0, $adv = false)
    {
        $type = $type ? 1 : 0;
        static $ip = null;
        if ($ip !== null) {
            return $ip[$type];
        }
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}

if (!function_exists('do_curl')) {
    /**
     * curl 操作函数
     *
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    function do_curl($url, $params = false, $ispost = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === false) {
            //echo "cURL Error: " . curl_error($ch);
            \Illuminate\Support\Facades\Log::error("CURL -- {$url} -- " . curl_error($ch));
            return false;
        }
        // $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }
}

if (!function_exists('sys_config')) {
    /**
     * 获取 数据表中的 系统配置项
     * @param bool $value
     * @return array | string
     */
    function sys_config($value = false)
    {
        $store_key = 'sys_configs';
        if (!Cache::store('redis')->has($store_key)) {
            $sys_config = DB::table('system_configs')->limit(100)->pluck('value', 'key')->toArray();
            Cache::store('redis')->forever($store_key, $sys_config);
        }
        $config = Cache::store('redis')->get($store_key);
        if ($value) {
            return $config[$value] ?? false;
        }
        return $config;
    }
}

if (!function_exists('sms_accounts')) {
    /**
     * 获取 数据表中的 短信账户
     * @param bool $value
     * @return array | string
     */
    function sms_accounts($value = false)
    {

        $store_key = 'sms_accounts';
        if (!Cache::store('redis')->has($store_key)) {
            $sys_config = DB::table('sms_accounts')->select('key', 'username', 'password')->limit(100)->get();
            $sys_config = $sys_config->keyBy('key')->toArray();
            Cache::store('redis')->forever($store_key, $sys_config);
        }
        $config = Cache::store('redis')->get($store_key);
        if ($value) {
            return $config[$value] ?? false;
        }
        return $config;
    }
}

if (!function_exists('image_url'))
{
    /**
     * 拼接图片路径
     * @return string
     */
    function image_url($path)
    {
        if (Str::startsWith($path,'http://') || Str::startsWith($path,'https://')) {
            return $path;
        }
        return config('app.url') . $path;
    }
}

if (!function_exists('xss_safe_filter')) {
    /**
     * 批量过滤
     *
     * @param  string/array $value [需要过滤的内容]
     * @return  mixed
     */
    function xss_safe_filter($value)
    {
        if ( empty($value) ){
            return $value;
        }else{
            return is_array($value) ? array_map('xss_safe_filter', $value) : get_htmlspecialchars($value);
        }
    }
}

if (!function_exists('get_htmlspecialchars')) {
    function get_htmlspecialchars($value)
    {
        $no = '/%0[0-8bcef]/';
        $value = preg_replace ( $no, ' ', $value );
        $no = '/%1[0-9a-f]/';
        $value = preg_replace ( $no, ' ', $value );
        $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
        $value = preg_replace ( $no, ' ', $value );

        $value = htmlspecialchars($value, ENT_QUOTES);
        return $value;
    }
}


if (!function_exists('ueditor_safe_filter')) {
    /**
     * 富文本过滤函数
     *
     * @param  string $text [需要过滤的内容]
     * @param  string $type [需要过滤的类型]
     *                      [支持的类型: 
     *                          text => 无标签格式,
     *                          link => 只保留链接,
     *                          image => 只保留图片,
     *                          font => 只存在字体样式,
     *                          base => 标题摘要基本格式,
     *                          form => 兼容Form格式,
     *                          html => 内容等允许HTML的格式,
     *                          all => 全HTML格式 没事不要用它，因为这表示全白名单          
     *                      ]
     * @return  mixed
     */
    function ueditor_safe_filter($text, $type = 'html')
    {
        // 无标签格式
        $text_tags = '';

        // 只保留链接
        $link_tags = '<a>';

        // 只保留图片
        $image_tags = '<img>';

        // 只存在字体样式
        $font_tags = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';

        // 标题摘要基本格式
        $base_tags = $font_tags . '<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike><section><header><footer><article><nav><audio><video>';
        
        // 兼容Form格式
        $form_tags = $base_tags . '<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';

        // 内容等允许HTML的格式
        $html_tags = $base_tags . '<meta><ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param>';

        // 全HTML格式 没事不要用它，因为这表示全白名单 
        $all_tags = $form_tags . $html_tags . '<!DOCTYPE><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';
        
        // 过滤标签
        $text = html_entity_decode ( $text, ENT_QUOTES, 'UTF-8' );
        $text = strip_tags ( $text, ${$type . '_tags'} );

        // 过滤攻击代码
        if ($type != 'all') {
            // 过滤危险的属性，如：过滤on事件lang js
            while ( preg_match ( '/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|codebase|dynsrc|lowsrc|onabort|onmousemove|onreset|onresize|onselect|onsubmit|onunload)([^><]*)/i', $text, $mat ) ) {
                $text = str_ireplace ( $mat [0], $mat [1] . ' ' . $mat [3], $text );
            }
            while ( preg_match ( '/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat ) ) {
                $text = str_ireplace ( $mat [0], $mat [1] . ' ' . $mat [3], $text );
            }
            while ( stripos($text,'&#') ){
                $text = str_ireplace('&#',' ',$text);
            };
        }
        return $text;
    }
}

/**
 * 二维数组根据字段进行排序
 * @params array $array 需要排序的数组
 * @params string $field 排序的字段
 * @params string $sort 排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
 */
if (!function_exists('arraySequence')) {
    function arraySequence($array, $field, $sort = 'SORT_DESC')
    {
        $arrSort = array();
        foreach ($array as $uniqid => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        array_multisort($arrSort[$field], constant($sort), $array);
        return $array;
    }
}

if (!function_exists('assoc_unique')) {
    function assoc_unique($arr, $key)
    {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($arr); //sort函数对数组进行排序
        return $arr;
    }
}

if (!function_exists('create_audio_num')) {
    /**
    * 16位音频号
    */
    function create_audio_num()
    {
        $y_code = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $order_sn = $y_code[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $order_sn;
    }
}