<?php

namespace WBT\Controller\WxUser;

use Bluefin\App;
use Bluefin\Controller;
use MP\Model\Mp\CommunityPhoneBook;

class CommunityPhoneBookController extends \Common\Helper\BaseController
{
    public function indexAction()
    {
        $ID = App::getInstance()->request()->get('community_phone_book_id');

        $c = new CommunityPhoneBook([CommunityPhoneBook::COMMUNITY_PHONE_BOOK_ID => $ID]);
        if($c->isEmpty())
        {
            $this->_redirectToErrorPage( "无效的电话");
            return;
        }
        $this->_view->set('phone', $c->getPhone());
        $this->_view->set('name', $c->getName());
    }

}
