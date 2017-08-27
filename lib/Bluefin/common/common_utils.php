<?php


function strict_in_array($needle, array $haystack)
{
    return in_array($needle, $haystack, true);
}

/**
 * 检查某哈希表是否拥有所有指定的键值。
 * @param array $keys 键值数组。
 * @param array $collection 目标哈希表。
 * @return boolean
 */
function all_keys_exists(array $keys, array $collection)
{
    foreach ($keys as $key)
    {
        if (!array_key_exists($key, $collection)) return false;
    }

    return true;
}

/**
 * 检查是否有一个以上的键存在于指定的哈希表中。
 * @param array $keys
 * @param array $collection
 * @return bool
 */
function any_keys_exists(array $keys, array $collection)
{
    foreach ($keys as $e)
    {
        if (array_key_exists($e, $collection)) return true;
    }

    return false;
}

function all_values_exists(array $array, array $collection)
{
    foreach ($array as $e)
    {
        if (!in_array($e, $collection, true)) return false;
    }

    return true;
}

/**
 * 将元素放入集合（集合没有重复的元素）。
 * @param $var 元素值。
 * @param $array 集合。
 */
function array_push_unique(array &$array, $var)
{
    $num = func_num_args();
    if ($num == 2)
    {
        if (is_array($var))
        {
            foreach ($var as $v)
            {
                in_array($v, $array, true) || ($array[] = $v);
            }
        }
        else if (!in_array($var, $array, true))
        {
            $array[] = $var;
        }
    }
    else
    {
        for ($i = 1; $i < $num; ++$i)
        {
            $arg_var = func_get_arg($i);
            in_array($arg_var, $array, true) || ($array[] = $arg_var);
        }
    }
}

function array_erase(array &$array, $value)
{
    $key = array_search($value, $array);
    if (false === $key) return false;

    unset($array[$key]);
    return true;
}

function array_equal(array $a1, array $a2)
{
    $m = count($a1);

    if (count($a2) != $m) return false;

    for ($i = 0; $i < $m; $i++)
    {
        if ($a1[$i] != $a2[$i]) return false;
    }

    return true;
}

function array_get_auto_inc_name(array $array, $name)
{
    $append = 2;
    $value = $name;
    while (in_array($value, $array, true))
    {
        $value = $name . $append;
        $append++;
    }

    return $value;
}

/**
 * 给字符串数组中每个字符串拼接一个指定值。
 * @param $array 字符串数组。
 * @param $var 需要拼接的值。
 * @param $right 左边还是右边。
 * @return array
 */
function array_str_pad(array $array, $var, $left = false, $right = true)
{
    $result = $array;
    $leftPad = $left ? $var : '';
    $rightPad = $right ? $var : '';

    foreach ($result as &$value)
    {
        $value = $leftPad . $value . $rightPad;
    }

    return $result;
}

function array_get_all(array &$array, array $keys, $pop = false)
{
    $result = array();

    foreach ($keys as $key)
    {
        if (array_key_exists($key, $array))
        {
            $result[$key] = $array[$key];
            if ($pop) unset($array[$key]);
        }
    }

    return $result;
}

/**
 * 如果关联数组中存在该键值，则返回该键值对应的值，否则返回第三个参数（默认为null）。
 * @param $array
 * @param $key
 * @param null $default
 * @param bool $pop
 * @return mixed
 */
function array_try_get(array &$array, $key, $default = null, $pop = false)
{
    if (array_key_exists($key, $array))
    {
        $result = $array[$key];
        if ($pop) unset($array[$key]);

        return $result;
    }

    return $default;
}

function array_get_first_key($array)
{
    foreach ($array as $key => $value)
    {
        return $key;
    }

    return null;
}

function array_get_first_value($array)
{
    foreach ($array as $value)
    {
        return $value;
    }

    return null;
}

/**
 * 从一个数组中，按照正则表达式查找匹配键，并返回该键对应的值。
 * @param array $array
 * @param $regex
 * @param bool $all
 * @return array|null 如果$all为true，则返回数组包含所有匹配的键值对，否则返回第一个匹配的值，如果没有匹配的，返回null
 */
function array_get_by_reg(array $array, $regex, $all = false)
{
    $result = array();
    foreach ($array as $key => $val)
    {
        if (preg_match($regex, $key, $matches))
        {
            if ($all)
            {
                $result[$matches[1]] = $val;
            }
            else
            {
                return array($key, $val, $matches);
            }
        }
    }

    return empty($result) ? null : $result;
}

/**
 * 从一个数组中，按照正则表达式查找匹配的值，并返回该值。
 * @param array $array
 * @param $regex
 * @param bool $all
 * @return mixed 如果$all为true，则返回数组包含所有的值，否则返回第一个匹配的值，如果没有匹配的，返回null
 */
