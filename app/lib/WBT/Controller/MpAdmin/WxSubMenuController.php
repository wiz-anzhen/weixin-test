<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxMenu;
use MP\Model\Mp\WxMenuContentType;
use MP\Model\Mp\WxSubMenu;
use WBT\Business\Weixin\WxSubMenuBusiness;
use WBT\Controller\WBTControllerBase;
use MP\Model\Mp\AccessAuthorityType;

class WxSubMenuController extends WBTControllerBase
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

        $wxMenuID = $this->_request->get( 'wx_menu_id' );
        $this->_view->set( WxSubMenu::MP_USER_ID, $this->_mpUserID );
        $this->_view->set( WxSubMenu::WX_MENU_ID, $wxMenuID );
        $wxMenu = new WxMenu([WxMenu::WX_MENU_ID => $wxMenuID, WxMenu::MP_USER_ID => $this->_mpUserID]);
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'wx_menu_name', $wxMenu->getName() );
        $paging = array();
        $outputColumns = WxSubMenu::s_metadata()->getFilterOptions();
        $ranking = [WxSubMenu::SORT_NO];
        $data = WxSubMenuBusiness::getWxSubMenuList([WxSubMenu::MP_USER_ID => $this->_mpUserID, WxSubMenu::WX_MENU_ID => $wxMenuID], $paging, $ranking, $outputColumns);

        $showColumns = [
            WxSubMenu::WX_MENU_NAME => [Table::COLUMN_TITLE => "名称"],
            WxSubMenu::WX_MENU_TYPE => [Table::COLUMN_TITLE => '类型'],
            WxSubMenu::CONTENT_TYPE => [Table::COLUMN_TITLE => '微信菜单内容类型'],
            WxSubMenu::CONTENT_VALUE => [Table::COLUMN_TITLE => '微信菜单内容',
                                         Table::COLUMN_FUNCTION => function (array $row)
                                         {
                                             if($row[WxSubMenu::CONTENT_TYPE] == WxMenuContentType::CUSTOM_NEWS)
                                             {
                                                 $url = sprintf("/mp_admin/wx_menu_content/index?mp_user_id=%s&wx_sub_menu_id=%s", $row[WxSubMenu::MP_USER_ID], $row[WxSubMenu::WX_SUB_MENU_ID]);
                                                 $link = new Link("编辑",$url);
                                                 return $link;
                                             }
                                             return $row[WxSubMenu::CONTENT_VALUE];
                                         }
            ],
            WxSubMenu::URL => [Table::COLUMN_TITLE => 'view网址'],
            WxSubMenu::SORT_NO => [Table::COLUMN_TITLE => "排序号"],
            WxSubMenu::ACCESS_AUTHORITY => [
            Table::COLUMN_FUNCTION => function(array $row){
                    return AccessAuthorityType::getDisplayName($row[WxSubMenu::ACCESS_AUTHORITY]);
            }],
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_OPERATIONS => [
                    new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/wx_sub_menu_dialog/edit?wx_sub_menu_id={{this.wx_sub_menu_id}}&mp_user_id={{this.mp_user_id}}');"),
                    new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/wx_sub_menu/remove?wx_sub_menu_id={{this.wx_sub_menu_id}}&mp_user_id={{this.mp_user_id}}', null, function(){bluefinBH.showInfo('移除成功', function() { location.reload(); }); }); })")
                ]
            ]
        ];

        $table = Table::fromDbData($data, $outputColumns, WxSubMenu::SORT_NO, $paging, $showColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = TRUE;
        $this->_view->set('table', $table);
    }
}