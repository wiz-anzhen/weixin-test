<?php

use Bluefin\Service;
use WBT\Business\Weixin\WxSubMenuBusiness;
use MP\Model\Mp\WxSubMenu;

require_once 'MpUserServiceBase.php';

class WxSubMenuService extends MpUserServiceBase
{
    public function update() {
        $res     = array( 'errno' => 0 );
        $request = $this->_app->request();

        $WxSubMenuID = $this->_app->request()->get( WxSubMenu::WX_SUB_MENU_ID );
        $mpUserId = $this->_app->request()->get(WxSubMenu::MP_USER_ID);
        $data = [
            WxSubMenu::WX_MENU_NAME => $request->get(WxSubMenu::WX_MENU_NAME),
            WxSubMenu::WX_MENU_TYPE => $request->get(WxSubMenu::WX_MENU_TYPE),
            WxSubMenu::CONTENT_TYPE => $request->get(WxSubMenu::CONTENT_TYPE),
            WxSubMenu::CONTENT_VALUE => $request->get(WxSubMenu::CONTENT_VALUE),
            WxSubMenu::URL => $request->get(WxSubMenu::URL),
            WxSubMenu::SORT_NO => $request->get(WxSubMenu::SORT_NO),
            WxSubMenu::ACCESS_AUTHORITY => $request->get(WxSubMenu::ACCESS_AUTHORITY),
        ];

        if (!WxSubMenuBusiness::update($mpUserId, $WxSubMenuID, $data))
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
        $WxSubMenuID = $this->_app->request()->get(WxSubMenu::WX_SUB_MENU_ID);
        $mpUserId = $this->_app->request()->get(WxSubMenu::MP_USER_ID);

        if (!WxSubMenuBusiness::delete($mpUserId, $WxSubMenuID )) {
            $error        = '删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function insert() {
        $res      = array( 'errno' => 0 );
        $request  = $this->_app->request();

        $data = [
            WxSubMenu::MP_USER_ID => $request->get(WxSubMenu::MP_USER_ID),
            WxSubMenu::WX_MENU_ID => $request->get(WxSubMenu::WX_MENU_ID),
            WxSubMenu::WX_MENU_NAME => $request->get(WxSubMenu::WX_MENU_NAME),
            WxSubMenu::WX_MENU_TYPE => $request->get(WxSubMenu::WX_MENU_TYPE),
            WxSubMenu::WX_MENU_KEY => $request->get(WxSubMenu::WX_MENU_KEY),
            WxSubMenu::CONTENT_TYPE => $request->get(WxSubMenu::CONTENT_TYPE),
            WxSubMenu::CONTENT_VALUE => $request->get(WxSubMenu::CONTENT_VALUE),
            WxSubMenu::URL => $request->get(WxSubMenu::URL),
            WxSubMenu::SORT_NO => $request->get(WxSubMenu::SORT_NO),
            WxSubMenu::ACCESS_AUTHORITY => $request->get(WxSubMenu::ACCESS_AUTHORITY),
        ];

        if (!WxSubMenuBusiness::insert($data))
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }
}