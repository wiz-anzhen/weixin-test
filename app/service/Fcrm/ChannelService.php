<?php

use MP\Model\Mp\Channel;
use MP\Model\Mp\ChannelArticle;
use WBT\Business\Weixin\ChannelBusiness;
use MP\Model\Mp\MpArticle;


require_once 'ServiceBase.php';

class ChannelService extends ServiceBase
{
    public function update()
    {
        $res                     = array( 'errno' => 0 );
        $channelId = $this->_app->request()->get( Channel::CHANNEL_ID );
        $communityId = $this->_app->request()->get('community_id');
        $channel = new Channel([Channel::CHANNEL_ID => $channelId]);

        if (!$this->_checkRule( $channel->getMpUserID() ))
        {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        $title = $this->_app->request()->get( Channel::TITLE );
        if (!ChannelBusiness::update($communityId,$channelId, $title ) )
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        return $res;
    }

    public function remove()
    {
        $res                     = array( 'errno' => 0 );
        $channelId = $this->_app->request()->get( Channel::CHANNEL_ID );
        $mpUserId = $this->_app->request()->get(Channel::MP_USER_ID);
        $communityId = $this->_app->request()->get('community_id');

        $channel = new Channel([Channel::CHANNEL_ID => $channelId]);
        if (!$this->_checkRule( $channel->getMpUserID() ))
        {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        if (!ChannelBusiness::remove($channelId,$communityId))
        {
            $error        = '删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function add()
    {
        $res       = array( 'errno' => 0 );
        $request   = $this->_app->request();
        $mpUserId  = $request->get( 'mp_user_id' );
        $communityId = $request->get('community_id');
        $channelID =$request ->get("channel_id");

        if (!$this->_checkRule( $mpUserId ))
        {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        $title = $request->get( Channel::TITLE );

        if (!ChannelBusiness::insert( $communityId,$mpUserId, $title ))
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function updateArticle()
    {
        $res       = array( 'errno' => 0 );
        $channelArticleId = $this->_app->request()->get( ChannelArticle::CHANNEL_ARTICLE_ID );
        $channelId = $this->_app->request()->get(Channel::CHANNEL_ID);
        $communityId  = $this->_app->request()->get( 'community_id' );

        $mpUserId  = $this->_app->request()->get(Channel::MP_USER_ID);

        $releaseDate = $this->_app->request()->get(ChannelArticle::RELEASE_DATE);
        $articleTitle = $this->_app->request()->get(ChannelArticle::ARTICLE_TITLE);

        $articleType = $this->_app->request()->get(ChannelArticle::ARTICLE_TYPE);
        $articleDetail = $this->_app->request()->get(ChannelArticle::ARTICLE_DETAIL);

        $articleDesc = $this->_app->request()->get(ChannelArticle::ARTICLE_DESC);
        $shareUrl = $this->_app->request()->get(ChannelArticle::SHARE_URL);
        $articleUrl = $this->_app->request()->get(ChannelArticle::ARTICLE_URL);
        $keepTop = $this->_app->request()->get(ChannelArticle::KEEP_TOP);

        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $releaseDate))
        {
            return ['errno' => 1, 'error' => '发布日期格式不对，请严格按照 1982-01-01 的格式填写',];
        }

        if($keepTop=="1")
        {
            ChannelBusiness::updateKeepTop($communityId,$channelId);
        }

        if ( !ChannelBusiness::updateChannelArticle($communityId,$channelArticleId, $articleDesc, $releaseDate, $shareUrl, $articleTitle,$articleType, $articleDetail, $articleUrl, $keepTop ) )
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        return $res;
    }

    public function addArticle()
    {
        $res       = array( 'errno' => 0 );
        $request   = $this->_app->request();
        $mpUserId  = $request->get( 'mp_user_id' );
        $communityId  = $request->get( 'community_id' );
        $channelId = $request->get( ChannelArticle::CHANNEL_ID );

        $releaseDate = $request->get(ChannelArticle::RELEASE_DATE);
        $articleTitle = $request->get(ChannelArticle::ARTICLE_TITLE);
        $articleDetail = $request->get(ChannelArticle::ARTICLE_DETAIL);
        $articleType = $request->get(ChannelArticle::ARTICLE_TYPE);
        $articleUrl = $request->get(ChannelArticle::ARTICLE_URL);

        $articleDesc = $this->_app->request()->get(ChannelArticle::ARTICLE_DESC);
        $shareUrl = $this->_app->request()->get(ChannelArticle::SHARE_URL);
        $keepTop = $this->_app->request()->get(ChannelArticle::KEEP_TOP);



        if (!$this->_checkRule( $mpUserId ))
        {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        if($keepTop=="1")
        {
            ChannelBusiness::updateKeepTop($communityId,$channelId);
        }

        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $releaseDate))
        {
            return ['errno' => 1, 'error' => '发布日期格式不对，请严格按照 1982-01-01 的格式填写',];
        }

        if (!ChannelBusiness::insertChannelArticle( $communityId,$mpUserId,$channelId, $articleDesc, $releaseDate, $shareUrl, $articleTitle, $articleDetail, $articleType, $articleUrl, $keepTop))
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function removeArticle()
    {
        $res                     = array( 'errno' => 0 );
        $channelArticleId = $this->_app->request()->get( ChannelArticle::CHANNEL_ARTICLE_ID );
        $communityId  = $this->_app->request()->get( 'community_id' );

        if (!ChannelBusiness::removeChannelArticle($communityId,$channelArticleId ))
        {
            $error        = '删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

}