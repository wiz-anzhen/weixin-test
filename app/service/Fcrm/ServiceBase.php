<?php

use Bluefin\Service;

class ServiceBase extends Service
{
    public function __construct()
    {
        parent::__construct();
        parent::_requireAuth('weibotui');
        $this->_init();
    }

    protected function _init()
    {

    }

    protected function _checkRule( $mpUserId )
    {
        return TRUE;
    }
}