function array_search_by_reg(array &$array, $regex, $all = false)
{
    $result = array();
    foreach ($array as $val)
    {
        if (preg_match($regex, $val))
        {
            if ($all)
            {
                $result[] = $val;
            }
            else
            {
                return $val;
            }
        }
    }

    return empty($result) ? null : $result;
}

function array_search_by_filters(array &$array, array $filters)
{
    foreach ($array as $key => $val)
    {
        $found = true;

        foreach ($filters as $filter => $value)
        {
            if ($val[$filter] != $value)
            {
                $found = false;
                break;
            }
        }

        if ($found) return $key;
    }

    return false;
}

function array_to_assoc(array $array)
{
    $result = [];

    foreach ($array as $key => $value)
    {
        if (is_int($key))
        {
            $result[$value] = null;
        }
        else
        {
            $result[$key] = $value;
        }
    }

    return $result;
}

/**
 * 为字符串加上某个字符，如果不是以该字符开头或结尾
 * @param $str
 * @param $sub_str
 * @param bool $left
 * @param bool $right
 * @return string
 */
function str_pad_if($str, $sub_str, $left = false, $right = true)
{
    $l = mb_strlen($sub_str);

    if ($str == '')
    {
        return $sub_str;
    }

    $left && $str != '' && mb_substr($str, 0, $l) != $sub_str && ($str = $sub_str . $str);
    $right && $str != '' && mb_substr($str, -$l) != $sub_str && ($str .= $sub_str);

    return $str;
}

function str_pad_lines($lines, $sub_str, $left = true, $right = false)
{
    $lines = explode("\n", $lines);

    foreach ($lines as &$line)
    {
        if ($left)
        {
            $line = $sub_str . $line;
        }

        if ($right)
        {
            $line .= $sub_str;
        }
    }

    return implode("\n", $lines);
}


/**
 * in_array_after_explode(‘abc’,'abcd,ef') 的结果为false
 * in_array_after_explode(‘abc’,'abc,def') 的结果为true
 * @param string $needle
 * @param string $haystack
 * @param string $delimiter
 * @return bool
 */
function in_array_after_explode($needle, $haystack, $delimiter = ',')
{
    $arr = explode($delimiter, $haystack);
    return in_array($needle, $arr, true);
}

/**
 * 获得两个CSV字符串的公共字串。
 * @param  $str1
 * @param  $str2
 * @return string
 */
function str_csv_intersect($str1, $str2)
{
    $str1Arr = explode(',', $str1);
    $str2Arr = explode(',', $str2);

    return implode(',', array_intersect($str1Arr, $str2Arr));
}

function str_is_quoted($str, $single = true, $double = true)
{
    $len = mb_strlen($str) - 1;
    if ($len < 1) return false;

    if ($str[0] == "'" && $single) return mb_substr($str, $len, 1) == "'";
    if ($str[0] == '"' && $double) return mb_substr($str, $len, 1) == '"';

    return false;
}

function str_is_wrapped_by($str, $left, $right)
{
    $ll = mb_strlen($left);
    $lr = mb_strlen($right);
    $ls = mb_strlen($str);

    if ($ls < $ll+$lr) return false;

    return (mb_substr($str, 0, $ll) == $left && mb_substr($str, $ls-$lr, $lr) == $right);
}

function str_quote($str, $singleQuote = false)
{
    $quote = $singleQuote ? "'": '"';
    $str = str_replace($quote, "\\{$quote}", $str);
    return "{$quote}{$str}{$quote}";
}

function trim_quote($str)
{
    return str_is_quoted($str) ? mb_substr($str, 1, -1) : $str;
}

/**
 * 支持中文的JSON编码方法。
 * @param  $data
 * @return mixed
 */
function json_encode_cn($data)
{
    return preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", json_encode($data));
}

/**
 * @param array $pairs
 * @param string $pairDelimiter
 * @param string $kvDelimiter
 * @return string
 */
function join_key_value_pairs(array $pairs, $pairDelimiter = ';', $kvDelimiter = '=')
{
    $buffer = array();

    foreach ($pairs as $key => $val)
    {
        $buffer[] = "{$key}{$kvDelimiter}{$val}";
    }

    return implode($pairDelimiter, $buffer);
}

/**
 * @param $uri
 * @param array $queryParams
 * @param array $fragmentParams
 * @param bool $checkSum
 * @return string
 */
