<?php

namespace WBT\Controller\MpAdmin;
use Bluefin\Data\Database;
use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpUser;
use WBT\Business\UserBusiness;
use MP\Model\Mp\Community;
use MP\Model\Mp\BeaconSetting;
use WBT\Business\Weixin\BleBusiness;
use WBT\Controller\CommunityControllerBase;

class BleController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "ble";
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
        $outputColumns = BeaconSetting::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $condition     = [ BeaconSetting::MP_USER_ID => $mpUserId,BeaconSetting::COMMUNITY_ID => $communityId];
        $ranking       = [ BeaconSetting::BEACON_SETTING_ID =>true ];
        $data          = BleBusiness::getList( $condition, $paging, $ranking, $outputColumns );
       /* //信息编号和摘要注释说明
        $industry = $mpUser->getIndustry();
        $infoidTitle= "信息编号";
        $descriptionTitle = "摘要";
        if($industry == IndustryType::FIANCE)
        {
            $infoidTitle = "来源";
            $descriptionTitle = "备注";
        }*/
        $shownColumns = [
            BeaconSetting::UUID ,
            BeaconSetting::DESCRIPTION =>[Table::COLUMN_TITLE => "描述",],

            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_CELL_STYLE => 'width:10%',
                Table::COLUMN_FUNCTION => function(array $row)
                    {
                        $community_id = $row[BeaconSetting::COMMUNITY_ID];
                        $beacon_setting_id = $row[BeaconSetting::BEACON_SETTING_ID];
                        $update = new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/ble_dialog/update?beacon_setting_id={$beacon_setting_id}&community_id={$community_id}');");
                        $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该设备信息将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/ble/delete?beacon_setting_id={$beacon_setting_id}&community_id={$community_id}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");

                        return $update."<br>".$delete;
                    } ], ];


        $table               = Table::fromDbData( $data, $outputColumns, BeaconSetting::BEACON_SETTING_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }



}