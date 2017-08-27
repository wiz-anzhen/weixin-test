<?php

namespace WBT\Controller;

use Bluefin\Controller;

class ErrorController extends Controller
{
    public function indexAction()
    {
        $message = $this->_request->get('message');
        $message = utf8_decode($message);
        $this->_view->set('message',$message);
    }
    public function webAction()
    {
        $message = $this->_request->get('message');
        $this->_view->set('message',$message);
    }

    //微信出错界面
    public function weixinAction()
    {
        $message = $this->_request->get('message');
        $message = utf8_decode($message);
        $this->_view->set('message',$message);
    }
}