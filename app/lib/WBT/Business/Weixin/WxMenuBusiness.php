<?php

namespace WBT\Business\Weixin;

use Bluefin\Data\DbExprNot;
use MP\Model\Mp\MpRuleNewsItem;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxMenu;
use MP\Model\Mp\WxMenuContentType;
use MP\Model\Mp\WxMenuType;
use MP\Model\Mp\WxSubMenu;
use Snoopy\Snoopy;

class WxMenuBusiness extends BaseBusiness
{
    public static function getWxMenuList(array $condition, array &$paging = NULL, $ranking, array $outputColumns = NULL)
    {
        return WxMenu::fetchRows(['*'], $condition, NULL, $ranking, $paging, $outputColumns);
    }

    public static function insert($mpUserId, $name, $sortNo, $access)
    {
        $access = $access == 1 ? 1 : 0;

        $wxMenu = new WxMenu();
        $wxMenu->setMpUserID($mpUserId)
            ->setName($name)
            ->setSortNo($sortNo)
            ->setAccessAuthority($access)
            ->insert();

        return true;
    }

    public static function update($mpUserId,$WxMenuID, $name, $sortNo,$access)
    {
        $wxMenu = new WxMenu([WxMenu::WX_MENU_ID => $WxMenuID, WxMenu::MP_USER_ID => $mpUserId]);

        if ($wxMenu->isEmpty()) {
            log_debug( "Could not find WxMenu($wxMenu)" );

            return false;
        }

        $access = $access == 1 ? 1 : 0;

        $wxMenu->setName($name)->setSortNo($sortNo)->setAccessAuthority($access)->update();

        return true;
    }

    public static function delete($mpUserId, $wxMenuID)
    {
        $wxMenu = new WxMenu([WxMenu::WX_MENU_ID => $wxMenuID, WxMenu::MP_USER_ID => $mpUserId]);

        if ($wxMenu->isEmpty()) {
            log_debug( "Could not find WxMenu($wxMenu)" );

            return false;
        }

        $wxMenu->delete();

        $wxSubMenuArray = WxSubMenuBusiness::getWxSubMenuList([WxSubMenu::WX_MENU_ID => $wxMenuID], $paging, null, null, null);
        foreach($wxSubMenuArray as $wxSubMenu)
        {
            $wxSubMenu1 = new WxSubMenu([WxSubMenu::WX_MENU_ID => $wxSubMenu[WxSubMenu::WX_MENU_ID]]);
            $wxSubMenu1->delete();
            if($wxSubMenu[WxSubMenu::CONTENT_TYPE] == WxMenuContentType::CUSTOM_NEWS)
            {
                $mpRuleNewsItem = explode(',',$wxSubMenu[WxSubMenu::CONTENT_VALUE]);
                foreach($mpRuleNewsItem as $mpRuleNewsItemID)
                {
                    $mpRuleItem = new MpRuleNewsItem([MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID => $mpRuleNewsItemID]);
                    $mpRuleItem->delete();
                }
            }
        }

        return true;
    }