function build_uri($uri, array $queryParams = null, array $fragmentParams = null, $checkSum = false)
{
    if (empty($queryParams) && empty($fragmentParams) && false === $checkSum) return $uri;

    $parse_url = parse_url($uri);

    if (false !== $checkSum)
    {
        $checkSumFlag = (isset($parse_url["scheme"]) || isset($parse_url["host"])) ?
            '_asig' : '_rsig';

        isset($queryParams) || ($queryParams = []);
        $queryParams[$checkSumFlag] = '__SIG__';
    }

    // Add our params to the parsed uri
    if (isset($queryParams))
    {
        if (isset($parse_url["query"]))
        {
            parse_str($parse_url["query"], $oldQueries);
            $queryParams = array_merge($oldQueries, $queryParams);
        }

        $parse_url["query"] = http_build_query($queryParams);
    }

    if (isset($fragmentParams))
    {
        if (isset($parse_url["fragment"]))
        {
            parse_str($parse_url["fragment"], $oldQueries);
            $fragmentParams = array_merge($oldQueries, $fragmentParams);
        }

        $parse_url["fragment"] = http_build_query($fragmentParams);
    }

    // Put humpty dumpty back together
    $url =
      ((isset($parse_url["scheme"])) ? $parse_url["scheme"] . "://" : "")
      . ((isset($parse_url["user"])) ? $parse_url["user"] . ((isset($parse_url["pass"])) ? ":" . $parse_url["pass"] : "") . "@" : "")
      . ((isset($parse_url["host"])) ? $parse_url["host"] : "")
      . ((isset($parse_url["port"])) ? ":" . $parse_url["port"] : "")
      . ((isset($parse_url["path"])) ? $parse_url["path"] : "")
      . ((!empty($parse_url["query"])) ? "?" . $parse_url["query"] : "")
      . ((!empty($parse_url["fragment"])) ? "#" . $parse_url["fragment"] : "");

    if (false !== $checkSum)
    {
        $sig = substr(md5($url . $checkSum), 4, 8);
        $url = str_replace('__SIG__', $sig, $url);
    }

    return $url;
}

function check_url_sig($url, $flag, $sig, $salt)
{
    $url = str_replace("{$flag}={$sig}", "{$flag}=__SIG__", $url);
    $expected_sig = substr(md5($url . $salt), 4, 8);
    return $expected_sig == $sig;
}

function fix_integer_overflow($size)
{
    if ($size < 0) {
        $size += 2.0 * (PHP_INT_MAX + 1);
    }
    return $size;
}

function uuid_gen()
{
    if (function_exists('uuid_create'))
    {
        return uuid_create();
    }
    else if (function_exists('com_create_guid'))
    {
        return trim(com_create_guid(), "{}");
    }
    else
    {
        die("No uuid generation function.");
    }
}

function fake_uuid()
{
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr($chars,0,8) . '-';
    $uuid .= substr($chars,8,4) . '-';
    $uuid .= substr($chars,12,4) . '-';
    $uuid .= substr($chars,16,4) . '-';
    $uuid .= substr($chars,20,12);
    return $uuid;
}

function exec_shell_command($cmd)
{
    $output = array();

    exec("LANG=\"en_US.UTF8\" {$cmd}", $output, $return_var);

    if ($return_var != 0)
    {
        $outputMsg = implode("\n", $output);
        \Bluefin\App::getInstance()->log()->error("Shell command failed. Cmd: {$cmd}, Code: {$return_var}, Output: {$outputMsg}");
        return false;
    }

    return true;
}

/**
 * 根据配置返回实际的大小
 * @param  $string
 * @return int
 */
function parse_size($string)
{
    preg_match('/^(?<size>-1|\d+)(?<unit>\D*)$/', $string, $matches);
    if ($matches["size"] == -1) return -1;
    $size = (int)$matches["size"];

    switch (strtolower($matches["unit"]))
    {
        case '':
        case 'b':
        case 'byte':
            return $size;
            break;
        case 'k':
        case 'kb':
            return $size * 1024;
            break;
        case 'm':
        case 'mb':
            return $size * 1024 * 1024;
            break;
        case 'g':
        case 'gb':
            return $size * 1024 * 1024 * 1024;
            break;
        default:
            return $string;
            break;
    }
}

function if_null_then($value, $default = null)
{
    return is_null($value) ? $default : $value;
}

function is_int_val($value)
{
    return !is_null($value) && (is_int($value) || ctype_digit($value));
}

function alphalize($number)
{
    $table = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!';

    $base = strlen($table);

    $val = '';

    $number = (int)$number;

    while ($number >= $base)
    {
        $val .= $table[$number % $base];
        $number = (int)($number / $base);
    }

    return $table[$number] . $val;
}

function bluefin_date($date, $format)
{
    if (is_string($date))
    {
        $date = strtotime($date);
    }

    return date($format, $date);
}

function md5_salt($value, $parameter)
{
    return md5($value . '*' . $parameter);
}

