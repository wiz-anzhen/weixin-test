<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\WxMenu;

class WxMenuContentBusiness extends BaseBusiness
{
    public static function getWxMenuList(array $condition, array &$paging = NULL, $ranking, array $outputColumns = NULL)
    {
        return WxMenu::fetchRows(['*'], $condition, NULL, $ranking, $paging, $outputColumns);
    }

    public static function insert($mpUserId, $name, $sortNo)
    {
        $wxMenu = new WxMenu();
        $wxMenu->setMpUserID($mpUserId)->setName($name)->setSortNo($sortNo)->insert();

        return true;
    }

    public static function update($WxMenuID, $name, $sortNo)
    {
        $wxMenu = new WxMenu([WxMenu::WX_MENU_ID => $WxMenuID]);

        if ($wxMenu->isEmpty()) {
            log_debug( "Could not find WxMenu($wxMenu)" );

            return false;
        }

        $wxMenu->setName($name)->setSortNo($sortNo)->update();

        return true;
    }

    public static function delete($wxMenuID)
    {
        $wxMenu = new WxMenu([WxMenu::WX_MENU_ID => $wxMenuID]);

        if ($wxMenu->isEmpty()) {
            log_debug( "Could not find WxMenu($wxMenu)" );

            return false;
        }

        $wxMenu->delete();

        return true;
    }
}