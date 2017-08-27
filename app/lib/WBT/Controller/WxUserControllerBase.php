<?php

namespace WBT\Controller;

use MP\Model\Mp\MpUser;
use WBT\Business\Weixin\WxUserBusiness;
use Common\Helper\BaseController;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\DirectoryPowerType;


class WxUserControllerBase extends BaseController
{
    protected $_mpUserID;
    protected $_wxUserID;
    protected $_communityID;
    protected $_wxUser;
    protected $_mpUserType;
    protected function _init()
    {
        $this->_mpUserID = $this->_request->get( 'mp_user_id' );
        $backUrl = base64_encode($this->_request->getFullRequestUri());
        //判断公众账号属性
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserID]);
        $mpUserType = $mpUser->getMpUserType();
        $this->_mpUserType = $mpUserType;
        $this->_view->set('mp_user_type',$mpUserType);
        //是服务号
        if($mpUserType == 1)
        {
            //验证是否使用微信访问
            if (strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger"))
            {
                $this->_wxUserID = WxUserBusiness::getCookieWxUserID($this->_mpUserID);
                log_debug("[wxUserID:{$this->_wxUserID}][mpUserID:{$this->_mpUserID}]");
                if(empty($this->_wxUserID))
                {
                    $url = sprintf('/wx_user/getwd/index?mp_user_id=%s&back_url=%s', $this->_mpUserID,$backUrl);
                    $this->_gateway->redirect($url);
                    //WxApiBusiness::getCode($this->_mpUserID,$backUrl);
                }
                $this->_wxUser = new WxUser([WxUser::WX_USER_ID => $this->_wxUserID]);
                log_debug("[wxUserID:{$this->_wxUserID}][mpUserID:{$this->_mpUserID}]");
                if(!$this->_wxUser->isEmpty())
                {
                    //$this->_mpUserID = $this->_wxUser->getMpUserID();
                    $this->_communityID = $this->_wxUser->getCurrentCommunityID();
                    $this->_view->set('wx_user_id',$this->_wxUserID);
                    $this->_view->set('mp_user_id', $this->_mpUserID);
                    $this->_view->set('community_id', $this->_communityID);
                }
                else
                {
                    //有些脏数据.销毁cookie
                    WxUserBusiness::setCookieWxUserID($this->_mpUserID,'',-1);
                    $this->_redirectToErrorPage("无效的微信用户");
                }
            }
            else
            {
                log_warn("browser is not weixin : ", $_SERVER["HTTP_USER_AGENT"]);
                $this->_redirectToErrorPage("请从微信中访问该页面");
            }
        }
        //是订阅号
        if($mpUserType == 0)
        {
            //验证是否使用微信访问
            if (strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger"))
            {
                $this->_wxUserID = $this->_request->get( 'wx_user_id' );
                log_debug("[wxUserID:{$this->_wxUserID}][mpUserID:{$this->_mpUserID}]");

                $this->_wxUser = new WxUser([WxUser::WX_USER_ID => $this->_wxUserID]);
                log_debug("[wxUserID:{$this->_wxUserID}][mpUserID:{$this->_mpUserID}]");
                $this->_communityID = $this->_wxUser->getCurrentCommunityID();
                $this->_view->set('wx_user_id',$this->_wxUserID);
                $this->_view->set('mp_user_id', $this->_mpUserID);
                $this->_view->set('community_id', $this->_communityID);

            }
            else
            {
                log_warn("browser is not weixin : ", $_SERVER["HTTP_USER_AGENT"]);
                $this->_redirectToErrorPage("请从微信中访问该页面");
            }
        }

        $directoryPowerType = $this->_request->get( 'power_type' );
        if($directoryPowerType == DirectoryPowerType::REGISTER )
        {
            if($this->_wxUser->getPhone() == "")
            {
                $url = sprintf('/wx_user/user_info/register?mp_user_id=%s', $this->_mpUserID);
                $this->_gateway->redirect($url);
            }
        }

        if($directoryPowerType == DirectoryPowerType::IDENTIFY )
        {
            if(!WxUserBusiness::isMember($this->_wxUser))
            {
                $url = sprintf('/wx_user/user_info/index?mp_user_id=%s', $this->_mpUserID);
                $this->_gateway->redirect($url);

            }
        }
        if($directoryPowerType == DirectoryPowerType::OTHER )
        {
            if($this->_wxUser->getPhone() == "")
            {
                $url = sprintf('/wx_user/user_info/other?mp_user_id=%s', $this->_mpUserID);
                $this->_gateway->redirect($url);
            }
        }

        parent::_init();
    }

    protected function _certifiedUserRequired()
    {
        $wxUserID = $this->_request->get('wx_user_id');

        // 要求必须是已认证用户才能访问
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        if ($wxUser->isEmpty())
        {
            $this->_redirectToErrorPage("无效的微信用户");
        }
        else
        {
            $curCommunityID =  $wxUser->getCurrentCommunityID();
            if (empty($curCommunityID))
            {
                $this->_view->set('wx_user_id', $wxUserID);
                $this->changeView('WBT/WxUser/Certified.userRequired.html');
            }
        }
    }

    // 可以在构造函数中调用
    protected function _redirectToErrorPage($message)
    {
        $uri = sprintf('/error/weixin/?message=%s', utf8_encode($message));
        $this->_gateway->redirect($uri);
    }
}