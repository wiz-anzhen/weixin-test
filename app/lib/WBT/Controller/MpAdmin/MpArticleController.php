<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpAdmin;
use MP\Model\Mp\MpArticle;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\WxUserLevel;
use MP\Model\Mp\Community;
use MP\Model\Mp\MpArticleDailyTraffic;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\MpArticleBusiness;
use WBT\Controller\CommunityControllerBase;
use MP\Model\Mp\ArticleComment;

class MpArticleController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "article";
        parent::_init();
    }
    public function listAction() {
        $paging = []; // 先初始化为空
        $outputColumns = Community::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);


        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        if (!isset($paging[Database::KW_SQL_PAGE_INDEX])) $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        if (!isset($paging[Database::KW_SQL_ROWS_PER_PAGE])) $paging[Database::KW_SQL_ROWS_PER_PAGE] = Database::DEFAULT_ROWS_PER_PAGE;
        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $mpUserID = $this->_request->get( MpUser::MP_USER_ID );

        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);

        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userId = UserBusiness::getLoginUser()->getUserID() );
        $title= $this->_request->get('title');
        $this -> _view -> set("title",$title);
        $tag_name    = $this->_request->get( 'tag_name' );
        $this -> _view -> set("tag_name",$tag_name);
        $count = MpAdmin::fetchCount( [ MpAdmin::USERNAME => UserBusiness::getLoginUsername() ] );
        if ($count > 0) $this->_view->set( 'is_admin', TRUE );
        $tagGroup = MpArticle::fetchColumn(MpArticle::TAG,[MpArticle::COMMUNITY_ID => $communityId]);

        $tagNewGroup = array();
        foreach($tagGroup as $tagkey => $tagvalue)
        {
            if(!empty($tagvalue))
            {
                $pie = explode(",",$tagvalue);
                foreach($pie as $v)
                {
                    if(!strict_in_array($v,$tagNewGroup))
                    {
                        $tagNewGroup[] = $v;
                    }

                }
            }
        }
        $this->_view->set('group', $tagNewGroup);

        $outputColumns = MpArticle::s_metadata()->getFilterOptions();
        $ranking       = [ MpArticle::LAST_MODIFY_TIME => true];
        $condition = [MpArticle::COMMUNITY_ID => $communityId,MpArticle::MP_USER_ID => $mpUserID];
        if(!empty($title))
        {
            $expr = " title like '%$title%'";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        if(!empty($tag_name))
        {
            $expr = " tag like '%$tag_name%'";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        $data          = MpArticleBusiness::getList($condition , $paging, $ranking,$outputColumns );
        $power = $this->checkChangePower("article_rw","article_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('article_rw', $checkReadPower);
        $shownColumns        = [ MpArticle::TITLE  =>
                                  [
                                     Table::COLUMN_TITLE => '标题/分享摘要',
                                      Table::COLUMN_CELL_STYLE => 'width:15%',
                                     Table::COLUMN_FUNCTION => function (array $row)
                                         {
                                             $title = "标题:<br>".$row[MpArticle::TITLE];
                                             $desc = "分享摘要:<br>".$row[MpArticle::SHARE_DESC];
                                             return $title."<br><br>".$desc;
                                         }
                                  ],

                                 MpArticle::CONTENT       =>
                                 [
                                     Table::COLUMN_TITLE    => '内容',
                                     Table::COLUMN_FUNCTION =>
                                     function (array $row)use($checkReadPower)
                                     {
                                           return new Link('查看内容',"/article/{$row['mp_article_id']}?mp_user_id={$row['mp_user_id']}&community_id={$row['community_id']}&check_read_power={$checkReadPower}", ['target' => '_blank']);
                                     },
                                 ],

                                 MpArticle::SHOW_LIKE     => [ Table::COLUMN_TITLE => '评价',
                                                               Table::COLUMN_FUNCTION => function (array $row) {
                                                                       return $row[MpArticle::SHOW_LIKE] == 1 ? '是' : '否';
                                                                   }, ],
                                 MpArticle::USER_LEVEL    => [ Table::COLUMN_TITLE => '用户等级',
                                                               Table::COLUMN_FUNCTION => function ( array $row )
                                                                   {
                                                                       $ret       = '';
                                                                       $allLevels = WxUserLevel::getDictionary();
                                                                       $types    = explode( ',', $row[MpArticle::USER_LEVEL] );
                                                                       foreach ($types as $type)
                                                                       {
                                                                           if (array_key_exists( $type, $allLevels ))
                                                                           {
                                                                               $ret .= WxUserLevel::getDisplayName( $type ) . '<br/>';
                                                                           }
                                                                       }

                                                                       return $ret;
                                                                   } ],
                                 MpArticle::LAST_MODIFY_TIME,
                                 MpArticle::LAST_MODIFY_AUTHOR,
                                 MpArticle::TAG           => [
                                     Table::COLUMN_FUNCTION => function(array $row)
                                     {
                                         return str_replace('"', '', $row[MpArticle::TAG]);
                                     } ],
                                 "Pv合计" => [
                                     Table::COLUMN_TITLE => '点击量合计',
                                     Table::COLUMN_FUNCTION => function(array $row) {
                                             $pv = MpArticleDailyTraffic::fetchColumn([MpArticleDailyTraffic::PV],[MpArticleDailyTraffic::MP_ARTICLE_ID => $row[MpArticleDailyTraffic::MP_ARTICLE_ID]]);
                                             $pv = array_sum($pv);
                                             if(!isset($pv))
                                             {
                                                 $pv = 0;
                                             }
                                             return $pv;
                                         }
                                 ],
                                 "当天Pv" => [
                                     Table::COLUMN_TITLE => '当天点击量',
                                     Table::COLUMN_FUNCTION => function(array $row) {
                                             $currentDate = get_current_ymd();
                                             $mpArticleDailyTraffic = new MpArticleDailyTraffic([MpArticleDailyTraffic::MP_ARTICLE_ID => $row[MpArticleDailyTraffic::MP_ARTICLE_ID],MpArticleDailyTraffic::YMD => $currentDate]);
                                             $pv = $mpArticleDailyTraffic->getPv();
                                             if(!isset($pv))
                                             {
                                                 $pv = 0;
                                             }
                                             return $pv;
                                         }
                                 ],
                         Table::COLUMN_OPERATIONS =>
                         [
                             Table::COLUMN_TITLE => "操作",
                             Table::COLUMN_CELL_STYLE => 'width:10%',
                             Table::COLUMN_FUNCTION => function (array $row)
                                 {
                                     $communityID = $row[MpArticle::COMMUNITY_ID];
                                     $mpUserID = $row[MpArticle::MP_USER_ID];
                                     $mpArticleID = $row[MpArticle::MP_ARTICLE_ID];
                                     $readComment =  new Link("查看评价", "/mp_admin/mp_article/read_comment?mp_article_id={$mpArticleID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                                     return $readComment;
                                 }
                         ]
        ];
        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS]    = [
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_CELL_STYLE => 'width:10%',
                Table::COLUMN_FUNCTION => function(array $row)use($power)
                    {
                        $communityID = $row[MpArticle::COMMUNITY_ID];
                        $mpUserID = $row[MpArticle::MP_USER_ID];
                        $mpArticleID = $row[MpArticle::MP_ARTICLE_ID];
                        $update =  new Link('修改', "/mp_admin/mp_article/edit?mp_article_id={$mpArticleID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                        $delete =  new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function(){javascript:wbtAPI.call('../fcrm/mp_article/remove?mp_article_id={$mpArticleID}&mp_user_id={$mpUserID}', null, function(){bluefinBH.showInfo('移除成功',function(){location.reload();});});});");
                        $userLevel =  new Link('会员等级', "javascript:bluefinBH.ajaxDialog('/mp_admin/mp_article_dialog/edit_user_level?wx_user_id={{this.wx_user_id}}&mp_user_id={$mpUserID}&mp_article_id={$mpArticleID}&community_id={$communityID}');");
                        $readComment =  new Link("查看评价", "/mp_admin/mp_article/read_comment?mp_article_id={$mpArticleID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                        $ret = $update."<br>".$userLevel."<br>".$readComment;
                        if($power["delete"])
                        {
                            $ret .= "<br>".$delete;
                        }
                        return $ret;
                    } ];
        }

        $table               = Table::fromDbData( $data, $outputColumns, MpArticle::MP_ARTICLE_ID, $paging,
            $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = FALSE;
        $this->_view->set( 'table', $table );
    }

    public function addAction() {
        $mpUserId    = $this->_request->get( MpUser::MP_USER_ID );
        $communityId = $this->_request->get( 'community_id');
        $mpArticleId = MpArticleBusiness::insert( $communityId,$mpUserId );


        $path        = $this->_gateway->path( "mp_admin/mp_article/edit?mp_user_id={$mpUserId}&mp_article_id={$mpArticleId}&community_id={$communityId}" );
        $this->_gateway->redirect( $path );
    }

    //查看评论
    public function readCommentAction()
    {
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $mpArticleId = $this->_request->get( MpArticle::MP_ARTICLE_ID );

        $paging = []; // 先初始化为空
        $outputColumns = ArticleComment::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $ranking       = [ ArticleComment::ARTICLE_COMMENT_ID => true ];
        $articleComment = ArticleComment::fetchRowsWithCount(['*'],[ArticleComment::MP_ARTICLE_ID => $mpArticleId], null, $ranking, $paging, $outputColumns);

        $shownColumns =
            [
                "send_user" =>
               [
                Table::COLUMN_TITLE => "发送用户",
                Table::COLUMN_CELL_STYLE => "width:10%",
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                      $wxUser = new WxUser([WxUser::WX_USER_ID => $row[ArticleComment::WX_USER_ID]]);
                      $name = $wxUser->getNick();
                      return $name;
                    }
                ],
            ArticleComment::_CREATED_AT => [
                Table::COLUMN_TITLE => "发送时间",
                Table::COLUMN_CELL_STYLE => "width:15%"
            ],
            ArticleComment::COMMENT => [Table::COLUMN_TITLE => "评论内容"],
            ];

        $table               = Table::fromDbData( $articleComment, $outputColumns, ArticleComment::ARTICLE_COMMENT_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );


    }

    public function editAction() {
        $mpUserID = $this->_request->get( MpUser::MP_USER_ID );
        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userId = UserBusiness::getLoginUser()->getUserID() );
        $count = MpAdmin::fetchCount( [ MpAdmin::USERNAME => $this->_username ] );
        if ($count > 0) $this->_view->set( 'is_admin', TRUE );

        $mpArticleId = $this->_request->get( MpArticle::MP_ARTICLE_ID );
        $this->_view->set( 'mp_article_id', $mpArticleId );
        $mpArticle = new MpArticle([ MpArticle::MP_ARTICLE_ID => $mpArticleId, MpArticle::MP_USER_ID => $this->_mpUserID ]);
        $this->_view->set( 'title', $mpArticle->getTitle() );
        $this->_view->set( 'content', $mpArticle->getContent() );
        $this->_view->set( MpArticle::SHARE_DESC, $mpArticle->getShareDesc() );
        $this->_view->set( 'show_like', $mpArticle->getShowLike() );
        $this->_view->set( 'tag', str_replace('"', '', $mpArticle->getTag()) );
        $this->_view->set( MpArticle::REDIRECT_URL, $mpArticle->getRedirectUrl() );
        $this->_view->set( MpArticle::REDIRECT, $mpArticle->getRedirect() );
    }

    public function trafficAction()
    {
        $mpUserID = $this->_request->get( MpUser::MP_USER_ID );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $communityId = $this->_request->get( MpArticleDailyTraffic::COMMUNITY_ID );
        $communityName = $this->_request->get( 'community_name' );
        $this->_view->set( "community_name", $communityName);
        $this->_view->set( "community_id", $communityId);

        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userId = UserBusiness::getLoginUser()->getUserID() );
        $this->_view->set( 'community_id', $communityId);

        $outputColumns = MpArticleDailyTraffic::s_metadata()->getFilterOptions();
        $paging = []; // 先初始化为空
        $ranking       = [ MpArticleDailyTraffic::YMD => true,MpArticleDailyTraffic::MP_ARTICLE_ID ];
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 100;

        $condition     = [ MpArticleDailyTraffic::COMMUNITY_ID => $communityId ,];
        $data          = MpArticleBusiness::getListTraffic( $condition, $paging, $ranking,$outputColumns );

        $shownColumns = [
            MpArticleDailyTraffic::MP_ARTICLE_ID =>
                [
                    Table::COLUMN_TITLE => '素材管理目录名称',
                    Table::COLUMN_FUNCTION => function(array $row) {
                            $mpArticle = new MpArticle([MpArticle::MP_ARTICLE_ID => $row[MpArticleDailyTraffic::MP_ARTICLE_ID]]);
                            $mpArticleName = $mpArticle->getTitle();
                            return $mpArticleName;
                        } ],
            MpArticleDailyTraffic::YMD,
            MpArticleDailyTraffic::PV =>
                [
                    Table::COLUMN_FUNCTION => function(array $row) {
                            if(!isset($row[MpArticleDailyTraffic::PV]))
                            {
                                return '0';
                            }
                            else
                            {
                                return $row[MpArticleDailyTraffic::PV];
                            }

                        } ],

        ];

        $table               = Table::fromDbData( $data, $outputColumns,
            MpArticleDailyTraffic::MP_ARTICLE_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }
}