<?php

namespace WBT\Controller\App;

use Bluefin\App;
use Bluefin\Controller;
use MP\Model\Mp\Community;

class CommunityPhoneController extends \Common\Helper\BaseController
{
    public function indexAction()
    {
        $communityID = App::getInstance()->request()->get('community_id');

        $c = new Community([Community::COMMUNITY_ID => $communityID]);
        if($c->isEmpty())
        {
            $this->_redirectToErrorPage( "无效的小区");
            return;
        }
        $this->_view->set('phone', $c->getPhone());
        $this->_view->set('community_name', $c->getName());
    }

}
