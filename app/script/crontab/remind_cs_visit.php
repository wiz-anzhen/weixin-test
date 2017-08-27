<?php

require_once '../../../lib/Bluefin/bluefin.php';
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\CustomerSpecialist;
use WBT\Business\Weixin\WxApiBusiness;
use MP\Model\Mp\MpUser;

remindCsVisit();

function remindCsVisit()
{
    $mpUserArray =  MpUser::fetchRows(['*'],[MpUser::VALID => 1]);
    foreach($mpUserArray as $mpUser)
    {
        if(ConfigBusiness::csAnswerEnabled($mpUser[MpUser::MP_USER_ID]))
        {
            $csArray = CustomerSpecialist::fetchRows([CustomerSpecialist::WX_USER_ID],
                                                [
                                                CustomerSpecialist::MP_USER_ID => $mpUser[MpUser::MP_USER_ID],
                                                CustomerSpecialist::VALID => 1
                                                ] );
            foreach($csArray as $cs)
            {
                $userMessage = \WBT\Business\ConfigBusiness::getCsClickWxMenuHint($mpUser[MpUser::MP_USER_ID]);
                if(isset($cs[CustomerSpecialist::WX_USER_ID]))
                {
                    WxApiBusiness::sentTextMessage($mpUser[MpUser::MP_USER_ID],
                                                   $cs[CustomerSpecialist::WX_USER_ID],$userMessage);
                }

            }
        }

    }

}