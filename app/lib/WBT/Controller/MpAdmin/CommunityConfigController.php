<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\Community;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\CommunityConfig;

use WBT\Business\UserBusiness;
use WBT\Business\Weixin\CommunityConfigBusiness;
use WBT\Controller\CommunityControllerBase;



class CommunityConfigController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "";
        parent::_init();
    }
    //显示
    public function listAction()
    {
        if (!$this->_isMpAdmin)
        {
            if(!$this->_isCompanyAdmin)
            {
                $this->_redirectToErrorPage("您没有权限访问此页面。");
            }
        }
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
        $outputColumns = CommunityConfig::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        $paging = [];
        $ranking       = [ CommunityConfig::COMMUNITY_CONFIG_ID ];
        $condition= [CommunityConfig::COMMUNITY_ID => $communityId,CommunityConfig::MP_USER_ID => $mpUserID] ;

        $data          = CommunityConfigBusiness::getCommunityConfigList( $condition, $paging, $ranking, $outputColumns );

        $shownColumns =
        [
            CommunityConfig::CONFIG_TYPE,
            CommunityConfig::CONFIG_VALUE,
            Table::COLUMN_OPERATIONS =>
             [
                Table::COLUMN_CELL_STYLE => 'width:15%',
                Table::COLUMN_OPERATIONS =>
                  [
                     new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/community_config_dialog/update?mp_user_id={{this.mp_user_id}}&community_id={{this.community_id}}&community_config_id={{ this.community_config_id }}');"),
                    new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，目录下所有的内容都将丢失，且无法回复。<br/><br/>确定更要删除吗？', function(){javascript:wbtAPI.call('../fcrm/community_config/delete?community_config_id={{ this.community_config_id }}&community_id={{this.community_id}}', null, function(){bluefinBH.showInfo('移除成功',function(){location.reload();});});});"),
                  ],
             ],
        ];

        $table  = Table::fromDbData( $data, $outputColumns,  CommunityConfig::CONFIG_TYPE, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }


}