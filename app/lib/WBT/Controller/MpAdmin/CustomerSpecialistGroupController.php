<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\Community;
use MP\Model\Mp\DirectoryType;

use MP\Model\Mp\MpArticle;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\Directory;
use MP\Model\Mp\TopDirectory;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\CommunityBusiness;
use WBT\Business\Weixin\CustomerSpecialistGroupBusiness;
use WBT\Controller\CommunityControllerBase;
use MP\Model\Mp\CustomerSpecialistGroup;



class CustomerSpecialistGroupController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "customer_specialist";
        parent::_init();
    }
    //显示
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

        $paging = []; // 先初始化为空
        $outputColumns = CustomerSpecialistGroup::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $ranking       = [ CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID ];
        $condition= [CustomerSpecialistGroup::MP_USER_ID => $mpUserID,CustomerSpecialistGroup::COMMUNITY_ID => $communityId] ;

        $data          = CustomerSpecialistGroupBusiness::getCustomerSpecialistGroupList( $condition, $paging, $ranking, $outputColumns );
        $power = $this->checkChangePower("customer_specialist_rw","customer_specialist_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('customer_specialist_rw', $checkReadPower);
        $shownColumns = [
            CustomerSpecialistGroup::GROUP_NAME => [Table::COLUMN_CELL_STYLE => 'width:30%',],
            CustomerSpecialistGroup::COMMENT  => [Table::COLUMN_CELL_STYLE => 'width:30%',],
            CustomerSpecialistGroup::WORK_TIME => [
                Table::COLUMN_TITLE =>'工作时间段',
                Table::COLUMN_CELL_STYLE => 'width:15%',
                Table::COLUMN_FUNCTION => function (array $row){
                        if(empty($row[CustomerSpecialistGroup::WORK_TIME]))
                        {
                            $timeValue ="";
                        }
                        else
                        {
                            $workTime = explode("-",$row[CustomerSpecialistGroup::WORK_TIME]);
                            $timeValue = [];
                            foreach($workTime as $value)
                            {
                                $time = explode(":",$value);
                                $timeValue[] = $time[0].":".$time[1]."--".$time[2].":".$time[3];

                            }
                            $timeValue = implode("<br>",$timeValue);
                        }
                        return $timeValue;
                    },
            ],
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_CELL_STYLE => 'width:15%',
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_FUNCTION => function(array $row)use($power)
                    {
                        $communityID = $row[CustomerSpecialistGroup::COMMUNITY_ID];
                        $mpUserID = $row[CustomerSpecialistGroup::MP_USER_ID];
                        $customerSpecialistGroupID = $row[CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID];
                        $groupName = $row[CustomerSpecialistGroup::GROUP_NAME];

                        $customerSpecialist =  new Link('客服专员列表'," /mp_admin/customer_specialist/list?customer_specialist_group_id={$customerSpecialistGroupID}&group_name={$groupName}&mp_user_id={$mpUserID}&community_id={$communityID}");
                        $ret = $customerSpecialist;

                        return $ret;
                    }
             ], ];
        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] = [
                    Table::COLUMN_CELL_STYLE => 'width:15%',
                    Table::COLUMN_TITLE => "操作",
                    Table::COLUMN_FUNCTION => function(array $row)use($power)
                        {
                            $communityID = $row[CustomerSpecialistGroup::COMMUNITY_ID];
                            $mpUserID = $row[CustomerSpecialistGroup::MP_USER_ID];
                            $customerSpecialistGroupID = $row[CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID];
                            $groupName = $row[CustomerSpecialistGroup::GROUP_NAME];
                            $update = new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/customer_specialist_group_dialog/update?mp_user_id={$mpUserID}&community_id={$communityID}&customer_specialist_group_id={$customerSpecialistGroupID}');");
                            $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，目录下所有的内容都将丢失，且无法回复。<br/><br/>确定更要删除吗？', function(){javascript:wbtAPI.call('../fcrm/customer_specialist_group/delete?customer_specialist_group_id={$customerSpecialistGroupID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('移除成功',function(){location.reload();});});});");
                            $customerSpecialist =  new Link('客服专员列表'," /mp_admin/customer_specialist/list?customer_specialist_group_id={$customerSpecialistGroupID}&group_name={$groupName}&mp_user_id={$mpUserID}&community_id={$communityID}");
                            $ret = $update."<br>".$customerSpecialist;
                            if($power["delete"])
                            {
                                $ret .= "<br>".$delete;
                            }
                            return $ret;
                        } ];
        }


        $table  = Table::fromDbData( $data, $outputColumns,  CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );
    }
}