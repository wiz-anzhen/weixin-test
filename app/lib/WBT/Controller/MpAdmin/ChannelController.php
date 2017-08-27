<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\ArticleType;
use MP\Model\Mp\Channel;
use MP\Model\Mp\ChannelArticle;
use MP\Model\Mp\Community;
use MP\Model\Mp\MpAdmin;
use MP\Model\Mp\MpUser;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\ChannelBusiness;
use WBT\Controller\CommunityControllerBase;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;



class ChannelController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "channel";

        parent::_init();
    }

    public function listAction() {

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX])) {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50; // Database::DEFAULT_ROWS_PER_PAGE;
        Database::extractQueryCondition( $condition, $outputColumns, $paging, $ranking );
        $mpUserID = $this->_request->get( MpUser::MP_USER_ID );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $communityId = $this->_request->get( Channel::COMMUNITY_ID );
        $this->_view->set( 'community_id', $communityId);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );


      //  $paging = array();
        $outputColumns = Channel::s_metadata()->getFilterOptions();
        $ranking       = [ Channel::CHANNEL_ID ];
        $data          = ChannelBusiness::getChannelList( [ Channel::COMMUNITY_ID => $communityId ], $paging,
            $ranking, $outputColumns );
        $power = $this->checkChangePower("channel_rw","channel_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('channel_rw', $checkReadPower);
        $shownColumns = [ Channel::CHANNEL_ID      => [ Table::COLUMN_TITLE => '频道 ID', ],
                          Channel::TITLE           => [ Table::COLUMN_TITLE => '标题', ],
                          Table::COLUMN_OPERATIONS =>
                              [ Table::COLUMN_CELL_STYLE => 'width:8%',
                                Table::COLUMN_TITLE => "操作",
                                Table::COLUMN_FUNCTION => function (array $row)use($power)
                                    {
                                        $channelID = $row[Channel::CHANNEL_ID];
                                        $communityID = $row[Channel::COMMUNITY_ID];
                                        $mpUserID = $row[Channel::MP_USER_ID];
                                        $article =  new Link("文章管理", "/mp_admin/channel/article?mp_user_id={$mpUserID}&channel_id={$channelID}&community_id={$communityID}");
                                        $address1 =   new Link("频道地址", "/wx_user/channel/channel?channel_id={$channelID}&community_id={$communityID}", ['target' => '_blank']);
                                        $address2 = new Link("频道地址2", "/wx_user/channel/channel?channel_id={$channelID}&view=2&community_id={$communityID}", ['target' => '_blank']);
                                        $ret =$article."<br>".$address1."<br>".$address2;
                                        return $ret;
                                    } ], ];
        if($power["update"] or $power["delete"])
        {
            $shownColumns[Table::COLUMN_OPERATIONS] =
                [ Table::COLUMN_CELL_STYLE => 'width:23%',
                  Table::COLUMN_TITLE => "操作",
                  Table::COLUMN_FUNCTION => function (array $row)use($power)
                      {
                          $channelID = $row[Channel::CHANNEL_ID];
                          $communityID = $row[Channel::COMMUNITY_ID];
                          $mpUserID = $row[Channel::MP_USER_ID];
                          $update =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/channel_dialog/edit?channel_id={$channelID}&mp_user_id={$mpUserID}&community_id={$communityID}');");
                          $delete = new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/channel/remove?channel_id={$channelID}&mp_user_id={$mpUserID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('移除成功', function() { location.reload(); }); }); })");
                          $article =  new Link("文章管理", "/mp_admin/channel/article?mp_user_id={$mpUserID}&channel_id={$channelID}&community_id={$communityID}");
                          $address1 =   new Link("频道地址", "/wx_user/channel/channel?channel_id={$channelID}&community_id={$communityID}", ['target' => '_blank']);
                          $address2 = new Link("频道地址2", "/wx_user/channel/channel?channel_id={$channelID}&view=2&community_id={$communityID}", ['target' => '_blank']);
                          $ret = $update."<br>".$article."<br>".$address1."<br>".$address2;
                          if($power["delete"])
                          {
                              $ret .= "<br>".$delete;
                          }
                          return $ret;
                      }
                   ];
        }


        $table               = Table::fromDbData( $data, $outputColumns,
            Channel::CHANNEL_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = FALSE;
        $this->_view->set( 'table', $table );
    }

    public function articleAction()
    {
        $mpUserID  = $mp_user_id = $this->_request->get( MpUser::MP_USER_ID );
        $channelId = $this->_request->get( Channel::CHANNEL_ID );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );



        $this->_view->set( Channel::CHANNEL_ID, $channelId );

        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $channel = new Channel([Channel::CHANNEL_ID => $channelId]);
        $title = $channel->getTitle();
        $this->_view->set( Channel::TITLE, $title );

        $condition = $this->_request->getQueryParams();
        Database::extractQueryCondition( $condition, $outputColumns, $paging, $ranking );
        $paging = null;


        $outputColumns = ChannelArticle::s_metadata()->getFilterOptions();
        $ranking       = [ ChannelArticle::KEEP_TOP => TRUE, ChannelArticle::RELEASE_DATE => true ];
        $condition = [Channel::COMMUNITY_ID => $communityId,Channel::CHANNEL_ID => $channelId];
        $data          = ChannelBusiness::getChannelArticleList( $condition, $ranking, $paging, $outputColumns );
        $power = $this->checkChangePower("channel_rw","channel_d");
        $this->_view->set('channel_rw', $power["update"]);
        $shownColumns = [ ChannelArticle::CHANNEL_ARTICLE_ID => [ Table::COLUMN_TITLE => 'ID', ],
                          ChannelArticle::ARTICLE_TITLE      => [ Table::COLUMN_TITLE => '文章标题', ],
                          ChannelArticle::SHARE_URL          => [ Table::COLUMN_TITLE => '分享图片',
                                                                  Table::COLUMN_CELL_STYLE => 'width: 10%',
                                                                  Table::COLUMN_FUNCTION => function(array $row){
                                                                          return "<img src=\"{$row[ChannelArticle::SHARE_URL]}\"/>";
                                                                      } ],
                          ChannelArticle::ARTICLE_DESC       => [ Table::COLUMN_TITLE => '文章摘要',
                                                                  Table::COLUMN_CELL_STYLE => 'width: 40%', ],
                         ChannelArticle::ARTICLE_TYPE => [ Table::COLUMN_TITLE => '文章类型',],
                          ChannelArticle::ARTICLE_URL        => [ Table::COLUMN_TITLE => '文章链接',
                                                                  Table::COLUMN_FUNCTION => function(array $row){
                                                                          $ret = '';
                                                                          if ($row[ChannelArticle::ARTICLE_TYPE] == ArticleType::ARTICLE_THIRD_PARTY )
                                                                          {
                                                                              $ret = new Link('点击查看', $row[ChannelArticle::ARTICLE_URL], ['target' => '_blank']);
                                                                          }

                                                                          if($row[ChannelArticle::ARTICLE_TYPE] == ArticleType::ARTICLE_OURS)
                                                                          {
                                                                              $ret = new Link('点击查看', "/wx_user/channel/ours?mp_user_id={$row['mp_user_id']}&channel_id={$row['channel_id']}&community_id={$row['community_id']}&channel_article_id={$row['channel_article_id']}", ['target' => '_blank']);
                                                                          }
                                                                          return $ret;
                                                                      } ],
                          ChannelArticle::RELEASE_DATE => [ Table::COLUMN_TITLE      => '发布日期',
                                                            Table::COLUMN_CELL_STYLE => 'width: 13%', ],
                          ChannelArticle::KEEP_TOP     => [ Table::COLUMN_FUNCTION => function ( array $row )
                              {
                                  return $row[ChannelArticle::KEEP_TOP] == 1 ? '是' : '&nbsp;';
                              } ],
                           ];
        if($power["update"] or $power["delete"])
        {
            $shownColumns[Table::COLUMN_OPERATIONS ] =
                [ Table::COLUMN_TITLE      => '操作',
                  Table::COLUMN_CELL_STYLE => 'width: 8%',
                  Table::COLUMN_FUNCTION => function (array $row)use($power)
                      {
                         $communityID = $row[ChannelArticle::COMMUNITY_ID];
                         $mpUserID = $row[ChannelArticle::MP_USER_ID];
                         $channelArticleID = $row[ChannelArticle::CHANNEL_ARTICLE_ID];
                         $channelID = $row[ChannelArticle::CHANNEL_ID];
                         $update = new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/channel_dialog/edit_article?mp_user_id={$mpUserID}&channel_article_id={$channelArticleID}&community_id={$communityID}');");
                          $delete =  new Link('删除', "javascript:bluefinBH.confirm('将删除，确定吗？', function(){javascript:wbtAPI.call('../fcrm/channel/remove_article?channel_article_id={$channelArticleID}&channel_id={$channelID}&mp_user_id={$mpUserID}&community_id={$communityID}', null, function(){ bluefinBH.showInfo('移除成功', function() { location.reload(); }); }); })");
                          $ret = $update;
                          if($power["delete"])
                          {
                              $ret .= "<br>".$delete;
                          }
                          return $ret;
                      }  ];
        }


        $table               = Table::fromDbData( $data, $outputColumns, ChannelArticle::CHANNEL_ARTICLE_ID, null,
            $shownColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = FALSE;
        $this->_view->set( 'table', $table );

    }
}