function b64_encode($data)
{
    return 'B64' . base64_encode($data);
}

function str_pad_crc($str, $len)
{
    $crc = (string)crc32($str);
    if (strlen($crc) < $len)
    {
        return $str. str_pad($crc, $len, "0", STR_PAD_LEFT);
    }

    return $str . substr($crc, -$len);
}

function is_abs_url($url)
{
    $urlScheme = ['http://', 'https://'];
    foreach ($urlScheme as $scheme)
    {
        $l = mb_strlen($scheme);
        if (mb_substr($url, 0, $l) == $scheme)
        {
            return true;
        }
    }

    return false;
}

// 可逆加密函数
function encrypt($str)
{
    $key = 'kcjg';
    return mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
}

// 可逆解密函数
function decrypt($str)
{
    $key = 'kcjg';
    return  mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
}

function substr_unicode($str, $start, $length = null)
{
    return join("", array_slice(preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $start, $length));
}

/**
 *  * Encode the Unicode values to be used in the URI ,code from wordpress
 *  @since 1.5.0
 * @param string $utf8_string
 * @param int $length Max length of the string
 * @return string String with Unicode encoded for URI.
 * */
function utf8_uri_encode( $utf8_string, $length = 1024 ) {
    $unicode = '';
    $values = array();
    $num_octets = 1;
    $unicode_length = 0;

    $string_length = strlen( $utf8_string );
    for ($i = 0; $i < $string_length; $i++ ) {

        $value = ord( $utf8_string[ $i ] );

        if ( $value < 128 ) {
            if ( $length && ( $unicode_length >= $length ) )
                break;
            $unicode .= chr($value);
            $unicode_length++;
        } else {
            if ( count( $values ) == 0 ) $num_octets = ( $value < 224 ) ? 2 : 3;

            $values[] = $value;

            if ( $length && ( $unicode_length + ($num_octets * 3) ) > $length )
                break;
            if ( count( $values ) == $num_octets ) {
                if ($num_octets == 3) {
                    $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]) . '%' . dechex($values[2]);
                    $unicode_length += 9;
                } else {
                    $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]);
                    $unicode_length += 6;
                }

                $values = array();
                $num_octets = 1;
            }
        }
    }

    return $unicode;
}

// 全角转半角
//  http://blog.csdn.net/yinyiniao/article/details/5411018
function str_to_semiangle($str)
{
    $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
        '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
        'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
        'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
        'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
        'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
        'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
        'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
        'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
        'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
        'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
        'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
        'ｙ' => 'y', 'ｚ' => 'z',
        '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[',
        '】' => ']', '〖' => '[', '〗' => ']', '“' => '[', '”' => ']',
        '‘' => '[', '’' => ']', '｛' => '{', '｝' => '}', '《' => '<',
        '》' => '>',
        '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-',
        '：' => ':', '。' => '.', '、' => ',', '，' => ',', '、' => '.',
        '；' => ';', '？' => '?', '！' => '!', '…' => '-', '‖' => '|',
        '”' => '"', '’' => '`', '‘' => '`', '｜' => '|', '〃' => '"',
        '　' => ' ','＄'=>'$','＠'=>'@','＃'=>'#','＾'=>'^','＆'=>'&','＊'=>'*',
        '＂'=>'"');

    return strtr($str, $arr);
}

function  get_host()
{
    $host =   \Bluefin\App::getInstance()->getContext('root');
    if(substr($host, -1) == '/')
    {
        $host = substr($host,0, strlen($host) -1);
    }

    return $host;
}



// 跳过4和7,餐馆忌讳4、7
function jump4and7($id)
{
    $str = ''.$id;
    $len = strlen($str);

    for($i =0; $i< $len; ++$i)
    {
        if($str[$i] == '4')
        {
            $str[$i] = 5;

            // 之后的字符变为0
            for($j = $i + 1; $j < $len; ++$j)
            {
                $str[$j] = '0';
            }
            break;
        }

        if($str[$i] == '7')
        {
            $str[$i] = 8;

            // 之后的字符变为0
            for($j = $i + 1; $j < $len; ++$j)
            {
                $str[$j] = '0';
            }
            break;
        }
    }

    return intval($str);
}

function _curl_post($url, $data) {
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, 1 );
    curl_setopt($ch, CURLOPT_HEADER, 0 ) ;
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $data = curl_exec($ch);
    curl_close($ch);
    if ($data)
        return $data;
    else
        return false;
}



function _curl_get($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1) ;
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}


// 和 nl2br()的行为不同， nl2br()在字符串中的每个新行（\n）之前插入 HTML 换行符
function replace_nl_to_br($str)
{
    return str_replace("\n",'<br>',$str);
}
