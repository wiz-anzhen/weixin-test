<?php

use Common\Helper\OAuthByClient;

require_once '../../../../lib/Bluefin/bluefin.php';

$oauth = OAuthByClient::createHandler();

try
{
	$oauth->grantAccessToken();
}
catch (OAuth2ServerException $oauthError)
{
	$oauthError->sendHttpResponse();
}
catch (\Bluefin\Exception\BluefinException $be)
{
    $be->sendHttpResponse();
}