<?php

use WBT\Business\Weixin\MpUserBusiness;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\SuperAdmin;
use WBT\Business\Weixin\CommunityBusiness;
use WBT\Business\Weixin\WxApiBusiness;

require_once 'ServiceBase.php';

class MpUserService extends ServiceBase
{

    public function superAdminUpdate()
    {
        $res      = array( 'errno' => 0 );
        $request  = $this->_app->request();
        $mpUserId = $request->get( MpUser::MP_USER_ID );
        $fields   = [ MpUser::MP_NAME, MpUser::COMMENT, MpUser::APP_ID, MpUser::APP_SECRET,MpUser::MP_USER_TYPE,MpUser::CS_VISIBLE,MpUser::INDUSTRY,
            MpUser::SALE_LIST_NAME,MpUser::PARTNER_ID,MpUser::PARTNER_KEY,MpUser::PAY_SIGN_KEY,
            MpUser::MCHID,MpUser::PAY_KEY,MpUser::JS_API_CALL_URL,MpUser::SSLCERT_PATH,
            MpUser::SSLKEY_PATH,MpUser::NOTIFY_URL,MpUser::CURL_TIMEOUT,
            MpUser::SHARE_PIC,MpUser::CARD_LOGO, MpUser::CARD_LIST_DIRECTORY,
            MpUser::CARD_BACKGROUND, MpUser::OPEN_DATE, MpUser::FOLLOWED_CONTENT,MpUser::VALID];
        $data     = $request->getArray( $fields );
        if (!MpUserBusiness::superAdminUpdate( $mpUserId, $data ))
        {
            $res = [ 'errno' => 1, 'error' => '保存失败' ];
        }
        CommunityBusiness::updateCommunityValid($mpUserId,$data[MpUser::VALID]);

        return $res;
    }

    public function down()
    {
        //log_debug("6666666666666666666666666666666666666");
        $request  = $this->_app->request();
        $mpUserID = $request->get( MpUser::MP_USER_ID );
        $times = $request->get( 'times' );
        $accessToken = WxApiBusiness::getAccessToken($mpUserID);
        if(empty($accessToken))
        {
            return $return = [ 'errno' => 1, 'error' => 'token获取失败' ];
        }
        //log_debug("555555555555555555555555555".$accessToken);
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$accessToken."&next_openid=";
        $res = _curl_get($url);
        $res = (array)json_decode($res);
        //log_debug("[mpUserID:$mpUserID]", $res);
        //log_debug("333333333333333333333333333333333333",$res['next_openid']);
        $wxUserIDs= is_object($res['data']) ? get_object_vars($res['data']) : $res['data'];
        $wxUserIDs = $wxUserIDs['openid'];
        //log_debug("333333333333333333333333333333333333",$wxUserIDs);
        //log_debug("333333333333333333333333333333333333",$res['next_openid']);
        //log_debug("333333333333333333333333333333333333",$res['count'] );
        if($res['count']  >= 9999)
        { //log_debug("5555555555555555555555555555555555555555");
            $nextOpenid = $res['next_openid'];
            $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$accessToken."&next_openid=".$nextOpenid;
            $resNext = _curl_get($url);
            $resNext = (array)json_decode($resNext);
            $wxUserIDsNext= is_object($resNext['data']) ? get_object_vars($resNext['data']) : $resNext['data'];
            $wxUserIDsNext = $wxUserIDsNext['openid'];
            //log_debug("[mpUserID:$mpUserID]", $wxUserIDsNext);
            $wxUserIDs = array_merge($wxUserIDs,$wxUserIDsNext);
        }

        $wxUserIDs = array_slice($wxUserIDs,$times*200,200);

        foreach($wxUserIDs as$key => $wxUserID)
        {
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$accessToken."&openid=".$wxUserID."&lang=zh_CN";
            $res = _curl_get($url);
            $res = (array)json_decode($res);

            if(!strict_in_array("errcode",$res))
            {
                $nickname = $res['nickname'];
                $headPic =  $res['headimgurl'];
                $subscribeTime =  $res['subscribe_time'];
                if(empty($subscribeTime))
                {
                    $subscribeTime = time();
                }

            }
            else
            {
                log_warn("[mpUserID:$mpUserID][wxUserID:$wxUserID]res = ", $res);
                return false;
            }
            //log_warn("[mpUserID:$mpUserID][wxUserID:$wxUserID]res = ", $res);
            $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
            if($wxUser->isEmpty())
            {
                $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
                if(empty($headPic))
                {
                    $wxUser->setVipNo(\WBT\Business\Weixin\WxUserBusiness::generateVipNo($mpUser))->setIsFans(1)->setMpUserID($mpUserID)->setNick($nickname)->setCreateTime($subscribeTime)->setWxUserID($wxUserID)->insert();
                }
                else
                {
                    $wxUser->setVipNo(\WBT\Business\Weixin\WxUserBusiness::generateVipNo($mpUser))->setIsFans(1)->setMpUserID($mpUserID)->setNick($nickname)->setHeadPic($headPic)->setCreateTime($subscribeTime)->setWxUserID($wxUserID)->insert();
                }

            }
            else
            {
                if(empty($headPic))
                {
                    $wxUser->setNick($nickname)->update();
                }
                else
                {
                    $wxUser->setNick($nickname)->setHeadPic($headPic)->update();
                }
            }
            log_debug($times."-----------------------------------------------------------------------------".$key);

        }
        if(count($wxUserIDs) >= 200)
        {
            $return = [ 'times' =>  $times+1 ];
        }
        else
        {
            $return = [ 'errno' => 0,'times' => 0];
        }

        return $return;
    }
    public function superAdminAdd() {
        $res                    = array( 'errno' => 0 );
        $request                = $this->_app->request();
        //$mpUserId               = $request->get( MpUser::MP_USER_ID );
        $data = [ MpUser::MP_NAME                  => $request->get( MpUser::MP_NAME ),
            MpUser::COMMENT                  => $request->get( MpUser::COMMENT ),
            MpUser::VALID                    => $request->get( MpUser::VALID ) ];


        $username  = \WBT\Business\UserBusiness::getLoginUsername();
        $superAdmin = new SuperAdmin([ SuperAdmin::USERNAME => $username ]);
        if (!$superAdmin->isEmpty()) {
            if (!MpUserBusiness::superAdminAdd( $data )) {
                $error        = '保存失败';
                $res['errno'] = 1;
                $res['error'] = $error;

                return $res;
            } else {
                return $res;
            }
        } else {
            $error        = '没有权限更改该记录';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }
    }





}