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
use MP\Model\Mp\CompanyAdmin;

class WBTControllerBase extends BaseController
{
    protected $_userID;
    protected $_username;
    protected $_mpUserID;
    protected $_isMpAdmin;
    protected $_isCompanyAdmin;

    protected function _init()
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
        //add company 权限
        elseif (CompanyAdmin::fetchCount([CompanyAdmin::MP_USER_ID => $this->_mpUserID,
                CompanyAdmin::USERNAME => UserBusiness::getLoginUsername()]) > 0)
        {
            $this->_view->set( 'is_company_admin', true );
            $this->_isCompanyAdmin = true;
        }
        elseif(in_array($communityAdminCommunityId ,$communityIdArray))
        {
            $this->_view->set('is_community_admin', TRUE);
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
        log_debug("$message");
        $uri = sprintf('/error/index/?message=%s', utf8_encode($message));
        $this->_gateway->redirect($uri);
    }
}
