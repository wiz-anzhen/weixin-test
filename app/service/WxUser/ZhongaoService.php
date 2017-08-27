<?php

use Bluefin\Service;
use Bluefin\App;
use WBT\Business\Weixin\ZhongaoBusiness;

class ZhongaoService extends Service
{
    public function removeMember()
    {
        $request  = App::getInstance()->request();
        $houseMemberId  = $request->get('house_member_id');

        return ZhongaoBusiness::removeMember($houseMemberId);
    }

    public function addMember()
    {
        $request  = App::getInstance()->request();
        $wxUserId = $request->get('wx_user_id');
        $houseMemberId  = $request->get('house_member_id');
        $name     = $request->get('name');
        $phone1    = $request->get('phone1');
        $memberType = $request->get('member_type');

        return ZhongaoBusiness::addMember($wxUserId, $houseMemberId, $name, $phone1, $memberType);
    }
    public function appAddMember()
    {
        $request  = App::getInstance()->request();
        $phone = $request->get('phone');
        $houseMemberId  = $request->get('house_member_id');
        $name     = $request->get('name');
        $phone1    = $request->get('phone1');
        $memberType = $request->get('member_type');

        return ZhongaoBusiness::addMember($phone, $houseMemberId, $name, $phone1, $memberType);
    }
}