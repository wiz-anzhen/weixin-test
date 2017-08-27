<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\WxMenuContentType;
use MP\Model\Mp\WxMenuType;
use MP\Model\Mp\WxSubMenu;

class WxSubMenuBusiness extends BaseBusiness
{
    public static function getWxSubMenuList(array $condition, array &$paging = NULL, $ranking, array $outputColumns = NULL)
    {
        return WxSubMenu::fetchRows(['*'], $condition, NULL, $ranking, $paging, $outputColumns);
    }

    public static function insert($data)
    {
        $WxSubMenu = new WxSubMenu();

        if($data[WxSubMenu::WX_MENU_TYPE ]== WxMenuType::VIEW)
        {
            $WxSubMenu->setMpUserID($data[WxSubMenu::MP_USER_ID])
                ->setWxMenuID($data[WxSubMenu::WX_MENU_ID])
                ->setWxMenuName($data[WxSubMenu::WX_MENU_NAME])
                ->setWxMenuType($data[WxSubMenu::WX_MENU_TYPE])
                ->setContentType(NULL)
                ->setUrl($data[WxSubMenu::URL])
                ->setSortNo($data[WxSubMenu::SORT_NO])
                ->setAccessAuthority($data[WxSubMenu::ACCESS_AUTHORITY])
                ->insert();

            return true;
        }

        if($data[WxSubMenu::WX_MENU_TYPE] == WxMenuType::CLICK)
        {
            if($data[WxSubMenu::CONTENT_TYPE] != WxMenuContentType::CUSTOM_TEXT)
            {
                $data[WxSubMenu::CONTENT_VALUE] = NULL;
            }
            $WxSubMenu->setMpUserID($data[WxSubMenu::MP_USER_ID])
                ->setWxMenuID($data[WxSubMenu::WX_MENU_ID])
                ->setWxMenuName($data[WxSubMenu::WX_MENU_NAME])
                ->setWxMenuKey($data[WxSubMenu::WX_MENU_KEY])
                ->setWxMenuType($data[WxSubMenu::WX_MENU_TYPE])
                ->setContentType($data[WxSubMenu::CONTENT_TYPE])
                ->setContentValue($data[WxSubMenu::CONTENT_VALUE])
                ->setSortNo($data[WxSubMenu::SORT_NO])
                ->setAccessAuthority($data[WxSubMenu::ACCESS_AUTHORITY])
                ->insert();

            return true;
        }
        return false;
    }

    public static function update($mpUserId, $WxSubMenuID, $data)
    {
        $WxSubMenu = new WxSubMenu([WxSubMenu::WX_SUB_MENU_ID => $WxSubMenuID,
                                   WxSubMenu::MP_USER_ID => $mpUserId]);

        if ($WxSubMenu->isEmpty()) {
            log_debug( "Could not find WxSubMenu($WxSubMenu)" );

            return false;
        }



        if($data[WxSubMenu::WX_MENU_TYPE] == WxMenuType::VIEW)
        {
            $WxSubMenu->setWxMenuName($data[WxSubMenu::WX_MENU_NAME])
                      ->setWxMenuType($data[WxSubMenu::WX_MENU_TYPE])
                      ->setUrl($data[WxSubMenu::URL])
                      ->setSortNo($data[WxSubMenu::SORT_NO])
                      ->setAccessAuthority($data[WxSubMenu::ACCESS_AUTHORITY])
                      ->update();

            return true;
        }

        if($data[WxSubMenu::WX_MENU_TYPE] == WxMenuType::CLICK)
        {
            if(($data[WxSubMenu::CONTENT_TYPE] != WxMenuContentType::CUSTOM_TEXT) && ($data[WxSubMenu::CONTENT_TYPE] != WxMenuContentType::CUSTOM_NEWS))
            {
                $data[WxSubMenu::CONTENT_VALUE] = NULL;
            }

            $WxSubMenu->setWxMenuName($data[WxSubMenu::WX_MENU_NAME])
                ->setWxMenuType($data[WxSubMenu::WX_MENU_TYPE])
                ->setContentType($data[WxSubMenu::CONTENT_TYPE])
                ->setContentValue($data[WxSubMenu::CONTENT_VALUE])
                ->setSortNo($data[WxSubMenu::SORT_NO])
                ->setAccessAuthority($data[WxSubMenu::ACCESS_AUTHORITY])
                ->update();

            return true;
        }
        return false;
    }

    public static function delete($mpUserId, $WxSubMenuID)
    {
        $WxSubMenu = new WxSubMenu([WxSubMenu::WX_SUB_MENU_ID => $WxSubMenuID, WxSubMenu::MP_USER_ID => $mpUserId]);

        if ($WxSubMenu->isEmpty()) {
            log_debug( "Could not find WxSubMenu($WxSubMenu)" );

            return false;
        }

        $WxSubMenu->delete();

        return true;
    }
}