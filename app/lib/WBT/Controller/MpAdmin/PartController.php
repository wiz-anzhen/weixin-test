<?php

namespace WBT\Controller\MpAdmin;
use Bluefin\Data\Database;
use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpUser;
use WBT\Business\ConfigBusiness;
use WBT\Business\UserBusiness;

use MP\Model\Mp\Store;
use MP\Model\Mp\Restaurant;
use MP\Model\Mp\Part;
use MP\Model\Mp\Product;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\PartBusiness;
use WBT\Controller\CommunityControllerBase;

class PartController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "store";
        parent::_init();
    }
    //显示
    public function listAction()
    {
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $paging = []; // 先初始化为空
        $outputColumns = Part::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $condition     = [ Part::MP_USER_ID => $mpUserId,Part::COMMUNITY_ID => $communityId];
        $ranking       = [ Part::PART_ID ];
        $data          = PartBusiness::getList( $condition, $paging, $ranking, $outputColumns );
        $power = $this->checkChangePower("store_rw","store_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('store_rw', $checkReadPower);
        $shownColumns = [
            Part::TITLE => [ Table::COLUMN_TITLE => "餐厅名称"],
            Part::BOUND_STORE_ID => [ Table::COLUMN_TITLE => "绑定供应商",
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        $supply = $row[Part::BOUND_STORE_ID];
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
            Part::COMMENT,
            ];
        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] =  [
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_CELL_STYLE => 'width:23%',
                Table::COLUMN_FUNCTION => function(array $row)use($power)
                    {
                        $communityID = $row[Part::COMMUNITY_ID];
                        $mpUserID = $row[Part::MP_USER_ID];
                        $partID = $row[Part::PART_ID];
                        $update =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/part_dialog/part_update?part_id={$partID}&community_id={$communityID}');");
                        $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该餐厅将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/part/part_delete?part_id={$partID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");


                        $ret = $update;
                        if($power["delete"])
                        {
                            $ret .= "<br>".$delete;
                        }
                        return $ret;
                    } ];
        }

        $table               = Table::fromDbData( $data, $outputColumns, Part::PART_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }



}