<?php

use Bluefin\Convention;
use Bluefin\Util\Trie;

function bluefin_autoload($class)
{
    if (class_exists($class, false) || interface_exists($class, false))
    {
        return true;
    }

    $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';

    if (DIRECTORY_SEPARATOR != '\\')
    {
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
    }

    if (false === stream_resolve_include_path($file))
    {
        throw new \Bluefin\Exception\FileNotFoundException($file);
    }

    include_once $file;

    if (!class_exists($class, false) && !interface_exists($class, false))
    {
        throw new \Bluefin\Exception\BluefinException("File \"$file\" does not exist or class \"$class\" was not found in the file");
    }

    return true;
}

/**
 * Change underline-separated words into pascal-naming form.
 * @param  $underline_separated_words
 * @return string
 */
function usw_to_pascal($underline_separated_words)
{
    $phrases = explode('_', $underline_separated_words);
    foreach ($phrases as &$phrase)
    {
        $phrase = strtolower($phrase);
        $phrase = ucwords(Convention::getPascalNaming($phrase, $phrase));
    }

    return implode('', $phrases);
}

function pascal_to_usw($pascal)
{
    $len = strlen($pascal);
    $start = 0;
    $words = array();

    for ($i = 1; $i < $len; ++$i)
    {
        if ($pascal[$i] == strtoupper($pascal[$i]))
        {
            $word = substr($pascal, $start, $i);
            $word[0] = strtoupper($word[0]);
            $words[] = $word;
            $start = $i;
        }
    }

    return implode('_', $words);
}

function usw_to_words($underline_separated_words)
{
    $phrases = explode('_', $underline_separated_words);
    foreach ($phrases as &$phrase)
    {
        $phrase = strtolower($phrase);
        $phrase = ucwords(Convention::getPascalNaming($phrase, $phrase));
    }

    return implode(' ', $phrases);
}

function usw_to_const($underline_separated_words)
{
    return strtoupper(strtr($underline_separated_words, array('-' => '_', ' ' => '_')));
}

/**
 * Change underline-separated words into pascal-naming form.
 * @param  $underline_separated_words
 * @return string
 */
function usw_to_camel($underline_separated_words)
{
    $phrases = explode('_', $underline_separated_words, 2);
    return strtolower($phrases[0]) . (count($phrases) > 1 ? usw_to_pascal($phrases[1]) : '');
}

function combine_usw($prefix, $name)
{
    $a1 = explode('_', $prefix);
    $a2 = explode('_', $name);

    $l1 = count($a1);
    $l2 = count($a2);

    $offset = $l1 - $l2;
    if ($offset < 0) $offset = 0;

    $i1 = $offset;
    $i2 = 0;
    while ($i1 < $l1)
    {
        $e1 = $a1[$i1];
        $e2 = $a2[$i2];
        if ($e1 == $e2)
        {
            $i1++;
            $i2++;
        }
        else
        {
            $offset++;
            $i1 = $offset;
            $i2 = 0;
        }
    }

    if ($offset < $l1)
    {
        array_splice($a1, $offset);
    }

    return implode('_', array_merge($a1, $a2));
}

/**
 * 解析VarText中的修饰符的参数。
 * 假如参数没有用单/双引号括起来，则表示该参数本身也是个引用。
 *
 * @param $param
 * @param $thisContext
 * @return array|float|int|mixed|null|string
 */
function parse_param($param, $thisContext)
{
    if (str_is_quoted($param))
    {
        return mb_substr($param, 1, -1);
    }

    if (str_is_wrapped_by($param, '[', ']') || str_is_wrapped_by($param, '{', '}'))
    {
        $array = \Symfony\Component\Yaml\Yaml::load($param);

        if (is_array($array))
        {
            foreach ($array as $key => &$value)
            {
                $value = parse_param($value, $thisContext);
            }
        }

        return $array;
    }

    return _C($param, null, $thisContext);
}

/**
 * Handling modifiers on a given value
 *
 * @param $value
 * @param array $modifiers modifiers to be applied to the value
 * @param \Bluefin\Util\Trie $handlersTrie handlers to handle each modifiers
 * @param null $thisContext context
 * @return mixed
 * @throws Bluefin\Exception\InvalidOperationException
 * @throws Bluefin\Exception\BluefinException
 */
function apply_modifiers($value, array $modifiers, Trie $handlersTrie, $thisContext = null)
{

    foreach ($modifiers as $modifier)
    {
        $modifierHandler = $handlersTrie->findLongestMatch($modifier);

        if (isset($modifierHandler))
        {
            /**
             * @var \Bluefin\VarTextModifier $modifierHandler
             */
            $modifierToken = $modifierHandler->getModifierToken();

            if ($modifierToken == $modifier)
            {
                $parameter = null;
            }
            else
            {
                $parameter = ltrim(mb_substr($modifier, mb_strlen($modifierToken)));

                if ($parameter[0] != Convention::MODIFIER_PARAMETER_DELIMITER)
                {
                    throw new \Bluefin\Exception\InvalidOperationException("Unknown modifier: {$modifier}");
                }

                $parameter = parse_param(ltrim(mb_substr($parameter, 1)), $thisContext);
            }

            $value = $modifierHandler->process($value, $parameter, $thisContext);
        }
        else
        {
            throw new \Bluefin\Exception\InvalidOperationException(
                "Handler for modifier '{$modifier}' is not given!"
            );
        }
    }

    return $value;
}

