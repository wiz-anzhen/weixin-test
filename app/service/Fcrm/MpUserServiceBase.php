<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 14-8-6
 * Time: 下午5:27
 */

use Bluefin\Service;

class MpUserServiceBase extends Service
{
    public function __construct()
    {
        parent::__construct();
        parent::_requireAuth('weibotui');
    }

} 