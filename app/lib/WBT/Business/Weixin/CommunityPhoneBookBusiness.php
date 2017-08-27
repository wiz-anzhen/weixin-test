<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\CommunityPhoneBook;
use MP\Model\Mp\WxUser;

/* 点菜相关业务 */
class CommunityPhoneBookBusiness extends BaseBusiness
{

    //列表的显示
    public static function getCommunityPhoneBookList(array $condition, array &$paging = null, $ranking, array $outputColumns = null)
    {
        return CommunityPhoneBook::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
    //数据的录入
    public static function insert($data)
    {
        $obj = new CommunityPhoneBook();
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
        $obj = new CommunityPhoneBook([ CommunityPhoneBook::COMMUNITY_PHONE_BOOK_ID => $id]);

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
        $obj = new CommunityPhoneBook([ CommunityPhoneBook::COMMUNITY_PHONE_BOOK_ID => $id]);

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