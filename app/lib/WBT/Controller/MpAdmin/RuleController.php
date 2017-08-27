<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpRule;
use MP\Model\Mp\MpUser;
use WBT\Business\Weixin\MpRuleBusiness;
use WBT\Controller\WBTControllerBase;
use MP\Model\Mp\MpAdmin;
use WBT\Business\UserBusiness;
use MP\Model\Mp\MpRuleNewsItem;
use WBT\Business\Weixin\MpRuleNewsItemBusiness;

class RuleController extends WBTControllerBase
{
    public function indexAction() {
        $this->_gateway->redirect( $this->_gateway->path( 'mp_admin/rule/list' ) );
    }

    public function listAction() {
        $condition = $this->_request->getQueryParams();
        Database::extractQueryCondition( $condition, $outputColumns, $paging, $ranking );

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX])) {
            ($paging[Database::KW_SQL_PAGE_INDEX] = 1);
        }
        if (!isset($paging[Database::KW_SQL_ROWS_PER_PAGE])) {
            ($paging[Database::KW_SQL_ROWS_PER_PAGE] = Database::DEFAULT_ROWS_PER_PAGE);
        }

        $mpUserID = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );
        $count = MpAdmin::fetchCount( [ MpAdmin::USERNAME => UserBusiness::getLoginUsername() ] );
        if ($count > 0) {
            $this->_view->set( 'is_admin', TRUE );
        }

        $outputColumns = MpRule::s_metadata()->getFilterOptions();
        $ranking       = array( MpRule::_CREATED_AT => TRUE );
        $data          = MpRuleBusiness::getMpRuleList( [ MpUser::MP_USER_ID => $mpUserID ], $paging, $ranking,
            $outputColumns );
        $shownColumns  = [ 'name'         => [ Table::COLUMN_TITLE      => '规则名称',
                                               Table::COLUMN_CELL_STYLE => 'width:15%', ],
                           'keyword'      => [ Table::COLUMN_TITLE      => '关键词',
                                               Table::COLUMN_CELL_STYLE => 'width:15%', ],
                           'content'      => [ Table::COLUMN_TITLE      => '内容',
                                               Table::COLUMN_CELL_STYLE => 'width:35%',
                                               Table::COLUMN_FUNCTION   => function ( array $row ) {
                                                   $msgType = '';
                                                   switch ($row[MpRule::CONTENT_TYPE]) {
                                                       case 'text':
                                                           $msgType = nl2br( $row['content'] );
                                                           break;
                                                       case 'news':
                                                           $msgType = "<a href=\"/mp_admin/rule/news?mp_user_id={$row['mp_user_id']}&mp_rule_id={$row['mp_rule_id']}\" title=\"查看详情\">点击查看详情</a>";
                                                           break;
                                                       default:
                                                           $msgType = '未知类型';
                                                           break;
                                                   }

                                                   return $msgType;
                                               }, ],
                           'content_type' => [ Table::COLUMN_TITLE      => '消息类型',
                                               Table::COLUMN_CELL_STYLE => 'width:9%', ],
                           'operations'   => [ Table::COLUMN_TITLE      => '操作',
                                               Table::COLUMN_CELL_STYLE => 'width:26%',
                                               Table::COLUMN_FUNCTION   => function ( array $row ) {
                                                   $ret = (new Link('编辑基本信息', "javascript:bluefinBH.ajaxDialog('/mp_admin/mp_rule_dialog/edit/?mp_rule_id={$row['mp_rule_id']}&mp_user_id={$row['mp_user_id']}');", NULL))
                                                       . ' ' . (new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function(){javascript:wbtAPI.call('../fcrm/mp_rule/remove?mp_rule_id={$row['mp_rule_id']}&mp_user_id={$row['mp_user_id']}', null, function(){ bluefinBH.showInfo('移除成功', function() { location.reload(); }); }); })"));
                                                   if ($row['content_type'] == 'news')
                                                       $ret .= ' ' . "<a href=\"/mp_admin/rule/news?mp_user_id={$row['mp_user_id']}&mp_rule_id={$row['mp_rule_id']}\" title=\"编辑图文消息\">编辑图文消息</a>";

                                                   return $ret;
                                               } ], ];

        $table               = Table::fromDbData( $data, $outputColumns, MpRule::MP_RULE_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = TRUE;
        $this->_view->set( 'table', $table );
    }

    public function newsAction() {
        $mpRuleId = $this->_request->get( 'mp_rule_id' );
        $mpUserID = $this->_request->get( 'mp_user_id' );
        if (MpRule::fetchCount( [ 'mp_rule_id' => $mpRuleId,
                                'mp_user_id'   => $mpUserID ] ) == 0
        )
        {
            log_error( "[userID:{$this->_userID}] 没有权限访问该页面" );
            $this->_redirectToErrorPage( '您没有权限访问该页面' );
        }
        $condition = $this->_request->getQueryParams();
        Database::extractQueryCondition( $condition, $outputColumns, $paging, $ranking );

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX])) {
            ($paging[Database::KW_SQL_PAGE_INDEX] = 1);
        }
        if (!isset($paging[Database::KW_SQL_ROWS_PER_PAGE])) {
            ($paging[Database::KW_SQL_ROWS_PER_PAGE] = Database::DEFAULT_ROWS_PER_PAGE);
        }

        $this->_view->set( 'mp_user_id', $mpUserID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );
        $count = MpAdmin::fetchCount( [ MpAdmin::USERNAME => UserBusiness::getLoginUsername() ] );
        if ($count > 0) {
            $this->_view->set( 'is_admin', TRUE );
        }

        $this->_view->set( 'mp_rule_id', $mpRuleId );
        $mpRule = new MpRule([MpRule::MP_RULE_ID => $mpRuleId, MpRule::MP_USER_ID => $this->_mpUserID]);
        if ($mpRule->isEmpty() || ($mpRule->getContentType() != 'news')) {
            //$this->_showErrorMessage( '错误的页面' );
        }
        $this->_view->set( 'rule_name', $mpRule->getName() );
        $this->_view->set( 'allow_new_item', count( explode( ',', $mpRule->getContent() ) ) < 10 );

        $outputColumns = MpRuleNewsItem::s_metadata()->getFilterOptions();
        $ranking       = array( MpRule::_CREATED_AT => TRUE );
        $data          = MpRuleNewsItemBusiness::getMpRuleNewsItemList( [ MpUser::MP_USER_ID                 => $mpUserID,
                                                                        MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID => explode( ',',
                                                                            $mpRule->getContent() ) ], $paging,
            $ranking, $outputColumns );
        $shownColumns  = [ 'title'       => [ Table::COLUMN_TITLE      => '标题',
                                              Table::COLUMN_CELL_STYLE => 'width:15%', ],
                           'description' => [ Table::COLUMN_TITLE      => '描述',
                                              Table::COLUMN_CELL_STYLE => 'width:25%', ],
                           'pic_url'     => [ Table::COLUMN_TITLE      => '图片',
                                              Table::COLUMN_CELL_STYLE => 'width:10%',
                                              Table::COLUMN_FUNCTION   => function ( array $row ) {
                                                  return "<a href=\"{$row['pic_url']}\" target=\"_blank\"><img src=\"{$row['pic_url']}\" alt=\"错误的图片地址\" /></a>";
                                              } ],
                           'url'         => [ Table::COLUMN_TITLE      => '跳转链接',
                                              Table::COLUMN_CELL_STYLE => 'width:25%',
                                              Table::COLUMN_FUNCTION   => function ( array $row ) {
                                                  $ret = "<a href=\"{$row['url']}\">";
                                                  for ($i = 0; $i < strlen( $row['url'] ); $i += 35) {
                                                      if ($i > 0)
                                                          $ret .= '<br/>';
                                                      $ret .= substr( $row['url'], $i, 35 );
                                                  }
                                                  $ret .= '</a>';

                                                  return $ret;
                                              } ],
                           'operations'  => [ Table::COLUMN_TITLE      => '连接',
                                              Table::COLUMN_CELL_STYLE => 'width:20%',
                                              Table::COLUMN_OPERATIONS => [ new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/mp_rule_dialog/edit_news?mp_user_id={$mpUserID}&mp_rule_news_item_id={{this.mp_rule_news_item_id}}');", NULL),
                                                                            new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function(){javascript:wbtAPI.call('../fcrm/mp_rule/remove_news?mp_user_id={$mpUserID}&mp_rule_news_item_id={{this.mp_rule_news_item_id}}&mp_rule_id={$mpRuleId}', null, function(){ bluefinBH.showInfo('移除成功', function() { location.reload(); }); }); })"), ] ], ];

        $table               = Table::fromDbData( $data, $outputColumns, MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID, $paging,
            $shownColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = TRUE;
        $this->_view->set( 'table', $table );
    }
}