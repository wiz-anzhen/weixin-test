<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 14-7-4
 * Time: 下午12:56
 */
require_once '../../../lib/Bluefin/bluefin.php';
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\WxApiBusiness;

updateWxUserHeadUrl();

function updateWxUserHeadUrl()
{
    $wxUserArray = WxUser::fetchRows(['*']);
    foreach($wxUserArray as $wxUser)
    {
        $nickname = '';
        $head_url = WxApiBusiness::getHeadImgUrl($wxUser[WxUser::MP_USER_ID],
                                                 $wxUser[WxUser::WX_USER_ID],
                                                 $nickname);

        $wxUserUpdate = new WxUser([WxUser::MP_USER_ID=>$wxUser[WxUser::MP_USER_ID],
                                    WxUser::WX_USER_ID=>$wxUser[WxUser::WX_USER_ID]]);

        $wxUserUpdate->setHeadPic($head_url)->update();
    }
}