    public static function getButton($mpUserID)
    {
        $res = array('errno' => 0);
        $wxMenu = WxMenu::fetchRows(['*'], [WxMenu::MP_USER_ID => $mpUserID, WxMenu::SORT_NO => new DbExprNot(0)], null, [WxMenu::SORT_NO], null);
        $wxMenuCount = count($wxMenu);
        if($wxMenuCount <= 3)
        {
            $content = array();
            foreach($wxMenu as $menuDetail)
            {
                $wxSubMenu = WxSubMenu::fetchRows(['*'], [WxSubMenu::MP_USER_ID => $menuDetail[WxMenu::MP_USER_ID], WxSubMenu::WX_MENU_ID => $menuDetail[WxMenu::WX_MENU_ID]], null, [WxSubMenu::SORT_NO], null);
                $wxSubMenuCount = count($wxSubMenu);
                if($wxSubMenuCount == 1)
                {
                    if($wxSubMenu[0][WxSubMenu::WX_MENU_TYPE] == WxMenuType::CLICK)
                    {
                        $menuContent = '{"type":"' . strtolower($wxSubMenu[0][WxSubMenu::WX_MENU_TYPE]) . '","name":"' . $wxSubMenu[0][WxSubMenu::WX_MENU_NAME] . '","key":"' . $wxSubMenu[0][WxSubMenu::WX_MENU_KEY] . '"}';
                        array_push($content, $menuContent);
                        //log_debug("content = ", $menuContent);
                    }
                    if($wxSubMenu[0][WxSubMenu::WX_MENU_TYPE] == WxMenuType::VIEW)
                    {
                        $menuContent = '{"type":"' . strtolower($wxSubMenu[0][WxSubMenu::WX_MENU_TYPE]) . '","name":"' . $wxSubMenu[0][WxSubMenu::WX_MENU_NAME] . '","url":"' . $wxSubMenu[0][WxSubMenu::URL] . '"}';
                        array_push($content, $menuContent);
                        //log_debug("content = ", $menuContent);
                    }
                }
                elseif($wxSubMenuCount >=2 && $wxSubMenuCount <=5)
                {
                    $menuContent = array();
                    foreach($wxSubMenu as $subMenuDetail)
                    {
                        if($subMenuDetail[WxSubMenu::WX_MENU_TYPE] == WxMenuType::CLICK)
                        {
                            $subMenuContent = '{"type":"' . strtolower($subMenuDetail[WxSubMenu::WX_MENU_TYPE]) . '","name":"' . $subMenuDetail[WxSubMenu::WX_MENU_NAME] . '","key":"' . $subMenuDetail[WxSubMenu::WX_MENU_KEY] . '"}';
                            array_push($menuContent, $subMenuContent);
                        }
                        if($subMenuDetail[WxSubMenu::WX_MENU_TYPE] == WxMenuType::VIEW)
                        {
                            $subMenuContent = '{"type":"' . strtolower($subMenuDetail[WxSubMenu::WX_MENU_TYPE]) . '","name":"' . $subMenuDetail[WxSubMenu::WX_MENU_NAME] . '","url":"' . $subMenuDetail[WxSubMenu::URL] . '"}';
                            array_push($menuContent, $subMenuContent);
                        }
                    }
                    $menuContent = implode(',', $menuContent);
                    $menuContent = '{"name":"' . $menuDetail[WxMenu::NAME] . '","sub_button":[' . $menuContent . ']}';
                    array_push($content, $menuContent);
                    //log_debug("content", $content);
                }
                else
                {
                    $res['errno'] = 1;
                    $res['error'] = "菜单[" . $menuDetail[WxMenu::NAME] . "]的子菜单数量必须为1-5个";
                    log_debug("res = ", $res);
                    return $res;
                }
            }
            $content = implode(',',$content);
            $content = '{"button":[' . $content . ']}';
            log_debug("[mpUserID:$mpUserID]", $content);;

            $res['content'] = $content;

            return $res;
        }
        else
        {
            $res['errno'] = 1;
            $res['error'] = "微信菜单数量为2-3个";
            log_debug("res = ", $res);
            return $res;
        }
    }

    public static function getToken($mpUserID)
    {
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $appID = $mpUser->getAppID();
        $appSecret = $mpUser->getAppSecret();

        $snoopy = new Snoopy();

        define('API_URL', 'https://api.weixin.qq.com/cgi-bin/token');

        $submit_vars = array();
        $submit_vars['grant_type'] = 'client_credential';
        $submit_vars['appid']      = $appID;
        $submit_vars['secret']     = $appSecret;

        if($snoopy->submit(API_URL, $submit_vars))
        {
            $result = trim($snoopy->results);
            $result = (array)json_decode($result);
            if(isset($result['access_token']))
            {
                $accessToken = $result['access_token'];

                $res = array('errno' => 0);
                $res['access_token'] = $accessToken;

                return $res;
            }
            log_warn("result = ", $result);
        }
        $res = array('errno' => 1);

        return $res;
    }

    public static function updateWxMenu($accessToken, $content)
    {
        log_debug($accessToken);
        log_debug($content);

        define('UPDATE_URL', 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$accessToken);

        $res = _curl_post(UPDATE_URL, $content);
        $res = (array)json_decode($res);
        if($res && $res['errcode'] == 0)
        {
            return true;
        }

        log_warn("res = ", $res);

        return false;
    }

    public static function deleteWxMenu($accessToken)
    {
        $snoopy = new Snoopy();

        define('DELETE_URL', 'https://api.weixin.qq.com/cgi-bin/menu/delete');

        $submit_vars = array();
        $submit_vars['access_token'] = $accessToken;

        if($snoopy->submit(DELETE_URL, $submit_vars))
        {
            $result = trim($snoopy->results);
            $result = (array)json_decode($result);
            if(isset($result['errcode']) && $result['errcode'] == 0)
            {
                return true;
            }
            log_warn("result = ", $result);
        }
        return false;
    }
}