<?php

use MP\Model\Mp\MpUser;
use MP\Model\Mp\SuperAdmin;
use WBT\Business\UserBusiness;
use MP\Model\Mp\MpUserConfig;
use WBT\Business\Weixin\SuperAdminBusiness;

require_once 'MpUserServiceBase.php';

class SuperAdminService extends MpUserServiceBase
{
    public function removeMpUser()
    {
        $username = UserBusiness::getLoginUsername();
        $superAdmin = new SuperAdmin([SuperAdmin::USERNAME => $username]);
        if ($superAdmin->isEmpty() || $superAdmin->getHasDeletePower() == 0) {
            log_info('非法的请求，没有权限的用户试图删除公众号');
            return ['errno' => 1, 'error' => '没有权限删除公众号'];
        }

        $mpUserId = $this->_app->request()->getQueryParam( MpUser::MP_USER_ID );

        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        if ($mpUser->isEmpty()) {
            return ['errno' => 1, 'error' => '找不到公众号'];
        }
        $mpUser->setValid(0)->update();
        return ['errno' => 0, 'error' => '公众号删除成功'];
    }
    public function mpUserConfigUpdate()
    {
        $id  = $this->_app->request()->getQueryParam(MpUserConfig::MP_USER_CONFIG_ID);
        $data = $this->_app->request()->getArray( [ MpUserConfig::CONFIG_VALUE,MpUserConfig::MP_USER_ID,MpUserConfig::CONFIG_TYPE_TYPE,'bool','img','text','hide'] );
         log_debug("kkkkkkkkkkk55555555555",$data);
        return SuperAdminBusiness::mpUserConfigUpdate( $id, $data );

    }

    public function mpUserConfigInsert()
    {
        $data = $this->_app->request()->getArray( [ MpUserConfig::CONFIG_TYPE,MpUserConfig::CONFIG_VALUE,MpUserConfig::MP_USER_ID,MpUserConfig::CONFIG_TYPE_TYPE ,'bool','img'] );

        return SuperAdminBusiness::mpUserConfigInsert( $data );
    }

    public function mpUserConfigDelete()
    {
        $id= $this->_app->request()->getQueryParam(  MpUserConfig::MP_USER_CONFIG_ID );
        return SuperAdminBusiness::mpUserConfigDelete( $id );
    }

}