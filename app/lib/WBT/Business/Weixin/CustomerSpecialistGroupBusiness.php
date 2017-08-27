<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\CustomerSpecialistGroup;
use MP\Model\Mp\WxUser;

class CustomerSpecialistGroupBusiness extends BaseBusiness
{

    //列表的显示
    public static function getCustomerSpecialistGroupList(array $condition, array &$paging = null, $ranking = null, array $outputColumns = null)
    {
        return CustomerSpecialistGroup::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
    //数据的录入
    public static function insert($data)
    {
        $obj = new CustomerSpecialistGroup();
        $obj->apply( $data );
        try {
            $obj->insert();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }
    //修改
    public static function update( $id,$data )
    {
        $obj = new CustomerSpecialistGroup([ CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $id]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find TopDirectory($id)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }

        $obj->apply( $data );

        try {
            $obj->update();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }
    //删除
    public static function delete( $id )
    {
        $obj = new CustomerSpecialistGroup([ CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $id]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Store($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        try {
            $obj->delete();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }


}