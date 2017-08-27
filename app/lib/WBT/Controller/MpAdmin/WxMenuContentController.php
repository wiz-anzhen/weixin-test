<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpRule;
use WBT\Business\Weixin\MpRuleNewsItemBusiness;
use WBT\Controller\WBTControllerBase;
use MP\Model\Mp\MpRuleNewsItem;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxMenu;
use MP\Model\Mp\WxSubMenu;

class WxMenuContentController extends WBTControllerBase
{
    public function indexAction() {
        $wxSubMenuID = $this->_request->get('wx_sub_menu_id');
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserID]);
        $wxSubMenu = new WxSubMenu([WxSubMenu::WX_SUB_MENU_ID => $wxSubMenuID, WxSubMenu::MP_USER_ID => $this->_mpUserID]);
        $wxMenu = new WxMenu([WxMenu::WX_MENU_ID => $wxSubMenu->getWxMenuID(), WxMenu::MP_USER_ID => $this->_mpUserID]);
        $mpUserID = $this->_mpUserID;
        $this->_view->set( WxSubMenu::MP_USER_ID, $this->_mpUserID );
        $this->_view->set( WxSubMenu::WX_SUB_MENU_ID, $wxSubMenuID );
        $this->_view->set( WxMenu::WX_MENU_ID, $wxSubMenu->getWxMenuID());
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'wx_menu_name', $wxMenu->getName() );
        $this->_view->set( 'wx_sub_menu_name', $wxSubMenu->getWxMenuName() );
        $paging = array();
        $outputColumns = MpRuleNewsItem::s_metadata()->getFilterOptions();
        $ranking = [MpRuleNewsItem::SORT_NO];
        $data = MpRuleNewsItemBusiness::getMpRuleNewsItemList([/* MpUser::MP_USER_ID => $mpUserID, */ MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID => explode( ',', $wxSubMenu->getContentValue() ) ], $paging, $ranking, $outputColumns );

        $showColumns = [
            MpRuleNewsItem::TITLE       => [Table::COLUMN_TITLE => '标题',
                                            Table::COLUMN_CELL_STYLE => 'width:11%'],
            MpRuleNewsItem::DESCRIPTION => [Table::COLUMN_TITLE => '摘要',
                                            Table::COLUMN_CELL_STYLE => 'width:20%'],
            MpRuleNewsItem::PIC_URL     => [Table::COLUMN_TITLE      => '图片',
                                            Table::COLUMN_CELL_STYLE => 'width:20%',
                                            Table::COLUMN_FUNCTION   => function ( array $row ) {
                                                return "<a href=\"{$row['pic_url']}\" target=\"_blank\"><img style='width:200px' src=\"{$row['pic_url']}\" alt=\"无图片\" /></a>";
                                            } ],
            MpRuleNewsItem::URL         => [Table::COLUMN_TITLE      => '跳转链接',
                                            Table::COLUMN_CELL_STYLE => 'width:14%',
                                            Table::COLUMN_FUNCTION   => function ( array $row ) {
                                             /*       if(empty($row['top_dir_no']))
                                                    {
                                                        $ret = "<a href=\"{$row['url']}\" target='_blank'>点击查看</a>";
                                                    }else{
                                                        $ret="<a href=\"http://www.hao123.com\" target='_blank'>点击查看</a>";
                                                    }*/
                                                $ret = "<a href=\"{$row['url']}\" target='_blank'>点击查看</a>";

                                                return $ret;
                                            } ],
            MpRuleNewsItem::TOP_DIR_NO =>[Table::COLUMN_TITLE => '一级目录编号',
                Table::COLUMN_CELL_STYLE => 'width:15%',
            ],
            MpRuleNewsItem::SORT_NO     => [Table::COLUMN_TITLE      => '排序',],
            Table::COLUMN_OPERATIONS    => [Table::COLUMN_TITLE      => '操作',
                                            Table::COLUMN_CELL_STYLE => 'width:10%',
                                            Table::COLUMN_OPERATIONS => [
                                                new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/mp_rule_dialog/edit_news?mp_user_id={$mpUserID}&mp_rule_news_item_id={{this.mp_rule_news_item_id}}&wx_sub_menu_id={$wxSubMenuID}');", NULL),
                                                new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function(){javascript:wbtAPI.call('../fcrm/mp_rule/removeNewsForWx?mp_user_id={$mpUserID}&mp_rule_news_item_id={{this.mp_rule_news_item_id}}&wx_sub_menu_id={$wxSubMenuID}', null, function(){ bluefinBH.showInfo('移除成功', function() { location.reload(); }); }); })"), ] ]

        ];

        $table = Table::fromDbData($data, $outputColumns, WxSubMenu::SORT_NO, $paging, $showColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = TRUE;
        $this->_view->set('table', $table);
    }
}