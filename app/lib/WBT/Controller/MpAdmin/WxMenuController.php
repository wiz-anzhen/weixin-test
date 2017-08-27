<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxMenu;
use WBT\Business\Weixin\WxMenuBusiness;
use WBT\Controller\WBTControllerBase;

class WxMenuController extends WBTControllerBase
{

    public function indexAction()
    {
        if(!$this->_isMpAdmin)
        {
            if(!$this->_isCompanyAdmin)
            {
                $this->_redirectToErrorPage("你没有权限访问此页面。");
            }
        }

        $this->_view->set( MpUser::MP_USER_ID, $this->_mpUserID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $paging = array();
        $outputColumns = array();
        $ranking = [WxMenu::SORT_NO];
        $data = WxMenuBusiness::getWxMenuList([MpUser::MP_USER_ID => $this->_mpUserID], $paging, $ranking, $outputColumns);

        $showColumns = [
            WxMenu::NAME => [Table::COLUMN_TITLE => "名称",
                             Table::COLUMN_FUNCTION => function(array $row){
                                 $url = new Link($row[WxMenu::NAME], "/mp_admin/wx_sub_menu/index?mp_user_id={$row['mp_user_id']}&wx_menu_id={$row['wx_menu_id']}");
                                 return $url;
                }
            ],
            WxMenu::SORT_NO => [Table::COLUMN_TITLE => "排序号"],
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_OPERATIONS => [
                    new Link('编辑', "javascript:bluefinBH.ajaxDialog('/mp_admin/wx_menu_dialog/edit?wx_menu_id={{this.wx_menu_id}}&mp_user_id={{this.mp_user_id}}');"),
                    new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/wx_menu/remove?wx_menu_id={{this.wx_menu_id}}&mp_user_id={{this.mp_user_id}}', null, function(){bluefinBH.showInfo('移除成功', function() { location.reload(); }); }); })"),
                    new Link("查看子菜单", "/mp_admin/wx_sub_menu/index?mp_user_id={{this.mp_user_id}}&wx_menu_id={{this.wx_menu_id}}")
                ]
            ]
        ];

        $table = Table::fromDbData($data, $outputColumns, WxMenu::SORT_NO, $paging, $showColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = TRUE;
        $this->_view->set('table', $table);
    }
}
