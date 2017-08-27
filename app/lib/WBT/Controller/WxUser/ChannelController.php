<?php

namespace WBT\Controller\WxUser;

use WBT\Business\Weixin\CarouselBusiness;
use WBT\Business\Weixin\ChannelBusiness;
use MP\Model\Mp\Channel;
use MP\Model\Mp\ChannelArticle;
use Common\Helper\BaseController;
use MP\Model\Mp\UrgentNoticeReadRecord;
use MP\Model\Mp\ArticleType;
use WBT\Business\Weixin\WxUserBusiness;
use MP\Model\Mp\MpUser;
class ChannelController extends BaseController
{
    public function channelAction()
    {
        $channelId = $this->_request->getQueryParam(Channel::CHANNEL_ID);
        $channel = new Channel([Channel::CHANNEL_ID => $channelId]);
        $this->_view->set('channel', $channel->data());
        $this->_view->set('mmdd_today', date('md'));
        $this->_view->set('mmdd_yesterday', date('md', strtotime("-1 day")));

        $view = $this->_request->get('view');
        $ranking = [ ChannelArticle::KEEP_TOP => TRUE, ChannelArticle::RELEASE_DATE => true ];
        $articles = ChannelBusiness::getChannelArticleListForFrontEnd($channelId, $view, $ranking, [], ['*']);
        $this->_view->set('articles', $articles);

        if ($view == 2)
        {
            foreach ($articles as $key=> $value)
            {
                $articles[$key]['article_desc'] = nl2br($articles[$key]['article_desc']);
            }
            $this->_view->set('articles', $articles);
            $this->changeView('WBT/WxUser/Channel.channel2.html');
        }
    }

    public function oursAction()
    {
        $channelArticleID = $this->_request->getQueryParam(ChannelArticle::CHANNEL_ARTICLE_ID);
        $channelArticle = new ChannelArticle([ChannelArticle::CHANNEL_ARTICLE_ID => $channelArticleID]);
        $mpUserID = $channelArticle->getMpUserID();
        //判断公众账号属性
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $mpUserType = $mpUser->getMpUserType();
        $signPackage = CarouselBusiness::getSignPackage($mpUserID);
        $this->_view->set('signPackage', $signPackage);
        if($mpUserType == 1)
        {
            $wxUserID = WxUserBusiness::getCookieWxUserID($mpUserID);
            if(!empty($wxUserID))
            {
                $urgentNoticeReadRecord = new UrgentNoticeReadRecord(
                    [
                        UrgentNoticeReadRecord::CHANNEL_ARTICLE_ID => $channelArticleID,
                        UrgentNoticeReadRecord::WX_USER_ID => $wxUserID
                    ]);
                if($urgentNoticeReadRecord->isEmpty())
                {
                    $urgentNoticeReadRecord->setWxUserID($wxUserID)
                        ->setChannelArticleID($channelArticleID)
                        ->insert(true);
                }
            }
            $type= $channelArticle->getArticleType();
            $url = $channelArticle->getArticleUrl();
            if($type == ArticleType::ARTICLE_OURS)
            {
                $this->_view->set('article', $channelArticle->data());
            }
            else if ($type == ArticleType::ARTICLE_THIRD_PARTY)
            {
                $this->_gateway->redirect($url);
            }
        }


    }

}
