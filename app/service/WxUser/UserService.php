<?php

use Bluefin\Service;
use Bluefin\App;
use WBT\Business\Weixin\WxUserBusiness;
use WBT\Business\Weixin\MpArticleBusiness;

// 普通用户账户设置相关api
// todo : 安全方面，增加token，
class UserService extends Service
{
    public function likeArticle()
    {
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $mpArticleId = App::getInstance()->request()->get('mp_article_id');
        $mpUserId = App::getInstance()->request()->get('mp_user_id');
        $communityID = App::getInstance()->request()->get('community_id');

        return MpArticleBusiness::likeArticle($wxUserId, $mpArticleId, $mpUserId,$communityID);
    }

    public function commentArticle()
    {
        $mpArticleId = App::getInstance()->request()->get('mp_article_id');
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $mpUserId = App::getInstance()->request()->get('mp_user_id');
        $comment = App::getInstance()->request()->get('comment');
        $communityID = App::getInstance()->request()->get('community_id');

        return MpArticleBusiness::commentArticle($wxUserId, $mpArticleId, $mpUserId, $comment,$communityID);
    }
}
