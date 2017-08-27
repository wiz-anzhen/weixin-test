<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\Community;
use MP\Model\Mp\DirectoryType;

use MP\Model\Mp\MpUser;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\CommunityBusiness;
use WBT\Business\Weixin\CustomerSpecialistBusiness;
use WBT\Controller\CommunityControllerBase;
use MP\Model\Mp\CustomerSpecialistGroup;
use MP\Model\Mp\CustomerSpecialist;



class CustomerSpecialistController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "customer_specialist";
        parent::_init();
    }
    public function listAction()
    {
        $mpUserID = $this->_request->get( MpUser::MP_USER_ID );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userId = UserBusiness::getLoginUser()->getUserID() );
        $this->_view->set( 'community_id', $communityId);
        $customerSpecialistGroupID =  $this->_request->get( CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID);
        $this->_view->set( 'customer_specialist_group_id', $customerSpecialistGroupID);
        $groupName =  $this->_request->get("group_name");
        $this->_view->set( 'group_name', $groupName);
        $paging = []; // 先初始化为空
        $outputColumns = CustomerSpecialist::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $ranking       = [ CustomerSpecialist::CUSTOMER_SPECIALIST_ID ];
        $condition= [CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID => $customerSpecialistGroupID,CustomerSpecialist::VALID => "1"] ;

        $data          = CustomerSpecialistBusiness::getCustomerSpecialistList( $condition, $paging, $ranking, $outputColumns );
        $power = $this->checkChangePower("customer_specialist_rw","customer_specialist_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('customer_specialist_rw', $checkReadPower);
        $shownColumns = [
            CustomerSpecialist::NAME => [Table::COLUMN_CELL_STYLE => 'width:10%',],
            CustomerSpecialist::PHONE  => [Table::COLUMN_CELL_STYLE => 'width:15%',],
            CustomerSpecialist::COMMENT  => [Table::COLUMN_CELL_STYLE => 'width:15%',],
            CustomerSpecialist::STAFF_ID  => [Table::COLUMN_CELL_STYLE => 'width:10%',],
            CustomerSpecialist::VIP_NO  => [Table::COLUMN_CELL_STYLE => 'width:8%',],
            CustomerSpecialist::HOLIDAY =>
                [
                Table::COLUMN_CELL_STYLE => 'width:15%',
                Table::COLUMN_FUNCTION => function(array $row)
                    {
                        $holiday = $row[CustomerSpecialist::HOLIDAY];
                        $holiday = explode(",",$holiday);
                        $day="";
                        foreach($holiday as $value)
                        {
                            if(empty($day))
                            {
                                $day= $value;
                            }
                            else
                            {
                                $day.= "<br>".$value;
                            }
                        }
                        return $day;
                    }
                ],
        ];
        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] = [
                    Table::COLUMN_CELL_STYLE => 'width:10%',
                    Table::COLUMN_TITLE => "操作",
                    Table::COLUMN_FUNCTION => function(array $row)use($power)
                        {
                            $communityID = $row[CustomerSpecialist::COMMUNITY_ID];
                            $mpUserID = $row[CustomerSpecialist::MP_USER_ID];
                            $customerSpecialistID = $row[CustomerSpecialist::CUSTOMER_SPECIALIST_ID];
                            $customerGroupID = $row[CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID];
                            $update =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/customer_specialist_dialog/update?mp_user_id={$mpUserID}&community_id={$communityID}&customer_specialist_id={$customerSpecialistID}');");
                            $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，目录下所有的内容都将丢失，且无法回复。<br/><br/>确定更要删除吗？', function(){javascript:wbtAPI.call('../fcrm/customer_specialist/delete?mp_user_id={$mpUserID}&customer_specialist_id={$customerSpecialistID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('移除成功',function(){location.reload();});});});");
                            $updateGroup =  new Link('修改客服组', "javascript:bluefinBH.ajaxDialog('/mp_admin/customer_specialist_dialog/updateGroup?mp_user_id={$mpUserID}&community_id={$communityID}&customer_specialist_id={$customerSpecialistID}');");
                            $csRecord =  "<a href=\"/wx_user/cs_chat_record/answer_table?mp_user_id={$mpUserID}&cs_group_id={$customerGroupID}\"  target=\"_blank\" >聊天记录</a>";
                            $ret = $update."<br>".$updateGroup."<br>".$csRecord;
                            if($power["delete"])
                            {
                                $ret .= "<br>".$delete;
                            }
                            return $ret;
                        }
            ];
        }



        $table  = Table::fromDbData( $data, $outputColumns,  CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );
    }

}