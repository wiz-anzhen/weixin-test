<?php

namespace WBT\Controller\Article;

use Common\Helper\BaseController;
use MP\Model\Mp\ArticleTagLog;
use MP\Model\Mp\MpArticle;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\MpUserConfig;
use MP\Model\Mp\MpUserConfigType;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\WxUserLevel;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\CarouselBusiness;
use WBT\Business\Weixin\MpArticleBusiness;
use WBT\Business\Weixin\WxUserBusiness;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\UrgentNoticeReadRecord;
use MP\Model\Mp\ChannelArticle;
use MP\Model\Mp\MpArticleDailyTraffic;
use MP\Model\Mp\ArticleComment;
use Bluefin\Data\Database;
use Bluefin\HTML\Table;
use WBT\Business\Weixin\CsChatBusiness;

class ArticleController extends BaseController
{
    // 用户扫描积分二维码后对应的链接后会触发此action
    // example url: http://canyin.weibotui.com/article/3c9a0423e856ccaa4decf9295be0fd03
    public function idAction()
    {
        $mpArticleId = $this->_request->getRouteParam( 'article_id' );
        $article = new MpArticle([MpArticle::MP_ARTICLE_ID => $mpArticleId]);
        $articleData = $article->data();
        $articleData[MpArticle::LAST_MODIFY_TIME] = substr($articleData[MpArticle::LAST_MODIFY_TIME],0,10);
        if(preg_match('/^0000/',$articleData[MpArticle::LAST_MODIFY_TIME] ))
        {
            $articleData[MpArticle::LAST_MODIFY_TIME] = date("Y-m-d");
        }
        $communityID = $article->getCommunityID();
        $mpUserId = $article->getMpUserID();
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $mpUserType = $mpUser->getMpUserType();
        $mpName = $mpUser->getMpName();
        $backUrl = base64_encode($this->_request->getFullRequestUri());
        $wxUserID='';
        if (strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger"))
        {
            if($mpUserType == 1)
            {
                $wxUserID = WxUserBusiness::getCookieWxUserID($mpUserId);
                if(empty($wxUserID))
                {
                    $url = sprintf('/wx_user/getwd/index?mp_user_id=%s&back_url=%s', $mpUserId,$backUrl);
                    $this->_gateway->redirect($url);
                }
            }
            else
            {
                $wxUserID = $this->_request->getRouteParam( 'wx_user_id' );
            }

            $tags = explode(',', str_replace('"', '', $article->getTag()));

            if (count($tags) > 0)
            {
                foreach($tags as $tag)
                {
                    if ($tag != '')
                    {
                        $tagLog = new ArticleTagLog();
                        $tagLog->setTag($tag)->setWxUserID($wxUserID)->insert();
                    }
                }
            }
            $levels = explode(',', $article->getUserLevel());
            if (!in_array(WxUserLevel::LEVEL_0, $levels))
            {
                $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);

                if (!in_array($wxUser->getUserLevel(), $levels))
                {
                    $this->_redirectToErrorPage('对不起，您所在的用户组无权查看该文章。');
                }
            }
        }
        //获取微信公共账号链接地址
        $mpUserConfig = ConfigBusiness::mpUserConfig($mpUserId);
        $mp_url = $mpUserConfig[MpUserConfigType::MP_USER_NAME_LINK];
        $this->_view->set( 'mp_url', $mp_url);
        $this->_view->set( 'mp_name', $mpName);
       //更新pv
        if($article->isEmpty())
        {
            $this->_redirectToErrorPage('此文章已不存在。');
        }
        else
        {
            MpArticleBusiness::trafficUpdate($mpArticleId,$mpUserId,$communityID);
        }
/*
        if(!empty($wxUserId))
        {
            $tags = explode(',', str_replace('"', '', $article->getTag()));
            if (count($tags) > 0)
            {
                foreach($tags as $tag)
                {
                    if ($tag != '')
                    {
                        $tagLog = new ArticleTagLog();
                        $tagLog->setTag($tag)->setWxUserID($wxUserId)->insert();
                    }
                }
            }
        }
*/


        if ($article->getRedirect() == 1)
        {
            $this->_gateway->redirect($article->getRedirectUrl());
        }

        // 用户等级判断，LEVEL_0 任何用户都可查看
        /*
        $levels = explode(',', $article->getUserLevel());
        if (!in_array(WxUserLevel::LEVEL_0, $levels))
        {
            $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);

            if (!in_array($wxUser->getUserLevel(), $levels))
            {
                $this->_redirectToErrorPage('对不起，您所在的用户组无权查看该文章。');
            }
        }
        */
        $logUser = UserBusiness::getLoginUser()->getUsername();
        if(!empty($logUser))
        {
            $isLogin = true;
        }else{
            $isLogin = false;
        }

        $checkReadPower =  $this->_request->get( 'check_read_power');
        //发送模板消息接收人信息
        $nameSend =  $this->_request->get( 'name_send');
        $phoneSend =  $this->_request->get( 'phone_send');
        $groupNameSend =  $this->_request->get( 'group_name_send');
        $this->_view->set( 'name_send', $nameSend);
        $this->_view->set( 'phone_send', $phoneSend);
        $this->_view->set( 'group_name_send', $groupNameSend);
        if (!strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger"))
        {
            $this->_view->set( 'check_read_power', $checkReadPower);
        }

        $this->_view->set( 'is_login', $isLogin);
        $this->_view->set( 'is_iframe', strpos($article->getContent(), 'iframe') !== false);
        //查看此用户是否点过赞
        $this->_view->set( 'had_liked', MpArticleBusiness::hadLiked($wxUserID, $mpArticleId, $mpUserId));
        $this->_view->set( 'article', $articleData );
        $this->_view->set( 'mp_article_id', $mpArticleId );
        $this->_view->set( 'wx_user_id', $wxUserID );
        $this->_view->set( 'mp_user_id', $mpUserId );
        $signPackage = CarouselBusiness::getSignPackage($mpUserId);
        $this->_view->set('signPackage', $signPackage);
        $this->_view->set( 'community_id', $communityID );
        $this->_view->set( 'like_count', MpArticleBusiness::likeCount($mpArticleId));


        $sharePic = '';
        if (!$mpUser->isEmpty())
        {
            $mpUserSharePic = $mpUser->getSharePic();
            if (!empty($mpUserSharePic))
            {
                $sharePic = $mpUserSharePic;
            }
        }
        $this->_view->set( 'share_pic', $sharePic );

        $pv = MpArticleDailyTraffic::fetchColumn([MpArticleDailyTraffic::PV],[MpArticleDailyTraffic::MP_ARTICLE_ID => $mpArticleId]);
        $pv = array_sum($pv);
        if(!isset($pv))
        {
            $pv = 0;
        }
        if($pv>10000)
        {
            $pv='10000+';
        }
        $this->_view->set( 'pv', $pv );

        //调取评论内容
        $paging = []; // 先初始化为空
        $outputColumns = ArticleComment::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 500;
        $ranking       = [ ArticleComment::ARTICLE_COMMENT_ID => true ];
        $articleComment = ArticleComment::fetchRows(['*'],[ArticleComment::MP_ARTICLE_ID => $mpArticleId], null, $ranking, $paging=null, $outputColumns);
        if(!empty($articleComment))
        {
            $this->_view->set( 'check_comment', true );//判断评论是否为空，如果为空则不显示评论内容
        }
        $shownColumns =
            [
                "send_user" =>
                    [
                        Table::COLUMN_TITLE => "发送用户",
                        Table::COLUMN_FUNCTION => function (array $row)
                            {
                                $wxUser = new WxUser([WxUser::WX_USER_ID => $row[ArticleComment::WX_USER_ID]]);
                                $name = $wxUser->getNick();
                                $headPic = $wxUser->getHeadPic();
                                $comment = $row[ArticleComment::COMMENT];
                                $time = CsChatBusiness::getCommentTime($row[ArticleComment::_CREATED_AT]);
                                $headPic = "<div style=\"float: left\"><img src = \"$headPic\" width=\"30px\" height=\"30px\"/></div>";
                                $name = "<div style=\"margin-bottom: 2px\">$name</div>";
                                $comment = "<div style=\"margin-bottom: 2px\">$comment</div>";
                                $time = "<div style=\"margin-bottom: 2px\">$time</div>";
                                $content = "<div style=\"margin-left: 40px\">".$name.$comment.$time."</div>";
                                $line = "<div style='height:1px;background-color:#EBE3E3;line-height:1px;clear:both;display:block;overflow:hidden;margin-bottom: 6px'></div>";
                                return $line.$headPic.$content;
                            }
                    ],
            ];

        $table               = Table::fromDbData( $articleComment, $outputColumns, ArticleComment::ARTICLE_COMMENT_ID, $paging, $shownColumns);
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }
}