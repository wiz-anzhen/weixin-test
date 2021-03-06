<?php
//Don't edit this file which is generated by Bluefin Lance.
namespace MP\Model\Mp;

use Bluefin\Convention;
use Bluefin\Data\ValidatorInterface;

class ReocrdContentType implements ValidatorInterface
{
    const TEXT = 'text';
    const PIC = 'pic';
    const VOICE = 'voice';

    private static $_data;

    public static function getDictionary()
    {
        if (!isset(self::$_data))
        {
            self::$_data = array(
                self::TEXT => _META_('mp.reocrd_content_type.text'),
                self::PIC => _META_('mp.reocrd_content_type.pic'),
                self::VOICE => _META_('mp.reocrd_content_type.voice'),
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
        return self::TEXT;
    }

    public function validate($value)
    {
        $data = self::getDictionary();
        return array_key_exists($value, $data);
    }
}