function is_dot_name($name)
{
    return false !== mb_strpos($name, '.');
}

function make_dot_name()
{
    $args = func_get_args();
    return implode('.', $args);
}

/**
 * Split a string by '|' except '||', and trim each part
 * @param  $name
 * @return array
 */
function split_modifiers($name)
{
    $modifiers = explode(Convention::DELIMITER_MODIFIER, $name);

    $result = array();
    $last = null;
    $cat = false;

    foreach ($modifiers as $modifier)
    {
        if ($modifier == '')
        {
            if (isset($last))
            {
                $last .= Convention::DELIMITER_MODIFIER;
                $cat = true;
            }
            else
            {
                $last = '';
            }
        }
        else if ($cat)
        {
            $last .= $modifier;
            $cat = false;
        }
        else
        {
            if (isset($last))
            {
                $result[] = trim($last);
            }
            $last = $modifier;
        }
    }

    if (isset($last))
    {
        $escaped = Convention::DELIMITER_MODIFIER . Convention::DELIMITER_MODIFIER;

        if (substr($last, -2) == $escaped)
        {
            $last = substr($last, 0, -1);
        }
        $result[] = trim($last);
    }

    return $result;
}

function merge_modifiers(array $parts)
{
    $translated = array();

    foreach ($parts as $part)
    {
        $pos = strpos($part, Convention::DELIMITER_MODIFIER);
        $len = strlen($part);
        while (false !== $pos)
        {
            $part = substr_replace($part, Convention::DELIMITER_MODIFIER, $pos, 0);
            $pos++;
            $len++;
            while ($pos < $len && $part[$pos] == Convention::DELIMITER_MODIFIER) $pos++;
            $pos = strpos($part, Convention::DELIMITER_MODIFIER, $pos);
        }

        $translated[] = $part;
    }

    return implode(Convention::DELIMITER_MODIFIER, $translated);
}

function datetime_to_str($datetime, $format = 'Y-m-d H:i:s')
{
    $value = date($format, $datetime);
    if (false === $value)
    {
        throw new \Bluefin\Exception\InvalidRequestException(_APP_('"%name%" is expected to be timestamp.',
            array('%name%' => 'datetime')));
    }
    return $value;
}

// 获取当前的年月日，返回结果示例： 20140318
function get_current_ymd()
{
    return date('Ymd');
}

/*
 * 输入utf8的汉字
 * 参考：http://blog.csdn.net/xuxujian/article/details/3978031
 */
function hanzi_to_pinyin($_String)
{
    $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".
        "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".
        "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".
        "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".
        "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".
        "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".
        "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".
        "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".
        "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".
        "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".
        "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".
        "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".
        "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".
        "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".
        "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".
        "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";

    $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".
        "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".
        "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".
        "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".
        "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".
        "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".
        "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".
        "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".
        "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".
        "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".
        "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".
        "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".
        "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".
        "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".
        "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".
        "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".
        "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".
        "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".
        "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".
        "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".
        "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".
        "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".
        "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".
        "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".
        "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".
        "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".
        "|-10270|-10262|-10260|-10256|-10254";

    $_TDataKey   = explode('|', $_DataKey);
    $_TDataValue = explode('|', $_DataValue);

    $_Data = (PHP_VERSION>='5.0') ? array_combine($_TDataKey, $_TDataValue) : _array_combine($_TDataKey, $_TDataValue);
    arsort($_Data);
    reset($_Data);

    //if($_Code != 'gb2312')
    $_String = _utf8_to_gb_2312($_String);
    $_Res = '';
    for($i=0; $i<strlen($_String); $i++)
    {
        $_P = ord(substr($_String, $i, 1));
        if($_P>160) { $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536; }
        $_Res .= _pinyin($_P, $_Data);
    }
    return preg_replace("/[^a-z0-9]*/", '', $_Res);
}

// 汉字转拼音辅助函数
function _pinyin($_Num, $_Data)
{
    if($_Num>0      && $_Num<160   )
    {
        return chr($_Num);
    }
    elseif($_Num<-20319 || $_Num>-10247)
    {
        return '';
    }
    else
    {
        $k = null;
        foreach($_Data as $k=>$v)
        {
            if($v<=$_Num) break;
        }
        return $k;
    }
}

// 汉字转拼音辅助函数
function _utf8_to_gb_2312($_C)
{
    $_String = '';
    if($_C < 0x80) $_String .= $_C;
    elseif($_C < 0x800)
    {
        $_String .= chr(0xC0 | $_C>>6);
        $_String .= chr(0x80 | $_C & 0x3F);
    }elseif($_C < 0x10000){
        $_String .= chr(0xE0 | $_C>>12);
        $_String .= chr(0x80 | $_C>>6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif($_C < 0x200000) {
        $_String .= chr(0xF0 | $_C>>18);
        $_String .= chr(0x80 | $_C>>12 & 0x3F);
        $_String .= chr(0x80 | $_C>>6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    }
    return iconv('UTF-8', 'GB2312', $_String);
}

// 汉字转拼音辅助函数
function _array_combine($_Arr1, $_Arr2)
{
    $_Res = null;
    for($i=0; $i<count($_Arr1); $i++)
    {
        $_Res[$_Arr1[$i]] = $_Arr2[$i];
    }
    return $_Res;
}
