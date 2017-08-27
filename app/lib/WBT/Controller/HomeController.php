<?php

namespace WBT\Controller;

use Bluefin\App;
use Common\Helper\BaseController;
use WBT\Business\AuthBusiness;
use WBT\Business\UserBusiness;
use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\Community;

class HomeController extends BaseController
{
    public function indexAction()
    {
        if (AuthBusiness::isLoggedIn())
        {
            $userID = UserBusiness::getLoginUser()->getUserID();

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
                        ["mp_user_id" => $mpUserId]));
                }
            }
            else
            {
                log_debug("[userID:$userID]");

                $this->_gateway->redirect( $this->_gateway->path( "mp_admin/super_admin_list/list" ) );
            }
        }
        else
        {
            $this->_gateway->redirect( $this->_gateway->path( "auth/index" ) );
        }
    }

    public function sessionAction()
    {
        if (ENV != 'dev')
        {
            throw new \Bluefin\Exception\PageNotFoundException();
        }

        echo '<pre>' . nl2br(\Symfony\Component\Yaml\Yaml::dump($_SESSION, 10)) . '</pre>';
    }

    public function cacheAction()
    {
        if (ENV != 'dev')
        {
            throw new \Bluefin\Exception\PageNotFoundException();
        }

        $cacheID = $this->_request->getQueryParam('id');

        $cache = App::getInstance()->cache($cacheID);

        if ($this->_request->has('info'))
        {
            $data = $cache->getHandlerObject()->info();
        }
        else if ($this->_request->has('keyspace'))
        {
            $data = $cache->getHandlerObject()->info('keyspace');
        }
        else if ($this->_request->has('size'))
        {
            $data = $cache->getHandlerObject()->dbSize();
        }
        else if ($this->_request->has('keys'))
        {
            $data = $cache->getHandlerObject()->keys('*');
        }
        else
        {
            $data = $cache->get();
        }

        echo '<pre>' . nl2br(\Symfony\Component\Yaml\Yaml::dump($data, 10)) . '</pre>';
    }

    public function testPayAction()
    {
        //长微博支付测试页
    }
}