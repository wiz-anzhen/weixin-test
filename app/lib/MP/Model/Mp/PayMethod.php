<?php
//Don't edit this file which is generated by Bluefin Lance.
namespace MP\Model\Mp;

use Bluefin\Convention;
use Bluefin\Data\ValidatorInterface;

class PayMethod implements ValidatorInterface
{
    const CASH_PAY = 'cash_pay';
    const WX_PAY = 'wx_pay';

    private static $_data;

    public static function getDictionary()
    {
        if (!isset(self::$_data))
        {
            self::$_data = array(
                self::CASH_PAY => _META_('mp.pay_method.cash_pay'),
                self::WX_PAY => _META_('mp.pay_method.wx_pay'),
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
        return self::CASH_PAY;
    }

    public function validate($value)
    {
        $data = self::getDictionary();
        return array_key_exists($value, $data);
    }
}