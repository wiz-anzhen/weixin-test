<?php

require_once 'MpUserServiceBase.php';

use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\HouseMember;
use WBT\Business\Weixin\CustomerSpecialistBusiness;

class CustomerSpecialistService extends MpUserServiceBase
{
    public function update()
    {
        $data = $this->_app->request()->getArray(
            [
            CustomerSpecialist::CUSTOMER_SPECIALIST_ID,
            CustomerSpecialist::NAME,
            CustomerSpecialist::MP_USER_ID,
            CustomerSpecialist::PHONE  ,
            CustomerSpecialist::VIP_NO,
            CustomerSpecialist::VALID,
            CustomerSpecialist::COMMENT ,
            CustomerSpecialist::STAFF_ID ,
            CustomerSpecialist::HOLIDAY
            ] );
        $vipNo   = $data[CustomerSpecialist::VIP_NO];
        $dataVipNo = WxUser::fetchColumn([WxUser::VIP_NO],[WxUser::MP_USER_ID => $data[CustomerSpecialist::MP_USER_ID]]);
        $wxUser = new WxUser([WxUser::VIP_NO => $vipNo]);

        if(!empty($vipNo))
        {
            if(strict_in_array($vipNo,$dataVipNo))
            {
                $data[CustomerSpecialist::WX_USER_ID] = $wxUser->getWxUserID();
            }
            else
            {
                return['errno' => 1,'error' => '请核对的信息，系统中不存在此会员号'];
            }
        }
        else
        {
            $data[CustomerSpecialist::WX_USER_ID] = "";
        }
        $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $data[CustomerSpecialist::CUSTOMER_SPECIALIST_ID]]);
        $cs->apply($data);
        if($cs->updateInRestraintOfUniqueKey())
        {
            return['errno' => 0];
        }
        else
        {
            return['errno' => 1,'error' => '请核对的信息，系统已存相同的工号'];
        }
    }

    public function updateGroup()
    {
        $data = $this->_app->request()->getArray(
            [
            CustomerSpecialist::CUSTOMER_SPECIALIST_ID ,
            CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID ,
            ]
        );

        $csID  = $data[CustomerSpecialist::CUSTOMER_SPECIALIST_ID];
        $csGroupID = $data[CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID];

        $cs = new CustomerSpecialist();
        $cs->apply($data)->update();
        $houseMember = new HouseMember();
        $houseMember->setCurrentCsGroupID($csGroupID)
                     ->update([HouseMember::CURRENT_CS_ID=>$csID]);

        return ['errno' => 0];
    }

    public function insert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray(
            [
            CustomerSpecialist::MP_USER_ID,
            CustomerSpecialist::NAME ,
            CustomerSpecialist::PHONE  ,
            CustomerSpecialist::COMMENT ,
            CustomerSpecialist::VIP_NO,
            CustomerSpecialist::STAFF_ID ,
            CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID,
            CustomerSpecialist::COMMUNITY_ID,
            CustomerSpecialist::HOLIDAY
            ] );
        $vipNo   = $data[CustomerSpecialist::VIP_NO];
        $wxUser = new WxUser([WxUser::VIP_NO=>$vipNo]);
        $dataVipNo = WxUser::fetchColumn([WxUser::VIP_NO],[WxUser::MP_USER_ID => $data[CustomerSpecialist::MP_USER_ID]]);
        $data[CustomerSpecialist::VALID] = 1;
        if(!empty($vipNo))
        {
            if(strict_in_array($vipNo,$dataVipNo))
            {
                $data[CustomerSpecialist::WX_USER_ID] = $wxUser->getWxUserID();
            }
            else
            {
                return['errno' => 1,'error' => '请核对的信息，系统中不存在此会员号'];
            }
        }
        $customerSpecialist = new CustomerSpecialist();

        $customerSpecialist->apply($data);
        if($customerSpecialist->insertInRestraintOfUniqueKey())
        {
            return['errno' => 0];
        }
        else
        {
            return['errno' => 1,'error' => '请核对的信息，系统已存相同的工号'];
        }
    }

    public function delete()
    {
        $id = $this->_app->request()->get( CustomerSpecialist::CUSTOMER_SPECIALIST_ID );

        return CustomerSpecialistBusiness::delete( $id );
    }

}