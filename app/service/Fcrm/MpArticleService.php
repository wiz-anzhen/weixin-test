<?php

use WBT\Business\Weixin\MpArticleBusiness;
use MP\Model\Mp\MpArticle;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\WxUserLevel;

require_once 'ServiceBase.php';

class MpArticleService extends ServiceBase
{
    public function updateUserLevel()
    {

        $res      = array( 'errno' => 0 );
        $request  = $this->_app->request();
        $mpUserId = $request->get( MpArticle::MP_USER_ID);
        $mpArticleId = $request->get(MpArticle::MP_ARTICLE_ID);
        $mpArticle   = new MpArticle([MpArticle::MP_ARTICLE_ID => $mpArticleId, MpArticle::MP_USER_ID => $mpUserId]);
        $data     = [];
        $level = [];
        foreach(WxUserLevel::getDictionary() as $key => $v)
        {
            $postData = $request->get($key);
            if (!empty($postData))
            {
                $level[] = $key;
            }
        }
        $data[MpArticle::USER_LEVEL] = implode(',', $level);

        $mpArticle->apply($data)->update();

        return $res;
    }

    public function update() {
        $res         = array( 'errno' => 0 );
        $mpArticleId = $this->_app->request()->get( MpArticle::MP_ARTICLE_ID );

        $data = $this->_app->request()->getArray([
            MpArticle::TITLE,
            MpArticle::SHARE_DESC,
            MpArticle::CONTENT,
            MpArticle::SHOW_LIKE,
            MpArticle::REDIRECT,
            MpArticle::REDIRECT_URL,
            MpArticle::TAG,
        ]);

        $data[MpArticle::REDIRECT] = ($data[MpArticle::REDIRECT] == 'true') ? 1 : 0;

        $data[MpArticle::TAG] = '"' . str_replace('，', '","', str_replace(',', '","', str_replace('"', '', str_replace(' ', '', $data[MpArticle::TAG])))) . '"';

        if ( !MpArticleBusiness::update($mpArticleId, $data ) )
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        return $res;
    }

    public function remove() {
        $res         = array( 'errno' => 0 );
        $mpArticleId = $this->_app->request()->get( MpArticle::MP_ARTICLE_ID );
        $mpUserId = $this->_app->request()->get(MpArticle::MP_USER_ID);

        $mpArticle = new MpArticle([MpArticle::MP_USER_ID => $mpUserId, MpArticle::MP_ARTICLE_ID => $mpArticleId]);
        if (!$this->_checkRule( $mpArticle->getMpUserID() )) {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        log_debug( "[mpArticleId:$mpArticleId]" );
        if (!MpArticleBusiness::remove($mpUserId, $mpArticleId )) {
            $error        = '删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

}