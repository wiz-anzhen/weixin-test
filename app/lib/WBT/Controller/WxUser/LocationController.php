<?php

namespace WBT\Controller\WxUser;

use Bluefin\App;
use Bluefin\Controller;
use WBT\Controller\WxUserControllerBase;


class LocationController extends WxUserControllerBase
{

    // 跳转URL
    public function indexAction()
    {
        $url = App::getInstance()->request()->get("url");
        $this->_view->set('url', $url);
//        $this->_gateway->redirect($url);
    }
}