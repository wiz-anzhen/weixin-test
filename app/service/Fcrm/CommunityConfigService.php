<?php

require_once 'MpUserServiceBase.php';

use MP\Model\Mp\CommunityConfig;

use WBT\Business\Weixin\CommunityConfigBusiness;

class CommunityConfigService extends MpUserServiceBase
{
    public function update()
    {
        $id  = $this->_app->request()->getQueryParam(CommunityConfig::COMMUNITY_CONFIG_ID);
        $data = $this->_app->request()->getArray( [ CommunityConfig::CONFIG_VALUE,] );

        return CommunityConfigBusiness::communityConfigUpdate( $id, $data );

    }

    public function insert()
    {
        $data = $this->_app->request()->getArray( [ CommunityConfig::CONFIG_TYPE,CommunityConfig::CONFIG_VALUE,CommunityConfig::MP_USER_ID,CommunityConfig::COMMUNITY_ID ] );

        return CommunityConfigBusiness::communityConfigInsert( $data );
    }

    public function delete()
    {
        $id = $this->_app->request()->get( CommunityConfig::COMMUNITY_CONFIG_ID );

        return CommunityConfigBusiness::communityConfigdelete( $id );
    }


}