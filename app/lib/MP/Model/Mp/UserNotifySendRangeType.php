<?php
//Don't edit this file which is generated by Bluefin Lance.
namespace MP\Model\Mp;

use Bluefin\Convention;
use Bluefin\Data\ValidatorInterface;

class UserNotifySendRangeType implements ValidatorInterface
{
    const SEND_TO_WHOLE_COMMUNITY = 'send_to_whole_community';
    const SEND_BY_HOUSE_NO = 'send_by_house_no';
    const SEND_CUSTOMER = 'send_customer';
    const SEND_TOTAL = 'send_total';
    const SEND_TOTAL_VERIFY = 'send_total_verify';
    const SEND_TOTAL_UN_VERIFY = 'send_total_un_verify';
    const SEND_TO_WHOLE_APP = 'send_to_whole_app';
    const SEND_APP_COMMUNITY = 'send_app_community';

    private static $_data;

    public static function getDictionary()
    {
        if (!isset(self::$_data))
        {
            self::$_data = array(
                self::SEND_TO_WHOLE_COMMUNITY => _META_('mp.user_notify_send_range_type.send_to_whole_community'),
                self::SEND_BY_HOUSE_NO => _META_('mp.user_notify_send_range_type.send_by_house_no'),
                self::SEND_CUSTOMER => _META_('mp.user_notify_send_range_type.send_customer'),
                self::SEND_TOTAL => _META_('mp.user_notify_send_range_type.send_total'),
                self::SEND_TOTAL_VERIFY => _META_('mp.user_notify_send_range_type.send_total_verify'),
                self::SEND_TOTAL_UN_VERIFY => _META_('mp.user_notify_send_range_type.send_total_un_verify'),
                self::SEND_TO_WHOLE_APP => _META_('mp.user_notify_send_range_type.send_to_whole_app'),
                self::SEND_APP_COMMUNITY => _META_('mp.user_notify_send_range_type.send_app_community'),
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
        return self::SEND_TO_WHOLE_COMMUNITY;
    }

    public function validate($value)
    {
        $data = self::getDictionary();
        return array_key_exists($value, $data);
    }
}