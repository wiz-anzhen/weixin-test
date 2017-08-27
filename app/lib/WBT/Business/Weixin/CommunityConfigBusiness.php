<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\CommunityConfig;

/* 点菜相关业务 */
class CommunityConfigBusiness extends BaseBusiness
{

    //列表的显示
    public static function getCommunityConfigList(array $condition, array &$paging = null, $ranking, array $outputColumns = null)
    {
        return CommunityConfig::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
    //数据的录入
    public static function communityConfigInsert($data)
    {
        $obj = new CommunityConfig();
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
    //修改
    public static function communityConfigUpdate( $id,$data )
    {
        $obj = new CommunityConfig([ CommunityConfig::COMMUNITY_CONFIG_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find CommunityConfig($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
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
    //删除
    public static function communityConfigDelete( $id )
    {
        $obj = new CommunityConfig([ CommunityConfig::COMMUNITY_CONFIG_ID => $id ]);
        if ($obj->isEmpty()) {
            log_debug( "Could not find CommunityConfig($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        $obj->delete();
        return [ 'errno' => 0 ];
    }



}