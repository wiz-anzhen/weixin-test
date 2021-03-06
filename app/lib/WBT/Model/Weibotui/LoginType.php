<?php
//Don't edit this file which is generated by Bluefin Lance.
namespace WBT\Model\Weibotui;

use Bluefin\Convention;
use Bluefin\Data\ValidatorInterface;

class LoginType implements ValidatorInterface
{
    const WEIBOTUI = 'weibotui';
    const WEIBO = 'weibo';

    private static $_data;

    public static function getDictionary()
    {
        if (!isset(self::$_data))
        {
            self::$_data = array(
                self::WEIBOTUI => _META_('weibotui.login_type.weibotui'),
                self::WEIBO => _META_('weibotui.login_type.weibo'),
            );
        }

        return self::$_data;
    }

    public static function getValues()
    {
        $data = self::getDictionary();
        return array_keys($data);
    }

    public static function getDisplayName($value)
    {
        $data = self::getDictionary();
        return $data[$value];
    }

    public static function getDefaultValue()
    {
        return self::WEIBOTUI;
    }

    public function validate($value)
    {
        $data = self::getDictionary();
        return array_key_exists($value, $data);
    }
}