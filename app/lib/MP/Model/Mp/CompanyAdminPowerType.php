<?php
//Don't edit this file which is generated by Bluefin Lance.
namespace MP\Model\Mp;

use Bluefin\Convention;
use Bluefin\Data\ValidatorInterface;

class CompanyAdminPowerType implements ValidatorInterface
{
    const CHANNEL = 'channel';
    const DIRECTORY = 'directory';
    const DIRECTORY_SMALL_FLOW = 'directory_small_flow';
    const IMG_CAROUSEL = 'img_carousel';
    const QUESTIONNAIRE = 'questionnaire';
    const ARTICLE = 'article';
    const BILL = 'bill';
    const STORE = 'store';
    const ORDER = 'order';
    const HOUSE_MEMBER = 'house_member';
    const MEMBER = 'member';
    const APP_USER = 'app_user';
    const PHONE_BOOK = 'phone_book';
    const CUSTOMER_SPECIALIST = 'customer_specialist';
    const RECEIVE_QUESTIONNAIRE_NOTIFY = 'receive_questionnaire_notify';
    const RECEIVE_ORDER_NOTIFY = 'receive_order_notify';
    const SEND_BY_GROUP = 'send_by_group';
    const SEND_BY_GROUP_MP = 'send_by_group_mp';
    const USER_NOTIFY = 'user_notify';
    const USER_NOTIFY_MP = 'user_notify_mp';
    const USER_NOTIFY_APP_MP = 'user_notify_app_mp';
    const USER_NOTIFY_APP_C = 'user_notify_app_c';
    const PUSH_MESSAGE_APP_MP = 'push_message_app_mp';
    const PUSH_MESSAGE_APP_C = 'push_message_app_c';
    const BLE = 'ble';

    private static $_data;

    public static function getDictionary()
    {
        if (!isset(self::$_data))
        {
            self::$_data = array(
                self::CHANNEL => _META_('mp.company_admin_power_type.channel'),
                self::DIRECTORY => _META_('mp.company_admin_power_type.directory'),
                self::DIRECTORY_SMALL_FLOW => _META_('mp.company_admin_power_type.directory_small_flow'),
                self::IMG_CAROUSEL => _META_('mp.company_admin_power_type.img_carousel'),
                self::QUESTIONNAIRE => _META_('mp.company_admin_power_type.questionnaire'),
                self::ARTICLE => _META_('mp.company_admin_power_type.article'),
                self::BILL => _META_('mp.company_admin_power_type.bill'),
                self::STORE => _META_('mp.company_admin_power_type.store'),
                self::ORDER => _META_('mp.company_admin_power_type.order'),
                self::HOUSE_MEMBER => _META_('mp.company_admin_power_type.house_member'),
                self::MEMBER => _META_('mp.company_admin_power_type.member'),
                self::APP_USER => _META_('mp.company_admin_power_type.app_user'),
                self::PHONE_BOOK => _META_('mp.company_admin_power_type.phone_book'),
                self::CUSTOMER_SPECIALIST => _META_('mp.company_admin_power_type.customer_specialist'),
                self::RECEIVE_QUESTIONNAIRE_NOTIFY => _META_('mp.company_admin_power_type.receive_questionnaire_notify'),
                self::RECEIVE_ORDER_NOTIFY => _META_('mp.company_admin_power_type.receive_order_notify'),
                self::SEND_BY_GROUP => _META_('mp.company_admin_power_type.send_by_group'),
                self::SEND_BY_GROUP_MP => _META_('mp.company_admin_power_type.send_by_group_mp'),
                self::USER_NOTIFY => _META_('mp.company_admin_power_type.user_notify'),
                self::USER_NOTIFY_MP => _META_('mp.company_admin_power_type.user_notify_mp'),
                self::USER_NOTIFY_APP_MP => _META_('mp.company_admin_power_type.user_notify_app_mp'),
                self::USER_NOTIFY_APP_C => _META_('mp.company_admin_power_type.user_notify_app_c'),
                self::PUSH_MESSAGE_APP_MP => _META_('mp.company_admin_power_type.push_message_app_mp'),
                self::PUSH_MESSAGE_APP_C => _META_('mp.company_admin_power_type.push_message_app_c'),
                self::BLE => _META_('mp.company_admin_power_type.ble'),
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
        return self::CHANNEL;
    }

    public function validate($value)
    {
        $data = self::getDictionary();
        return array_key_exists($value, $data);
    }
}