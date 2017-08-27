<?php
//Don't edit this file which is generated by Bluefin Lance.
namespace MP\Model\Mp;

use Bluefin\Convention;
use Bluefin\Data\ValidatorInterface;

class CommunityAdminPowerType implements ValidatorInterface
{
    const CHANNEL_R = 'channel_r';
    const CHANNEL_RW = 'channel_rw';
    const CHANNEL_D = 'channel_d';
    const DIRECTORY_R = 'directory_r';
    const DIRECTORY_RW = 'directory_rw';
    const DIRECTORY_D = 'directory_d';
    const DIRECTORY_SMALL_FLOW = 'directory_small_flow';
    const IMG_CAROUSEL_R = 'img_carousel_r';
    const IMG_CAROUSEL_RW = 'img_carousel_rw';
    const IMG_CAROUSEL_D = 'img_carousel_d';
    const QUESTIONNAIRE_R = 'questionnaire_r';
    const QUESTIONNAIRE_RW = 'questionnaire_rw';
    const QUESTIONNAIRE_D = 'questionnaire_d';
    const ARTICLE_R = 'article_r';
    const ARTICLE_RW = 'article_rw';
    const ARTICLE_D = 'article_d';
    const BILL_R = 'bill_r';
    const BILL_RW = 'bill_rw';
    const BILL_D = 'bill_d';
    const STORE_R = 'store_r';
    const STORE_RW = 'store_rw';
    const STORE_D = 'store_d';
    const ORDER_R = 'order_r';
    const ORDER_RW = 'order_rw';
    const HOUSE_MEMBER_R = 'house_member_r';
    const HOUSE_MEMBER_RW = 'house_member_rw';
    const HOUSE_MEMBER_D = 'house_member_d';
    const MEMBER = 'member';
    const APP_USER = 'app_user';
    const PHONE_BOOK_R = 'phone_book_r';
    const PHONE_BOOK_RW = 'phone_book_rw';
    const PHONE_BOOK_D = 'phone_book_d';
    const CUSTOMER_SPECIALIST_R = 'customer_specialist_r';
    const CUSTOMER_SPECIALIST_RW = 'customer_specialist_rw';
    const CUSTOMER_SPECIALIST_D = 'customer_specialist_d';
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
                self::CHANNEL_R => _META_('mp.community_admin_power_type.channel_r'),
                self::CHANNEL_RW => _META_('mp.community_admin_power_type.channel_rw'),
                self::CHANNEL_D => _META_('mp.community_admin_power_type.channel_d'),
                self::DIRECTORY_R => _META_('mp.community_admin_power_type.directory_r'),
                self::DIRECTORY_RW => _META_('mp.community_admin_power_type.directory_rw'),
                self::DIRECTORY_D => _META_('mp.community_admin_power_type.directory_d'),
                self::DIRECTORY_SMALL_FLOW => _META_('mp.community_admin_power_type.directory_small_flow'),
                self::IMG_CAROUSEL_R => _META_('mp.community_admin_power_type.img_carousel_r'),
                self::IMG_CAROUSEL_RW => _META_('mp.community_admin_power_type.img_carousel_rw'),
                self::IMG_CAROUSEL_D => _META_('mp.community_admin_power_type.img_carousel_d'),
                self::QUESTIONNAIRE_R => _META_('mp.community_admin_power_type.questionnaire_r'),
                self::QUESTIONNAIRE_RW => _META_('mp.community_admin_power_type.questionnaire_rw'),
                self::QUESTIONNAIRE_D => _META_('mp.community_admin_power_type.questionnaire_d'),
                self::ARTICLE_R => _META_('mp.community_admin_power_type.article_r'),
                self::ARTICLE_RW => _META_('mp.community_admin_power_type.article_rw'),
                self::ARTICLE_D => _META_('mp.community_admin_power_type.article_d'),
                self::BILL_R => _META_('mp.community_admin_power_type.bill_r'),
                self::BILL_RW => _META_('mp.community_admin_power_type.bill_rw'),
                self::BILL_D => _META_('mp.community_admin_power_type.bill_d'),
                self::STORE_R => _META_('mp.community_admin_power_type.store_r'),
                self::STORE_RW => _META_('mp.community_admin_power_type.store_rw'),
                self::STORE_D => _META_('mp.community_admin_power_type.store_d'),
                self::ORDER_R => _META_('mp.community_admin_power_type.order_r'),
                self::ORDER_RW => _META_('mp.community_admin_power_type.order_rw'),
                self::HOUSE_MEMBER_R => _META_('mp.community_admin_power_type.house_member_r'),
                self::HOUSE_MEMBER_RW => _META_('mp.community_admin_power_type.house_member_rw'),
                self::HOUSE_MEMBER_D => _META_('mp.community_admin_power_type.house_member_d'),
                self::MEMBER => _META_('mp.community_admin_power_type.member'),
                self::APP_USER => _META_('mp.community_admin_power_type.app_user'),
                self::PHONE_BOOK_R => _META_('mp.community_admin_power_type.phone_book_r'),
                self::PHONE_BOOK_RW => _META_('mp.community_admin_power_type.phone_book_rw'),
                self::PHONE_BOOK_D => _META_('mp.community_admin_power_type.phone_book_d'),
                self::CUSTOMER_SPECIALIST_R => _META_('mp.community_admin_power_type.customer_specialist_r'),
                self::CUSTOMER_SPECIALIST_RW => _META_('mp.community_admin_power_type.customer_specialist_rw'),
                self::CUSTOMER_SPECIALIST_D => _META_('mp.community_admin_power_type.customer_specialist_d'),
                self::RECEIVE_QUESTIONNAIRE_NOTIFY => _META_('mp.community_admin_power_type.receive_questionnaire_notify'),
                self::RECEIVE_ORDER_NOTIFY => _META_('mp.community_admin_power_type.receive_order_notify'),
                self::SEND_BY_GROUP => _META_('mp.community_admin_power_type.send_by_group'),
                self::SEND_BY_GROUP_MP => _META_('mp.community_admin_power_type.send_by_group_mp'),
                self::USER_NOTIFY => _META_('mp.community_admin_power_type.user_notify'),
                self::USER_NOTIFY_MP => _META_('mp.community_admin_power_type.user_notify_mp'),
                self::USER_NOTIFY_APP_MP => _META_('mp.community_admin_power_type.user_notify_app_mp'),
                self::USER_NOTIFY_APP_C => _META_('mp.community_admin_power_type.user_notify_app_c'),
                self::PUSH_MESSAGE_APP_MP => _META_('mp.community_admin_power_type.push_message_app_mp'),
                self::PUSH_MESSAGE_APP_C => _META_('mp.community_admin_power_type.push_message_app_c'),
                self::BLE => _META_('mp.community_admin_power_type.ble'),
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
        return self::CHANNEL_R;
    }

    public function validate($value)
    {
        $data = self::getDictionary();
        return array_key_exists($value, $data);
    }
}