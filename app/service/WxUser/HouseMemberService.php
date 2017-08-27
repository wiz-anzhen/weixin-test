<?php

use Bluefin\Service;
use Bluefin\App;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use WBT\Business\Weixin\WxUserBusiness;
use MP\Model\Mp\Bill;
use WBT\Business\Weixin\HouseMemberBusiness;
use MP\Model\Mp\AddressLevelInfo;
// 普通用户账户设置相关api
// todo : 安全方面，增加token，
class HouseMemberService extends Service
{
    public function certify()
    {
        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $memberNumber = App::getInstance()->request()->get('memberNumber');
        $phone    = App::getInstance()->request()->get('phone');
        $name = App::getInstance()->request()->get('name');
        $type = App::getInstance()->request()->get('type');
        $address = App::getInstance()->request()->get('address');
        //取出所有信息，以levelID为键名
        $addressInfo = AddressLevelInfo::fetchRows(['*']);
        $addressProcess = [];
        foreach($addressInfo as $value)
        {
            $addressProcess [$value[AddressLevelInfo::ADDRESS_LEVEL_INFO_ID]]= $value[AddressLevelInfo::ADD_INFO];
        }
        log_debug("=================",$addressProcess);
        //对传过来的address进行处理
        $address = explode(",",$address);
        $addressLevel = [];//各等级地址
        foreach($address as $value)
        {
            if(!empty($value))
            {
                $addressLevel[] = $addressProcess[$value];
            }
        }
        log_debug("=================",$addressLevel);
        $address = implode("->",$addressLevel);
        log_debug("=================".$address);
        $ret = [$wxUserID,$memberNumber,$phone,$name,$type,$address];
        log_debug("=================",$ret);
        return HouseMemberBusiness::certify($wxUserID,$memberNumber,$phone,$name,$type,$address);
    }

    public function returnId()
    {
        $addressLevelInfoID = App::getInstance()->request()->get('address_level_info_id');
        log_debug("=================".$addressLevelInfoID);
        $address = AddressLevelInfo::fetchRows(['*'],[AddressLevelInfo::PARENT_ID => $addressLevelInfoID]);
        log_debug("=================",$address);
        $address = json_encode($address);
        return $address;
    }

}
