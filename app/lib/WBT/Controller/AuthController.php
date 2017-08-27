<?php

namespace WBT\Controller;

use Bluefin\App;
use Bluefin\Auth\AuthHelper;
use Bluefin\Auth\AuthInterface;
use Common\Helper\BaseController;

use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\Community;
use MP\Model\Mp\MpUser;
use WBT\Business\ConfigBusiness;
use WBT\Business\UserBusiness;
use WBT\Business\AuthBusiness;
use WBT\Model\Weibotui\UserStatus;
use Common\Data\Event;


class AuthController extends BaseController
{
    /**
     * 登录界面入口。
     */
    public function indexAction() {

        if ($this->_request->isPost())
        {
            $flag = AuthBusiness::login( $this->_request->getPostParams() );

            if (AuthHelper::SUCCESS === $flag)
            {
                $from = $this->_request->get("_from");
                log_debug("============================",$from);
                $host = ConfigBusiness::getHost();
                if(!empty($from))
                {
                    $this->_gateway->redirect( $host.$from );
                }
                else
                {
                    $auth = $this->_app->auth( 'weibotui' );
                    $this->_checkAccountStatus( $auth );
                    $userID = UserBusiness::getLoginUser()->getUserID();
                    $username = UserBusiness::getLoginUser()->getUsername();
                    $communityAdmin = new CommunityAdmin([CommunityAdmin::USERNAME => $username]);
                    if(!$communityAdmin->isEmpty())
                    {
                        $communityId = $communityAdmin -> getCommunityID();

                        if(!empty($communityId))
                        {
                            $community = new Community([Community::COMMUNITY_ID => $communityId]);
                            $mpUserId = $community -> getMpUserID();

                            $this->_gateway->redirect( $this->_gateway->path( "mp_admin/community/list",
                                ["mp_user_id" => $mpUserId,"community_id" => $communityId]));
                        }
                    }
                    else
                    {
                        log_debug("[userID:$userID]");

                        $this->_gateway->redirect( $this->_gateway->path( "mp_admin/super_admin_list/list" ) );
                    }
                }


            }
            else
            {
                $this->_setEventMessage( $flag, Event::SRC_AUTH, Event::LEVEL_ERROR );
                $this->_transferPostStates();

                return;
            }
        }


        if (strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger"))
        {
            $this->changeView('WBT/Auth.weixin.html');
        }


    }

    protected function _checkAccountStatus( AuthInterface $auth )
    {

    }

    /**
     * 注销登录。
     */
    public function logoutAction() {
        AuthBusiness::logout();
        $this->_gateway->redirect( App::getInstance()->rootUrl() );
    }
}