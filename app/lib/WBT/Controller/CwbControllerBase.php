<?php

namespace WBT\Controller;

use Bluefin\Controller;
use Common\Data\Event;
use Common\Helper\BaseController;
use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\Community;
use MP\Model\Mp\MpAdmin;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\SuperAdmin;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\MpUserBusiness;
use WBT\Model\Weibotui\UserStatus;
use Bluefin\Auth\AuthInterface;
use WBT\Business\Weixin\WxUserBusiness;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\CompanyAdmin;

class CwbControllerBase extends BaseController
{
    protected $_userID;
    protected $_username;
    protected $_mpUserID;
    protected $_isMpAdmin;
    //微信页面初始化所用常量
    protected $_wxUserID;
    protected $_communityID;
    protected $_wxUser;

    protected function _init()
    {   log_debug("11111111111111111111111");
        if(strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger"))
        {
            $this->_mpUserID = $this->_request->get( 'mp_user_id' );
            $backUrl = base64_encode($this->_request->getFullRequestUri());
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
                    $this->_mpUserID = $this->_wxUser->getMpUserID();
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
        else
        {
            $this->_isMpAdmin = false;
            $this->_userID   = UserBusiness::getLoginUser()->getUserID();
            $this->_username = UserBusiness::getLoginUser()->getUsername();
            $this->_view->set('username', $this->getSimpleUsername($this->_username));
            $this->_view->set('username_all', $this->_username);
            $this->_mpUserID = $this->_request->get( 'mp_user_id' );
            $communityId = $this->_request->get("community_id");
            $this->_view->set('community_id', $communityId);
            $this->_view->set('mp_user_id', $this->_mpUserID);
            log_debug( "[userId:{$this->_userID}][mpUserId:{$this->_mpUserID}]" );
            log_debug( strlen( $this->_mpUserID ) );

            $auth = $this->_requireAuth( 'weibotui' );
            $this->_checkAccountStatus( $auth );

            $communityAdmin = new CommunityAdmin([CommunityAdmin::USERNAME => $this->_username]);
            $communityIdArray = CommunityAdmin::fetchColumn(CommunityAdmin::COMMUNITY_ID,[CommunityAdmin::USERNAME => $this->_username]);
            $communityAdminCommunityId = null;
            if(!$communityAdmin ->isEmpty())
            {
                $communityAdminCommunityId = $communityAdmin->getCommunityID();
            }


            if (empty( $this->_mpUserID ))
            {
                log_warn("[mpUserId:{$this->_mpUserID}] 缺少公共账号 ID 参数");
                $this->_redirectToErrorPage('缺少公共账号 ID 参数' );
            }
            $this->_urlSignature = 'hou8e';
            $superAdmin = new SuperAdmin([ SuperAdmin::USERNAME => $this->_username ]);
            $companyAdmin = new CompanyAdmin([CompanyAdmin::USERNAME=>$this->_username,CompanyAdmin::MP_USER_ID=>$this->_mpUserID]);
            if (!$superAdmin->isEmpty())
            {
                $this->_view->set( 'is_super_admin', true );
                $this->_view->set( 'is_mp_admin', true );
                $this->_isMpAdmin = true;
            }
            elseif (MpAdmin::fetchCount([MpAdmin::MP_USER_ID => $this->_mpUserID,
                    MpAdmin::USERNAME => UserBusiness::getLoginUsername()]) > 0)
            {
                $this->_view->set( 'is_mp_admin', true );
                $this->_isMpAdmin = true;
            }
            elseif(in_array($communityAdminCommunityId ,$communityIdArray))
            {
                $this->_view->set('is_community_admin', TRUE);
            }
            elseif(!$companyAdmin->isEmpty())
            {
                $this->_view->set( 'is_mp_admin', true );
                $this->_isMpAdmin = true;
            }
            else
            {
                log_warn("[userID:{$this->_userID}] 没有权限访问该页面");
                $this->_redirectToErrorPage('试图访问未授权的公共账号信息');
            }

            // my accounts
            $outputColumns = MpUser::s_metadata()->getFilterOptions();
            $ranking       = [ MpUser::MP_USER_ID ];
            $paging        = [];
            $data          = MpUserBusiness::getMpUserList($this->_username, $ranking, $paging, $outputColumns );
            $this->_view->set('my_accounts', $data);
        }

        parent::_init();
    }



    protected function _checkAccountStatus( AuthInterface $auth )
    {

    }

    protected function _setUserProfileAndRolesInView() {
        $userProfiles = UserBusiness::getUserProfileFromSession();

        $this->_view->set( 'loginProfile', $userProfiles );
        $this->_view->set( 'userRoles', $this->_app->role( 'weibotui' )->get() );
    }

    // 可以在构造函数中调用
    protected function _redirectToErrorPage( $message) {
        if(strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger"))
        {
            $uri = sprintf('/error/weixin/?message=%s', utf8_encode($message));
        }
        else
        {
            $uri = sprintf('/error/index/?message=%s', utf8_encode($message));
        }

        $this->_gateway->redirect($uri);
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
}
