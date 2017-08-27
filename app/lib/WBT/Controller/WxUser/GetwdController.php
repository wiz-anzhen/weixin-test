<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 14-7-15
 * Time: 上午11:27
 */

namespace WBT\Controller\WxUser;

use WBT\Business\Weixin\WxApiBusiness;
use WBT\Business\Weixin\WxUserBusiness;
use Common\Helper\BaseController;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\MpUser;

class GetwdController extends BaseController {

    public function indexAction()
    {
        $mpUserID = $this->_request->get( 'mp_user_id' );
        $backUrl = $this->_request->get( 'back_url' );
        WxApiBusiness::getCode($mpUserID,$backUrl);
    }

    public function getCodeAction()
    {
        $code = $this->_request->get( 'code' );
        $state = $this->_request->get( 'state' );
        $state = explode("|",$state);
        $mpUserID = $state[0];
        $backUrl = base64_decode($state[1]);
        $wxUserID = WxApiBusiness::getWxUserID($code,$mpUserID);
        log_debug("[wxUserID:$wxUserID][mpUserID:$mpUserID]");
        if($wxUserID)
        {
            $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
            if($wxUser->isEmpty())
            {
                $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
                if(!$mpUser->isEmpty())
                {
                    WxUserBusiness::initWxUserForSession($mpUser, $wxUserID);
                }
            }

            $expireTime = ConfigBusiness::getCookieExpireTime();
            WxUserBusiness::setCookieWxUserID($mpUserID,$wxUserID,$expireTime);
            $this->_gateway->redirect($backUrl);
        }
        else
        {
            $this->_redirectToErrorPage('错误：获取用户信息失败');
        }

    }


} 