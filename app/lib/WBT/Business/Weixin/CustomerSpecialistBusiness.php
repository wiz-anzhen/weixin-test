<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\CustomerSpecialistGroup;
use WBT\Business\Weixin\CustomerSpecialistGroupBusiness;

class CustomerSpecialistBusiness extends BaseBusiness
{

    //列表的显示
    public static function getCustomerSpecialistList(array $condition, array &$paging = null, $ranking, array $outputColumns = null)
    {
        return CustomerSpecialist::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
    public static function getSelectGroup($mpUserID,$communityID,$groupName)
    {
        $customerSpecialistGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::GROUP_NAME => $groupName]);
        if($customerSpecialistGroup->isEmpty())
        {//提示：你输入的客服组名称有误，请检查
            //$this->_view->set("notice","你输入的客服组名称不存在，请检查");
            $condition['wx_user_id'] = -1;
        }
        else
        {
            $customerSpecialistGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::GROUP_NAME => $groupName]);
            $customerSpecialistGroupID = $customerSpecialistGroup ->getCustomerSpecialistGroupID();
            $pag = null;
            $conn['customer_specialist_group_id'] = $customerSpecialistGroupID;
            $customerSpecialistIDArr = CustomerSpecialistBusiness::getCustomerSpecialistList($conn,$pag,null,null);
            $cs = $wx = null;
            //$condition = ['no' => [1,2,3]]
            for($i = 0;$i < count($customerSpecialistIDArr);$i++)
            {
                $cs['customer_specialist_id'][] = $customerSpecialistIDArr[$i]['customer_specialist_id'];
            }
            $conditionCustomer = ['current_cs_id' => $cs['customer_specialist_id']] ;
            $wxIDArr = WxUserBusiness::getWxUserList($conditionCustomer,$pag,null,null);
            for($i = 0;$i < count($wxIDArr);$i++)
            {
                $wx[] = $wxIDArr[$i]['wx_user_id'];
            }
            $condition = ['wx_user_id' => $wx,'community_id' => $communityID,'mp_user_id' => $mpUserID] ;
            $expr = "wx_user_id is not null";
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        return $condition;
    }
    public static function getSelectCustomer($mpUserID,$communityID,$customerName,$group_name)
    {
        if(!empty($group_name))
        {
            $customerSpecialistGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::GROUP_NAME => $group_name]);
            $customerSpecialistGroupID = $customerSpecialistGroup ->getCustomerSpecialistGroupID();
            if($customerSpecialistGroup->isEmpty())
            {
                //$this->_view->set("notice","你输入的客服组名称不存在，请检查");//
                $condition['wx_user_id'] = -1;
                $customerSpecialist = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID => $customerSpecialistGroupID]);
            }
            else
            {
                $customerSpecialist = new CustomerSpecialist([CustomerSpecialist::NAME => $customerName,CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID => $customerSpecialistGroupID]);
            }
        }
        else
        {
            $customerSpecialist = new CustomerSpecialist([CustomerSpecialist::NAME => $customerName]);
        }

        if($customerSpecialist->isEmpty())
        {//提示：你输入的客服专员姓名有误，请检查
            //$this->_view->set("notice_customer","你输入的客服专员名称不存在，请检查");
            $condition['wx_user_id'] = -1;
        }
        else
        {
            $customerSpecialist = new CustomerSpecialist([CustomerSpecialist::NAME => $customerName,$customerSpecialist::MP_USER_ID => $mpUserID,CustomerSpecialist::COMMUNITY_ID => $communityID]);
            $pag = $wx = null;
            $customerSpecialistID = $customerSpecialist -> getCustomerSpecialistID();

                $con['current_cs_id'] = $customerSpecialistID;


            $wxIDArr = WxUserBusiness::getWxUserList($con,$pag,null,null);

            for($i = 0;$i < count($wxIDArr);$i++)
            {
                $wx[] = $wxIDArr[$i]['wx_user_id'];
            }

            $condition = ['wx_user_id' => $wx,'community_id' => $communityID,'mp_user_id' => $mpUserID] ;
            $expr = "wx_user_id is not null";
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        return $condition;
    }
    public static function getSelectCustomerFirst($mpUserID,$communityID,$groupID,$csID = null)
    {
        $pag = null;
        $arrCsID = $arrWxUserIDGroup = $arrWxUserIDCs = null;
        if(!empty($groupID) && empty($csID))
        {
            $connGroup = [CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID => $groupID];
            $arrCsIDList = CustomerSpecialistBusiness::getCustomerSpecialistList($connGroup,$pag,null,null);
            for($i=0;$i<count($arrCsIDList);$i++)
            {
                $arrCsID[] = $arrCsIDList[$i][CustomerSpecialist::CUSTOMER_SPECIALIST_ID];
            }
            $connCs = [WxUser::FIRST_CS_ID => $arrCsID ];

            $arrWxUserIDGroupList = WxUserBusiness::getWxUserList($connCs,$pag,null,null);
            for($i=0;$i<count($arrWxUserIDGroupList);$i++)
            {
                $arrWxUserIDGroup[] = $arrWxUserIDGroupList[$i][WxUser::WX_USER_ID];
            }
            $condition = ['wx_user_id' => $arrWxUserIDGroup,'mp_user_id' => $mpUserID,'community_id' => $communityID];
            $expr = "wx_user_id is not null";
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        else if(!empty($groupID) && !empty($csID))
        {
            $connCs = [WxUser::FIRST_CS_ID => $csID ];

            $arrWxUserIDCsList = WxUserBusiness::getWxUserList($connCs,$pag,null,null);
            for($i=0;$i<count($arrWxUserIDCsList);$i++)
            {
                $arrWxUserIDCs[] = $arrWxUserIDCsList[$i][WxUser::WX_USER_ID];
            }

            $condition = ['wx_user_id' => $arrWxUserIDCs,'mp_user_id' => $mpUserID,'community_id' => $communityID];
            $expr = "wx_user_id is not null";
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        else
        {
            $condition = ['mp_user_id' => $mpUserID,'community_id' => $communityID];
        }
        return $condition;
    }
    public static function getSelectByTime($condition,$mpUserID,$communityID,$timeS = null,$timeE = null,$flag)
    {
        log_debug("77777",$condition);
        $yearE = substr($timeE,0,4);
        $monthE = substr($timeE,4,2);
        $dayE = substr($timeE,6,2);
        $yearS = substr($timeS,0,4);
        $monthS = substr($timeS,4,2);
        $dayS = substr($timeS,6,2);
        $connWx = ['wx_user_id' => $condition['wx_user_id']];
        if($flag == 'end')
        {

            $time = $yearE . "-" . $monthE . "-" . $dayE . " " . "23:59:59";
            $exprWx = sprintf("`first_cs_update_time` < '%s'",$time);
        }
        else if($flag == 'start')
        {

            $time = $yearS . "-" . $monthS . "-" . $dayS . " " . "00:00:01";
            $exprWx = sprintf("`first_cs_update_time` > '%s'",$time);
        }
        else
        {
            $timeStart = $yearS . "-" . $monthS . "-" . $dayS . " " . "23:59:59";
            $timeEnd = $yearE . "-" . $monthE . "-" . $dayE . " " . "00:00:01";
            $exprWx = sprintf("`first_cs_update_time` > '%s' and `first_cs_update_time` < '%s'",$timeStart,$timeEnd);
        }
        $con = new \Bluefin\Data\DbCondition($exprWx);
        $connWx[] = $con;$pagWx= null;$wx = [];
        $exprWxTime = "first_cs_update_time is not null";
        $conTime = new \Bluefin\Data\DbCondition($exprWxTime);
        $connWx[] = $conTime;
        log_debug("001",$connWx);
        $dataWx = WxUserBusiness::getWxUserList($connWx,$pagWx,null,null);
        log_debug("002",$dataWx);
        if(!empty($dataWx))
        {
            for($i = 0;$i < count($dataWx);$i++)
            {
                $wx[] = $dataWx[$i]['wx_user_id'];
            }
            $conditionNew = ['wx_user_id' => $wx] ;
        }
        else
        {
            $conditionNew = ['wx_user_id' => -1] ;
        }
        return $conditionNew;
    }
    //数据的录入
    public static function insert($data)
    {
        $obj = new CustomerSpecialist();
        $obj->apply( $data );
        $obj->insert();

        return [ 'errno' => 0 ];
    }
    //修改
    public static function update( $id,$data )
    {
        $obj = new CustomerSpecialist([ CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $id]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find TopDirectory($id)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }

        $obj->apply( $data );
        $obj->update();

        return ['errno' => 0];
    }
    //删除
    public static function delete( $id )
    {
        $obj = new CustomerSpecialist([ CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $id]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Store($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        try {
            $obj->setValid("0")->update();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }
    //覆盖
    public static function cover( $staffID,$data )
    {
        $obj = new CustomerSpecialist([ CustomerSpecialist::STAFF_ID => $staffID]);

        if ($obj->isEmpty())
        {
            log_debug( "Could not find CustomerSpecialist($staffID)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }

        $obj->apply( $data );

        try {
            $obj->update();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }

    public static function getGroupName($mpUserID, $CommunityID)
    {
        $condition = ['mp_user_id'=> $mpUserID, 'community_id'=> $CommunityID];
        $arrGroup  = CustomerSpecialistGroupBusiness::getCustomerSpecialistGroupList($condition);

        $groupName = array();
        for ($i = 0; $i < count($arrGroup); $i++)
        {
            $groupName[$arrGroup[$i]['customer_specialist_group_id']] = $arrGroup[$i]['group_name']; //以主键id作为下标，值为某一字段值，的一个数组
        }
        log_debug("000000000000000",$groupName);
        return $groupName;
    }

}