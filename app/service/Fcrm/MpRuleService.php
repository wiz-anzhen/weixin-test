<?php

use WBT\Business\Weixin\MpRuleBusiness;
use WBT\Business\Weixin\MpRuleNewsItemBusiness;
use MP\Model\Mp\MpRule;
use MP\Model\Mp\MpRuleNewsItem;
use MP\Model\Mp\WeixinMessageType;

require_once 'ServiceBase.php';

class MpRuleService extends ServiceBase
{
    /* 编辑菜单
     * url: /api/fcrm/menu/edit
     */
    public function update() {
        $res         = array( 'errno' => 0 );
        $request     = $this->_app->request();
        $mpRuleId    = $request->get( 'mp_rule_id' );
        $ruleName    = $request->get( 'name' );
        $keyword     = $request->get( 'keyword' );
        $contentType = $request->get( 'content_type' );
        $content     = $request->get( 'content' );
        $mpUserId = $request->get( 'mp_user_id' );

        $mpRule = new MpRule([MpRule::MP_USER_ID => $mpUserId, MpRule::MP_RULE_ID => $mpRuleId]);
        if (!$this->_checkRule( $mpRule->getMpUserID() )) {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        if (!in_array( $contentType, [ WeixinMessageType::TEXT, WeixinMessageType::NEWS ] )) {
            $contentType = WeixinMessageType::TEXT;
        }
        if ($contentType === WeixinMessageType::NEWS) {
            $content = '';
        }

        log_debug( "[mpRuleID:$mpRuleId][ruleName:$ruleName][keyword:$keyword][contentType:{$contentType}][content:$content]" );

        if (!MpRuleBusiness::update($mpUserId, $mpRuleId, $ruleName, $keyword, $contentType, $content )) {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function insert() {
        $res         = array( 'errno' => 0 );
        $request     = $this->_app->request();
        $mpUserId    = $request->get( 'mp_user_id' );
        $ruleName    = $request->get( 'name' );
        $keyword     = $request->get( 'keyword' );
        $contentType = $request->get( 'content_type' );
        $content     = $request->get( 'content' );

        if (!$this->_checkRule( $mpUserId )) {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        if (!in_array( $contentType, [ WeixinMessageType::TEXT, WeixinMessageType::NEWS ] )) {
            $contentType = WeixinMessageType::TEXT;
        }
        if ($contentType === WeixinMessageType::NEWS) {
            $content = '';
        }

        log_debug( "[mpUserId:{$mpUserId}][ruleName:{$ruleName}][keyword:{$keyword}][contentType:{$contentType}][content:{$content}]" );

        if (!MpRuleBusiness::insert( $mpUserId, $ruleName, $keyword, $contentType, $content )) {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function remove() {
        $res      = array( 'errno' => 0 );
        $mpRuleId = $this->_app->request()->get( MpRule::MP_RULE_ID );
        $mpUserId = $this->_app->request()->get(MpRule::MP_USER_ID);

        $mpRule = new MpRule([MpRule::MP_USER_ID => $mpUserId, MpRule::MP_RULE_ID => $mpRuleId]);
        if (!$this->_checkRule( $mpRule->getMpUserID() )) {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        log_debug( "[mpRuleId:$mpRuleId]" );
        if (!MpRuleBusiness::remove($mpUserId, $mpRuleId )) {
            $error        = '删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function updateNews() {
        $res              = array( 'errno' => 0 );
        $mpRuleNewsItemId = $this->_app->request()->get( 'mp_rule_news_item_id' );
        $title            = $this->_app->request()->get( 'title' );
        $description      = $this->_app->request()->get( 'description' );
        $picUrl           = $this->_app->request()->get( 'pic_url' );
        $url              = $this->_app->request()->get( 'url' );
        $top_dir_no       = $this->_app->request()->get( 'top_dir_no' );
        $sortNo           = $this->_app->request()->get( 'sort_no' );
        $mpUserId = $this->_app->request()->get('mp_user_id');

        $mpRuleNewsItem = new MpRuleNewsItem([MpRuleNewsItem::MP_USER_ID=>$mpUserId,
                                             MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID => $mpRuleNewsItemId]);
        $wxSubMenuID = $this->_app->request()->get( 'wx_sub_menu_id' );
        $wxSubMenu = new \MP\Model\Mp\WxSubMenu([\MP\Model\Mp\WxSubMenu::WX_SUB_MENU_ID => $wxSubMenuID]);
        $accessAuthority =  $wxSubMenu ->getAccessAuthority();
        if($accessAuthority != 'identify' and !empty($top_dir_no))
        {
            $error        = '此菜单不是认证菜单，不能设置一级目录编号，只能添加跳转链接';
            $res['errno'] = 1;
            $res['error'] = $error;
            return $res;
        }
        if (!$this->_checkRule( $mpRuleNewsItem->getMpUserID() )) {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        log_debug( "[mpRuleNewsItemId:$mpRuleNewsItemId][title:$title][description:$description][picUrl:$picUrl][url:$url]" );

        if (!MpRuleNewsItemBusiness::update($mpUserId, $mpRuleNewsItemId, $title, $description, $picUrl, $url, $top_dir_no, $sortNo)) {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function removeNews() {
        $res              = array( 'errno' => 0 );
        $mpRuleId         = $this->_app->request()->get( 'mp_rule_id' );
        $mpRuleNewsItemId = $this->_app->request()->get( MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID );
        $mpUserId = $this->_app->request()->get(MpRuleNewsItem::MP_USER_ID);

        $mpRuleNewsItem = new MpRuleNewsItem([MpRuleNewsItem::MP_USER_ID=>$mpUserId,
                                             MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID => $mpRuleNewsItemId]);
        if (!$this->_checkRule( $mpRuleNewsItem->getMpUserID() )) {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        log_debug( "[mpRuleNewsItemId:$mpRuleNewsItemId]" );
        if (!MpRuleNewsItemBusiness::remove($mpUserId, $mpRuleNewsItemId )) {
            $error        = '删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        } elseif (!MpRuleBusiness::removeRuleNewsItem($mpUserId, $mpRuleId, $mpRuleNewsItemId )) {
            $error        = '记录已删除，但从规则内容删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function removeNewsForWx() {
        $res              = array( 'errno' => 0 );
        $wxSubMenuID      = $this->_app->request()->get( 'wx_sub_menu_id' );
        $mpRuleNewsItemId = $this->_app->request()->get( MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID );
        $mpUserId = $this->_app->request()->get(MpRuleNewsItem::MP_USER_ID);

        $mpRuleNewsItem = new MpRuleNewsItem([MpRuleNewsItem::MP_USER_ID => $mpUserId,
                                             MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID => $mpRuleNewsItemId]);
        if (!$this->_checkRule( $mpRuleNewsItem->getMpUserID() )) {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        log_debug( "[mpRuleNewsItemId:$mpRuleNewsItemId]" );
        if (!MpRuleNewsItemBusiness::remove($mpUserId, $mpRuleNewsItemId )) {
            $error        = '删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        } elseif (!MpRuleBusiness::removeRuleNewsItemForWx($mpUserId, $wxSubMenuID, $mpRuleNewsItemId )) {
            $error        = '记录已删除，但从规则内容删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function insertNews() {
        $res         = array( 'errno' => 0 );
        $request     = $this->_app->request();
        $mpUserId    = $request->get( 'mp_user_id' );
        $mpRuleId    = $request->get( 'mp_rule_id' );
        $title       = $request->get( 'title' );
        $description = $request->get( 'description' );
        $pic_url     = $request->get( 'pic_url' );
        $url         = $request->get( 'url' );
        $top_dir_no       = $this->_app->request()->get( 'top_dir_no' );
        $sortNo           = $this->_app->request()->get( 'sort_no' );


        if (!$this->_checkRule( $mpUserId )) {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        log_debug( "[mpUserId:{$mpUserId}][mpRuleId:{$mpRuleId}][title:{$title}][description:{$description}][pic_url:{$pic_url}][url:{$url}]" );

        if (!($lastInsertId = MpRuleNewsItemBusiness::insert( $mpUserId, $title, $description, $pic_url, $url, $top_dir_no, $sortNo))) {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        } else if (!MpRuleBusiness::addRuleNewsItem( $mpRuleId, $lastInsertId )) {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function insertNewsForWx() {
        $res         = array( 'errno' => 0 );
        $request     = $this->_app->request();
        $mpUserId    = $request->get( 'mp_user_id' );
        $wxSubMenuID    = $request->get( 'wx_sub_menu_id' );
        $title       = $request->get( 'title' );
        $description = $request->get( 'description' );
        $pic_url     = $request->get( 'pic_url' );
        $url         = $request->get( 'url' );
        $top_dir_no =$request ->get('top_dir_no');
        $sortNo      = $request->get( 'sort_no' );

        if (!$this->_checkRule( $mpUserId )) {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        $wxSubMenu = new \MP\Model\Mp\WxSubMenu([\MP\Model\Mp\WxSubMenu::WX_SUB_MENU_ID => $wxSubMenuID]);
        $accessAuthority =  $wxSubMenu ->getAccessAuthority();
        if($accessAuthority != 'identify' and !empty($top_dir_no))
        {
            $error        = '此菜单不是认证菜单，不能设置一级目录编号，只能添加跳转链接';
            $res['errno'] = 1;
            $res['error'] = $error;
            return $res;
        }

        log_debug( "[mpUserId:{$mpUserId}][wxSubMenuID:{$wxSubMenuID}][title:{$title}][description:{$description}][pic_url:{$pic_url}][url:{$url}]" );

        if (!($lastInsertId = MpRuleNewsItemBusiness::insert( $mpUserId, $title, $description, $pic_url, $url, $top_dir_no, $sortNo))) {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        } else if (!MpRuleBusiness::addRuleNewsItemForWx( $wxSubMenuID, $lastInsertId )) {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }
}