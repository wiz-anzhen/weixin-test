<?php

namespace WBT\Business\Weixin;

use Bluefin\Controller;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\SuperAdmin;
use MP\Model\Mp\MpUserConfig;

class SuperAdminBusiness extends BaseBusiness
{
    public static function getMpUserConfigList( array $condition, array &$paging = null, $ranking,
                                         array $outputColumns = null )
    {
        return MpUserConfig::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function mpUserConfigInsert( $data )
    {
        $obj = new MpUserConfig();
        if($data[MpUserConfig::CONFIG_TYPE_TYPE] == 'bool')
        {
            $data[MpUserConfig::CONFIG_VALUE] = $data["bool"];
        }
        else if($data[MpUserConfig::CONFIG_TYPE_TYPE] == 'img')
        {
            $data[MpUserConfig::CONFIG_VALUE] = $data["img"];
        }
        $obj->apply( $data );
        if($obj->insertInRestraintOfUniqueKey())
        {
            return['errno' => 0];
        }
        else
        {
            return['errno' => 1,'error' => '不能和其他信息重复'];
        }
    }

    public static function mpUserConfigUpdate( $id, $data )
    {
        $obj = new MpUserConfig([ MpUserConfig::MP_USER_CONFIG_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find MpUserConfig($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        if($data[MpUserConfig::CONFIG_TYPE_TYPE] == 'bool' and $data['hide']=='change')
        {
            $data[MpUserConfig::CONFIG_VALUE] = $data["bool"];
        }
        else if($data[MpUserConfig::CONFIG_TYPE_TYPE] == 'img' and $data['hide']=='change')
        {
            $data[MpUserConfig::CONFIG_VALUE] = $data["img"];
        }
        else if($data[MpUserConfig::CONFIG_TYPE_TYPE] == 'text' and $data['hide']=='change')
        {
            $data[MpUserConfig::CONFIG_VALUE] = $data["text"];
        }
        $obj->apply( $data );
        if($obj->updateInRestraintOfUniqueKey())
        {
           return['errno' => 0];
        }
        else
        {
           return['errno' => 1,'error' => '不能和其他信息重复'];
        }

    }

    public static function mpUserConfigDelete( $id )
    {
        $obj = new MpUserConfig([ MpUserConfig::MP_USER_CONFIG_ID => $id ]);

        log_debug(print_r($id));
        if ($obj->isEmpty()) {
            log_debug( "Could not find MpUserConfig($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        $obj->delete();
        return [ 'errno' => 0 ];
    }
}