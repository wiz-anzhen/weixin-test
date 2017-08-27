<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\CompanyAdmin;
use MP\Model\Mp\MpUser;

use WBT\Business\UserBusiness;
use WBT\Business\Weixin\CompanyAdminBusiness;
use WBT\Controller\CommunityControllerBase;
use MP\Model\Mp\CompanyAdminPowerType;
class CompanyAdminController extends CommunityControllerBase
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
            $this->_redirectToErrorPage("您没有权限访问此页面。");
        }
        $mpUserID = $this->_request->get( MpUser::MP_USER_ID );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userId = UserBusiness::getLoginUser()->getUserID() );


        $paging = []; // 先初始化为空
        $outputColumns = CompanyAdmin::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $ranking       = [ CompanyAdmin::COMPANY_ADMIN_ID ];
        $condition= [CommunityAdmin::MP_USER_ID => $mpUserID] ;

        $data          = CompanyAdminBusiness::getCompanyAdminList( $condition, $paging, $ranking, $outputColumns );

        $shownColumns = [
            CompanyAdmin::USERNAME => [Table::COLUMN_TITLE => '管理员邮箱'],
            CompanyAdmin::MP_USER_ID => [Table::COLUMN_TITLE => '公共账号ID'],
            CompanyAdmin::MP_NAME => [Table::COLUMN_TITLE => '公共账号名称'],
            CompanyAdmin::POWER => [ Table::COLUMN_TITLE  => '权限',
                Table::COLUMN_FUNCTION => function(array $row)
                    {
                        $ret = '';
                        $allPowers = CompanyAdminPowerType::getDictionary();
                        $powers = explode(',',$row[CompanyAdmin::POWER]);
                        foreach($powers as $power)
                        {
                            if(array_key_exists($power,$allPowers))
                            {
                                $ret.=CompanyAdminPowerType::getDisplayName($power).'<br/>';
                            }
                        }
                        return $ret;
                    }],
            CommunityAdmin::COMMENT => [Table::COLUMN_TITLE => '备注'],
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_CELL_STYLE => 'width:15%',
                Table::COLUMN_OPERATIONS => [
                    new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/company_admin_dialog/update?mp_user_id={{this.mp_user_id}}&company_admin_id={{ this.company_admin_id }}');"),
                    new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，目录下所有的内容都将丢失，且无法回复。<br/><br/>确定更要删除吗？', function(){javascript:wbtAPI.call('../fcrm/company_admin/delete?company_admin_id={{ this.company_admin_id }}', null, function(){bluefinBH.showInfo('移除成功',function(){location.reload();});});});"),
                    new Link('修改密码', "javascript:bluefinBH.ajaxDialog('/mp_admin/company_admin_dialog/update_password?mp_user_id={{this.mp_user_id}}&company_admin_id={{ this.company_admin_id }}');"),
                ], ], ];

        $table  = Table::fromDbData( $data, $outputColumns,  CompanyAdmin::COMPANY_ADMIN_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );
    }


}