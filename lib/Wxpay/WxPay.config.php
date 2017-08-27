<?php
/**
* 
*/

use WBT\Business\ConfigBusiness;

define(APPID , ConfigBusiness::getAppId());  //appid
define(APPKEY ,ConfigBusiness::getPaySignKey()); //paysign key
define(SIGNTYPE, "sha1"); //method
define(PARTNERKEY,ConfigBusiness::getPartnerKey());//通加密串
define(APPSERCERT, "");

?>