<?php

namespace WBT\Controller\Home;

use WBT\Controller\WBTControllerBase;
use WBT\Model\Weibotui\UserStatus;
use WBT\Business\UserBusiness;

class WeibotuiController extends WBTControllerBase
{
    protected $_auth;

    protected function _init()
    {
        parent::_init();

        //要求是微博推用户身份
        $this->_requireWeibotuiLoginRole();
    }

    protected function _requireWeibotuiLoginRole()
    {
        $this->_auth = $this->_requireAuth('weibotui');

        $this->_setUserProfileAndRolesInView();
    }

    public function indexAction()
    {
        $this->_checkAccountStatus($this->_auth);
    }

    public function nonactivatedAction()
    {
        $accountStatus = $this->_auth->getData('status');

        if ($accountStatus == UserStatus::ACTIVATED)
        {
            $userID = UserBusiness::getLoginUserID();
            $this->_gateway->redirect( $this->_gateway->path( "mp_admin/mp_user/list?user_id={$userID}" ) );
        }
    }

    public function activateAction()
    {
        $username = $this->_auth->getData('username');
        UserBusiness::sendVerificationEmail($username);
        $this->_gateway->redirect($this->_signUrl('register/verify_email', ['email' => $username], true));
    }
}
