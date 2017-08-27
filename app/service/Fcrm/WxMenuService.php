<?php

use WBT\Business\Weixin\WxMenuBusiness;
use MP\Model\Mp\WxMenu;

require_once 'MpUserServiceBase.php';

class WxMenuService extends MpUserServiceBase
{
    public function update()
    {
        $res      = array( 'errno' => 0 );
        $wxMenuID = $this->_app->request()->get( WxMenu::WX_MENU_ID );
        $name     = $this->_app->request()->get( WxMenu::NAME );
        $sortNo   = $this->_app->request()->get( WxMenu::SORT_NO );
        $mpUserId = $this->_app->request()->get( WxMenu::MP_USER_ID);
        $access   = 0;

        if (!WxMenuBusiness::update($mpUserId, $wxMenuID, $name, $sortNo, $access))
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
        $res      = array( 'errno' => 0 );
        $wxMenuID = $this->_app->request()->get( WxMenu::WX_MENU_ID );
        $mpUserId = $this->_app->request()->get(WxMenu::MP_USER_ID);

        if (!WxMenuBusiness::delete($mpUserId, $wxMenuID )) {
            $error        = '删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function insert()
    {
        $res      = array( 'errno' => 0 );
        $request  = $this->_app->request();
        $mpUserId = $request->get( 'mp_user_id' );
        $name     = $request->get( WxMenu::NAME );
        $sortNo   = $request->get( WxMenu::SORT_NO );
        $access   = 0;

        if (!WxMenuBusiness::insert($mpUserId, $name, $sortNo, $access))
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function updateWxMenu()
    {
        $res      = array( 'errno' => 0 );
        $mpUserID = $this->_app->request()->get('mp_user_id');

        $buttonContent = WxMenuBusiness::getButton($mpUserID);

        log_debug("bc = ", $buttonContent);
        if($buttonContent['errno'] == 0)
        {
            $content = $buttonContent['content'];
            $result = WxMenuBusiness::getToken($mpUserID);
            if($result['errno'] == 0)
            {
                $accessToken = $result['access_token'];
                if(WxMenuBusiness::updateWxMenu($accessToken, $content))
                {
                    return $res;
                }
                else
                {
                    $res['errno'] = 1;
                    $res['error'] = "更新菜单失败";
                }
            }
            else
            {
                $res['errno'] = 1;
                $res['error'] = "获取access_token失败";
            }
        }
        else
        {
            $res['errno'] = 1;
            $res['error'] = $buttonContent['error'];
        }
        return $res;
    }

    public function deleteWxMenu()
    {
        $res      = array( 'errno' => 0 );
        $mpUserID = $this->_app->request()->get('mp_user_id');

        $result = WxMenuBusiness::getToken($mpUserID);
        if($result['errno'] == 0)
        {
            $accessToken = $result['access_token'];
            if(WxMenuBusiness::deleteWxMenu($accessToken))
            {
                return $res;
            }
            else
            {
                $res['errno'] = 1;
                $res['error'] = "删除菜单失败";
            }
        }
        else
        {
            $res['errno'] = 1;
            $res['error'] = "获取access_token失败";
        }
        return $res;
    }
}