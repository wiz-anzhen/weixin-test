<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\Community;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\IndustryType;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\Directory;
use MP\Model\Mp\ProcurementPowerType;
use MP\Model\Mp\WxUser;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\HouseMemberBusiness;
use WBT\Business\Weixin\WxUserBusiness;
use WBT\Controller\CommunityControllerBase;
use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\CommunityAdminPowerType;
use MP\Model\Mp\CustomerSpecialist;
use WBT\Business\Weixin\CustomerSpecialistBusiness;
use MP\Model\Mp\CustomerSpecialistGroup;
use WBT\Business\Weixin\CustomerSpecialistGroupBusiness;
use Bluefin\HTML\Form;



class HouseController extends CommunityControllerBase
{

    protected function _init()
    {
        $this->_moduleName = "house_member";
        parent::_init();
    }
    //显示
    public function listAction()
    {
        $communityId = $this->_request->get( 'community_id');
        //$paging = []; // 先初始化为空
        $outputColumns = HouseMember::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);
log_debug("paging:",$paging);
        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }

        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;

        $mpUserID = $this->_request->get( MpUser::MP_USER_ID );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userId = UserBusiness::getLoginUser()->getUserID() );
        $this->_view->set( 'community_id', $communityId);
        $ranking    = $this->_request->get( 'rank' );
        $this->_view->set( 'rank', $ranking);
         if(empty($ranking))
         {
             $ranking = [HouseMember::HOUSE_NO];
         }
        else
        {
            if($ranking == 'house_no_reduce')
            {
                $ranking       = [ HouseMember::HOUSE_NO => true ];
            }
            elseif($ranking == 'house_no_increase')
            {
                $ranking       = [ HouseMember::HOUSE_NO ];
            }
            else
            {
                $ranking       = [ $ranking ];
            }
        }
        $condition[HouseMember::MP_USER_ID] = $mpUserID;
        $condition[HouseMember::COMMUNITY_ID] = $communityId;
        $tel = $name = $house_no = $house_address =  $group_name = $customer_name = $employee = $verify = '';
        $tel     = $this->_request->get( 'tel' );
        $this -> _view -> set("tel",$tel);
        $name     = $this->_request->get( 'name' );
        $this -> _view -> set("name",$name);
        $house_no = $this->_request->get( 'house_no' );
        $this -> _view -> set("house_no",$house_no);
        $house_address    = $this->_request->get( 'house_address' );
        $this -> _view -> set("house_address",$house_address);
        $pageArr    = $this->_request->get( '*PAGING*' );
        $page = $pageArr['page'];

        $customer_id    = $this->_request->get( 'customer_id' );
        $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $customer_id]);
        $customer_name = $cs->getName();
        $this -> _view -> set("customer_name",$customer_name);
        $this -> _view -> set("customer_id",$customer_id);
        $group_id    = $this->_request->get( 'group_id' );
        $csGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $group_id]);
        $group_name = $csGroup->getGroupName();
        $this -> _view -> set("group_name",$group_name);

        $employee    = $this->_request->get( 'employee' );
        $this -> _view -> set("employee",$employee);
        $verify    = $this->_request->get( 'verify' );
        $this->_view->set("verify",$verify);
        if (!empty($page))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = intval($page);
        }
        else
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $this->_view->set('page', $paging[Database::KW_SQL_PAGE_INDEX]);
        $condition = [HouseMember::COMMUNITY_ID => $communityId,HouseMember::MP_USER_ID => $mpUserID];
        if(!empty($group_id) && empty($customer_id))
        {
            $condition[HouseMember::CURRENT_CS_GROUP_ID] = $group_id;
        }
        if(!empty($customer_name))
        {
            $condition[HouseMember::CURRENT_CS_ID] = $customer_id;
        }
        if(!empty($name))
        {
            $expr = " name like '%$name%'";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        if(!empty($house_no))
        {
            $condition[HouseMember::HOUSE_NO] = $house_no;
        }
        if(!empty($house_address))
        {
            $expr = " house_address like '%$house_address%'";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        if(!empty($tel))
        {
            $tel = str_replace(' ','',$tel);
            $expr = sprintf("`phone1` = '%s' or `phone2` = '%s' or `phone3`='%s'",$tel,$tel,$tel);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }

        $userType = HouseMemberType::getDictionary();
        $industry = $mpUser->getIndustry();
        if($industry != IndustryType::PROCUREMENT)
        {
            $userType = array_slice($userType,0,5);
        }
        else
        {
            $userType = array_slice($userType,6);
        }

        $this->_view->set("user_type",$userType);
        if(!empty($employee) and $employee != "all")
        {
            $condition[HouseMember::MEMBER_TYPE] = $employee;
        }
        if($verify == 'verify')
        {
            $expr = "wx_user_id is not null";
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        else if($verify == 'null')
        {
            $expr = "wx_user_id is null";
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        $timeVerifyStart = $this->_request->get("time_verify_start");
        $timeVerifyEnd = $this->_request->get("time_verify_end");
        if(!empty($timeVerifyStart) && !empty($timeVerifyEnd))
        {
            $expr = "wx_user_id is not null";
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
            $condition[] = HouseMemberBusiness::getSelectByOrderTimeCondition($timeVerifyStart, $timeVerifyEnd);
        }
        $this->_view->set('time_verify_start', $timeVerifyStart);
        $this->_view->set('time_verify_end', $timeVerifyEnd);
        //第二个搜索
            //下拉

        $conn =[CustomerSpecialistGroup::COMMUNITY_ID => $communityId];
        $csGroup =  CustomerSpecialistGroup::fetchRows( [ '*' ],$conn);
        $this->_view->set('cs_group', $csGroup);
        $this->_view->set('group', $csGroup);
        $cs_group_id = $cs_id = $start_time = $end_time = $cs_group_name = $cs_name = '';

        $cs_group_id     = $this->_request->get( 'cs_group_id' );
        $this->_view->set('cs_group_id', $cs_group_id);
        $cs_id     = $this->_request->get( 'cs_id' );
        $this->_view->set('cs_id', $cs_id);
        $start_time     = $this->_request->get( 'start_time' );
        $this -> _view -> set("start_time",$start_time);
        $end_time     = $this->_request->get( 'end_time' );
        $this -> _view -> set("end_time",$end_time);

        $data          = HouseMemberBusiness::getHouseMemberList( $condition, $paging, $ranking, $outputColumns );
        $this->_view->set('verify_yezhu_count', HouseMemberBusiness::getCommunityVerifyYezhuCount($communityId));
        $this->_view->set('yezhu_count', HouseMemberBusiness::getCommunityYezhuCount($communityId));
        $this->_view->set('verify_zhuhu_count', HouseMemberBusiness::getCommunityVerifyZhuhuCount($communityId));
        $this->_view->set('zhuhu_count', $paging['total']);
        $power = $this->checkChangePower("house_member_rw","house_member_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('house_member_rw', $checkReadPower);
        $shownColumns = [
            HouseMember::HOUSE_NO => [Table::COLUMN_TITLE => "用户/房间号",
                Table::COLUMN_CELL_STYLE => 'width:8%',],
            HouseMember::HOUSE_ADDRESS => [Table::COLUMN_CELL_STYLE => 'width:8%',],
            HouseMember::HOUSE_AREA => [Table::COLUMN_CELL_STYLE => 'width:8%',],
            HouseMember::NAME => [Table::COLUMN_CELL_STYLE => 'width:8%',],
            HouseMember::BIRTHDAY =>[Table::COLUMN_TITLE =>'生日',Table::COLUMN_CELL_STYLE => 'width:8%',Table::COLUMN_HINT => _DICT_('birthday'),],
            HouseMember::PHONE1 =>[Table::COLUMN_TITLE => '电话',
                Table::COLUMN_CELL_STYLE => 'width:10%',
                Table::COLUMN_FUNCTION => function($row){
                        $wxUser = new WxUser([WxUser::WX_USER_ID => $row[HouseMember::WX_USER_ID]]);
                        $phone = $wxUser->getPhone();
                        switch($phone){
                            case $row[HouseMember::PHONE1]: return  '<span>认证电话：<br>'.$row[HouseMember::PHONE1].'</span>'."<br>其它电话：<br>".$row[HouseMember::PHONE2]."<br>".$row[HouseMember::PHONE3];
                                break;
                            case $row[HouseMember::PHONE2]: return '<span>认证电话：<br>'.$row[HouseMember::PHONE2].'</span>'."<br>其它电话：<br>".$row[HouseMember::PHONE1]."<br>".$row[HouseMember::PHONE3];
                                break;
                            case $row[HouseMember::PHONE3]: return  '<span>认证电话：<br>'.$row[HouseMember::PHONE3].'</span>'."<br>其它电话：<br>".$row[HouseMember::PHONE2]."<br>".$row[HouseMember::PHONE1];
                                break;
                            default:
                                 return  '<span>认证电话：<br></span>'."<br>其它电话：<br>".$row[HouseMember::PHONE3].$row[HouseMember::PHONE2]."<br>".$row[HouseMember::PHONE1];
                        }
                }],
            'cs_name' => [
                Table::COLUMN_TITLE => '客服专员',Table::COLUMN_CELL_STYLE => 'width:8%',Table::COLUMN_FUNCTION =>function($row){
                        if(empty($row['cs_name']))
                        {
                            $row['cs_name'] = '';
                        }
                        return $row['cs_name'];
                    }
            ],
            'cs_group_name' => [
                Table::COLUMN_TITLE => '客服组',Table::COLUMN_CELL_STYLE => 'width:8%',Table::COLUMN_FUNCTION =>function($row){
                        if(empty($row['cs_group_name']))
                        {
                            $row['cs_group_name'] = '';
                        }
                        return $row['cs_group_name'];
                    }
            ],
            HouseMember::MEMBER_TYPE => [Table::COLUMN_TITLE => '用户类型',Table::COLUMN_CELL_STYLE => 'width:8%',],
//            HouseMember::ADD_TYPE => [Table::COLUMN_TITLE => '添加类型'],
            HouseMember::WX_USER_ID => [Table::COLUMN_TITLE => '是否认证',Table::COLUMN_CELL_STYLE => 'width:8%',
                                        Table::COLUMN_FUNCTION => function($row)
                                            {
                                                if(empty($row[HouseMember::WX_USER_ID]))
                                                {
                                                    return '否';
                                                }
                                                else
                                                {
                                                    return '是'."<br>".$row[HouseMember::VERIFY_TIME];
                                                }
                                            }],
            HouseMember::COMMENT => [Table::COLUMN_CELL_STYLE => 'width:8%',],];
        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] = [
                    Table::COLUMN_TITLE => '操作',
                    Table::COLUMN_CELL_STYLE => 'width:8%',
                    Table::COLUMN_FUNCTION =>function(array $row)use($power)
                        {
                            $mpUserID = $row[HouseMember::MP_USER_ID];
                            $communityID = $row[HouseMember::COMMUNITY_ID];
                            $wxUserID = $row[HouseMember::WX_USER_ID];
                            $houseMemberID = $row[HouseMember::HOUSE_MEMBER_ID];
                            $name = $row[HouseMember::NAME];
                            $update= new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/house_dialog/edit?mp_user_id={$mpUserID}&community_id={$communityID}&house_member_id={$houseMemberID}');");
                            $delete= new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，目录下所有的内容都将丢失，且无法回复。<br/><br/>确定更要删除吗？', function(){javascript:wbtAPI.call('../fcrm/house/remove?house_member_id={$houseMemberID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('移除成功',function(){location.reload();});});});");
                            $CsChatRecord= "<a href=\"/mp_admin/cs_chat_record/list?mp_user_id={$mpUserID}&wx_user_id={$wxUserID}&community_id={$communityID}&name={$name}\" >聊天记录</a>";
                            $check= new Link('认证', "javascript:bluefinBH.ajaxDialog('/mp_admin/house_dialog/check?mp_user_id={$mpUserID}&community_id={$communityID}&house_member_id={$houseMemberID}');");
                            $reSet= new Link('重置', "javascript:bluefinBH.confirm('警告：将重置该用户在该小区认证状态。<br/><br/>确定更要重置吗？', function(){javascript:wbtAPI.call('../fcrm/house/reset?house_member_id={$houseMemberID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('重置成功',function(){location.reload();});});});");
                            $ret = $update."<br>".$CsChatRecord;
                            if(empty($row[HouseMember::WX_USER_ID]))
                            {
                                $ret .= "<br>".$check;
                            }
                            else
                            {
                                $ret .= "<br>".$reSet;
                            }
                            if($power["delete"])
                            {
                                $ret .= "<br>".$delete;
                            }
                            return $ret;

                        },
                     ];
        }

        $table  = Table::fromDbData( $data, $outputColumns,  HouseMember::HOUSE_MEMBER_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        //    $table->showRecordNo = true;
        $this->_view->set( 'table', $table );
    }


    //显示采购
    public function procurementAction()
    {
        $communityId = $this->_request->get( 'community_id');
        //$paging = []; // 先初始化为空
        $outputColumns = HouseMember::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);
        log_debug("paging:",$paging);
        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }

        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;

        $mpUserID = $this->_request->get( MpUser::MP_USER_ID );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityType = $community->getCommunityType();
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userId = UserBusiness::getLoginUser()->getUserID() );
        $this->_view->set( 'community_id', $communityId);
        $ranking    = $this->_request->get( 'rank' );
        $this->_view->set( 'rank', $ranking);
        if(empty($ranking))
        {
            $ranking = [HouseMember::HOUSE_NO];
        }

        $condition[HouseMember::MP_USER_ID] = $mpUserID;
        $condition[HouseMember::COMMUNITY_ID] = $communityId;
        $tel = $name = $house_no = $house_address =  $group_name = $customer_name = $employee = $verify = '';
        $tel     = $this->_request->get( 'tel' );
        $this -> _view -> set("tel",$tel);
        $name     = $this->_request->get( 'name' );
        $this -> _view -> set("name",$name);

        $pageArr    = $this->_request->get( '*PAGING*' );
        $page = $pageArr['page'];

        $employee    = $this->_request->get( 'employee' );
        $this -> _view -> set("employee",$employee);
        if (!empty($page))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = intval($page);
        }
        else
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $this->_view->set('page', $paging[Database::KW_SQL_PAGE_INDEX]);
        $condition = [HouseMember::COMMUNITY_ID => $communityId,HouseMember::MP_USER_ID => $mpUserID];

        if(!empty($name))
        {
            $expr = " name like '%$name%'";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }

        if(!empty($tel))
        {
            $tel = str_replace(' ','',$tel);
            $expr = sprintf("`phone1` = '%s' or `phone2` = '%s' or `phone3`='%s'",$tel,$tel,$tel);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        $userType = HouseMemberType::getDictionary();
        $industry = $mpUser->getIndustry();
        if($industry != IndustryType::PROCUREMENT)
        {
            $userType = array_slice($userType,0,5);
        }
        else
        {
            $userType = array_slice($userType,6);
        }

        $this->_view->set("user_type",$userType);
        if(!empty($employee) and $employee != "all")
        {
            $condition[HouseMember::MEMBER_TYPE] = $employee;
        }

        $data          = HouseMemberBusiness::getHouseMemberList( $condition, $paging, $ranking, $outputColumns );

        $power = $this->checkChangePower("house_member_rw","house_member_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('house_member_rw', $checkReadPower);
        //标题名称变更
        if($communityType != "procurement_restaurant")
        {
            $shownColumns = [
                HouseMember::NAME => [Table::COLUMN_CELL_STYLE => 'width:8%',],
                HouseMember::MEMBER_TYPE => [Table::COLUMN_TITLE => '用户类型',Table::COLUMN_CELL_STYLE => 'width:8%',],
                HouseMember::PHONE1 =>[Table::COLUMN_TITLE => '电话',
                    Table::COLUMN_CELL_STYLE => 'width:10%',
                    Table::COLUMN_FUNCTION => function($row){
                            $wxUser = new WxUser([WxUser::WX_USER_ID => $row[HouseMember::WX_USER_ID]]);
                            $phone = $wxUser->getPhone();
                            switch($phone){
                                case $row[HouseMember::PHONE1]: return  '<span>认证电话：<br>'.$row[HouseMember::PHONE1].'</span>'."<br>其它电话：<br>".$row[HouseMember::PHONE2]."<br>".$row[HouseMember::PHONE3];
                                    break;
                                case $row[HouseMember::PHONE2]: return '<span>认证电话：<br>'.$row[HouseMember::PHONE2].'</span>'."<br>其它电话：<br>".$row[HouseMember::PHONE1]."<br>".$row[HouseMember::PHONE3];
                                    break;
                                case $row[HouseMember::PHONE3]: return  '<span>认证电话：<br>'.$row[HouseMember::PHONE3].'</span>'."<br>其它电话：<br>".$row[HouseMember::PHONE2]."<br>".$row[HouseMember::PHONE1];
                                    break;
                                default:
                                    return  '<span>认证电话：<br></span>'."<br>其它电话：<br>".$row[HouseMember::PHONE3].$row[HouseMember::PHONE2]."<br>".$row[HouseMember::PHONE1];
                            }
                        }],

                HouseMember::PROCUREMENT_POWER_TYPE => [
                    Table::COLUMN_TITLE => '员工权限',
                    Table::COLUMN_CELL_STYLE => 'width:8%',
                    Table::COLUMN_FUNCTION => function(array $row)
                        {
                            $ret = '';
                            $allPowers = ProcurementPowerType::getDictionary();
                            $powers = explode(',',$row[HouseMember::PROCUREMENT_POWER_TYPE]);
                            foreach($powers as $power)
                            {
                                if(array_key_exists($power,$allPowers))
                                {
                                    $ret.=ProcurementPowerType::getDisplayName($power).'<br/>';
                                }
                            }
                            return $ret;
                        }],
                HouseMember::WX_USER_ID => [Table::COLUMN_TITLE => '是否认证',Table::COLUMN_CELL_STYLE => 'width:8%',
                    Table::COLUMN_FUNCTION => function($row)
                        {
                            if(empty($row[HouseMember::WX_USER_ID]))
                            {
                                return '否';
                            }
                            else
                            {
                                return '是'."<br>".$row[HouseMember::VERIFY_TIME];
                            }
                        }],
                HouseMember::COMMENT => [Table::COLUMN_CELL_STYLE => 'width:8%',],];
        }
        else
        {
            $shownColumns = [
                HouseMember::NAME => [Table::COLUMN_CELL_STYLE => 'width:8%',],
                HouseMember::MEMBER_TYPE => [Table::COLUMN_TITLE => '用户类型',Table::COLUMN_CELL_STYLE => 'width:8%',],
                HouseMember::PHONE1 =>[Table::COLUMN_TITLE => '电话',
                    Table::COLUMN_CELL_STYLE => 'width:10%',
                    Table::COLUMN_FUNCTION => function($row){
                            $wxUser = new WxUser([WxUser::WX_USER_ID => $row[HouseMember::WX_USER_ID]]);
                            $phone = $wxUser->getPhone();
                            switch($phone){
                                case $row[HouseMember::PHONE1]: return  '<span>认证电话：<br>'.$row[HouseMember::PHONE1].'</span>'."<br>其它电话：<br>".$row[HouseMember::PHONE2]."<br>".$row[HouseMember::PHONE3];
                                    break;
                                case $row[HouseMember::PHONE2]: return '<span>认证电话：<br>'.$row[HouseMember::PHONE2].'</span>'."<br>其它电话：<br>".$row[HouseMember::PHONE1]."<br>".$row[HouseMember::PHONE3];
                                    break;
                                case $row[HouseMember::PHONE3]: return  '<span>认证电话：<br>'.$row[HouseMember::PHONE3].'</span>'."<br>其它电话：<br>".$row[HouseMember::PHONE2]."<br>".$row[HouseMember::PHONE1];
                                    break;
                                default:
                                    return  '<span>认证电话：<br></span>'."<br>其它电话：<br>".$row[HouseMember::PHONE3].$row[HouseMember::PHONE2]."<br>".$row[HouseMember::PHONE1];
                            }
                        }],

                HouseMember::PROCUREMENT_POWER_TYPE => [
                    Table::COLUMN_TITLE => '员工权限',
                    Table::COLUMN_CELL_STYLE => 'width:8%',
                    Table::COLUMN_FUNCTION => function(array $row)
                        {
                            $ret = '';
                            $allPowers = ProcurementPowerType::getDictionary();
                            $powers = explode(',',$row[HouseMember::PROCUREMENT_POWER_TYPE]);
                            foreach($powers as $power)
                            {
                                if(array_key_exists($power,$allPowers))
                                {
                                    $ret.=ProcurementPowerType::getDisplayName($power).'<br/>';
                                }
                            }
                            return $ret;
                        }],
                HouseMember::PART_ID => [Table::COLUMN_TITLE => '所在档口',
                    Table::COLUMN_CELL_STYLE => 'width:18%',
                    Table::COLUMN_FUNCTION => function($row)
                        {
                            $supply = $row[HouseMember::PART_ID ];
                            $supply = explode(",",$supply);
                            $supplyData="";
                            foreach($supply as $value)
                            {
                                $progress = explode("_",$value);
                                if(empty($supplyData))
                                {
                                    $supplyData = $progress[0];
                                }
                                else
                                {
                                    $supplyData.= "<br>".$progress[0];
                                }
                            }
                            return $supplyData;
                        }],
                HouseMember::WX_USER_ID => [Table::COLUMN_TITLE => '是否认证',Table::COLUMN_CELL_STYLE => 'width:8%',
                    Table::COLUMN_FUNCTION => function($row)
                        {
                            if(empty($row[HouseMember::WX_USER_ID]))
                            {
                                return '否';
                            }
                            else
                            {
                                return '是'."<br>".$row[HouseMember::VERIFY_TIME];
                            }
                        }],
                HouseMember::COMMENT => [Table::COLUMN_CELL_STYLE => 'width:8%',],];
        }

        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] = [
                Table::COLUMN_TITLE => '操作',
                Table::COLUMN_CELL_STYLE => 'width:8%',
                Table::COLUMN_FUNCTION =>function(array $row)use($power)
                    {
                        $mpUserID = $row[HouseMember::MP_USER_ID];
                        $communityID = $row[HouseMember::COMMUNITY_ID];
                        $wxUserID = $row[HouseMember::WX_USER_ID];
                        $houseMemberID = $row[HouseMember::HOUSE_MEMBER_ID];
                        $name = $row[HouseMember::NAME];
                        $update= new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/house_dialog/edit_procurement?mp_user_id={$mpUserID}&community_id={$communityID}&house_member_id={$houseMemberID}');");
                        $delete= new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，目录下所有的内容都将丢失，且无法回复。<br/><br/>确定更要删除吗？', function(){javascript:wbtAPI.call('../fcrm/house/remove?house_member_id={$houseMemberID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('移除成功',function(){location.reload();});});});");
                        $check= new Link('认证', "javascript:bluefinBH.ajaxDialog('/mp_admin/house_dialog/check_procurement?mp_user_id={$mpUserID}&community_id={$communityID}&house_member_id={$houseMemberID}');");

                        $ret = $update;
                        if(empty($row[HouseMember::WX_USER_ID]))
                        {
                            $ret .= "<br>".$check;
                        }

                        if($power["delete"])
                        {
                            $ret .= "<br>".$delete;
                        }
                        return $ret;

                    },
            ];
        }

        $table  = Table::fromDbData( $data, $outputColumns,  HouseMember::HOUSE_MEMBER_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        //    $table->showRecordNo = true;
        $this->_view->set( 'table', $table );
    }


}