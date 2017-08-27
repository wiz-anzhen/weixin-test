<?php

namespace WBT\Controller\WxApi;

use Bluefin\Controller;
use WBT\Business\Weixin\ApiBusiness;
use MP\Model\Mp\MpUser;

class ApiController extends Controller
{
    public function idAction()
    {
        $apiID = $this->_request->getRouteParam('apiid');
        log_debug("[apiID:$apiID]");

        $mpUser = new MpUser([MpUser::API_ID => $apiID]);
        if($mpUser->isEmpty())
        {
            log_warning("unknown api id. [apiID:$apiID]");
            return;
        }

        $urlParams =  $this->getRequest()->getQueryParams();
        $postStr =  $this->getRequest()->getRawBody();

        $responseStr =  ApiBusiness::processRequest($mpUser, $urlParams, $postStr);
        echo $responseStr;
    }
}
