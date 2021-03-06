<?php
//Don't edit this file which is generated by Bluefin Lance.
namespace MP\Model\Mp;

use Bluefin\Convention;
use Bluefin\Data\ValidatorInterface;

class ReasonType implements ValidatorInterface
{
    const OPTION_FIRST = 'option_first';
    const OPTION_SECOND = 'option_second';
    const OPTION_THIRD = 'option_third';
    const OPTION_FOURTH = 'option_fourth';
    const OTHER = 'other';

    private static $_data;

    public static function getDictionary()
    {
        if (!isset(self::$_data))
        {
            self::$_data = array(
                self::OPTION_FIRST => _META_('mp.reason_type.option_first'),
                self::OPTION_SECOND => _META_('mp.reason_type.option_second'),
                self::OPTION_THIRD => _META_('mp.reason_type.option_third'),
                self::OPTION_FOURTH => _META_('mp.reason_type.option_fourth'),
                self::OTHER => _META_('mp.reason_type.other'),
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
        return self::OPTION_FIRST;
    }

    public function validate($value)
    {
        $data = self::getDictionary();
        return array_key_exists($value, $data);
    }
}