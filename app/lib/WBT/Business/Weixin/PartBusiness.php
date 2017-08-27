<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\Community;
use MP\Model\Mp\Directory;
use MP\Model\Mp\Store;
use MP\Model\Mp\Category;
use MP\Model\Mp\Restaurant;
use MP\Model\Mp\Part;
use MP\Model\Mp\CommunityType;
use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\CommunityAdminPowerType;
use WBT\Business\Weixin\SendTemplateBusiness;

class PartBusiness extends BaseBusiness
{
    public static function getList( array $condition, array &$paging = null, $ranking,
                                         array $outputColumns = null )
    {
        return Part::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function partInsert( $data )
    {
        $obj = new Part();
        $obj->apply( $data );
        $obj->insert();
        return [ 'errno' => 0 ];

    }


    public static function partUpdate( $id, $data )
    {
        $obj = new Part([ Part::PART_ID => $id ]);
        $obj->apply( $data );
        $obj->update();
        return [ 'errno' => 0 ];
    }

    public static function partDelete( $id )
    {
        $obj = new Part([ Part::PART_ID => $id ]);
        if ($obj->isEmpty()) {
            log_debug( "Could not find Store($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }

        $obj->delete();
        return [ 'errno' => 0 ];
    }

}