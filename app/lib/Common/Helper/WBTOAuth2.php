<?php

namespace Common\Helper;

use OAuth2;
use Bluefin\App;

class WBTOAuth2 extends OAuth2
{
    protected function genAccessToken()
    {
        return UniqueIdentity::generate(40);
    }
}
