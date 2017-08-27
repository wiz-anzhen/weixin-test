<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\MpUserNav;

class MpUserNavBusiness
{
    public static function getMpUserNavList( array $condition, array &$paging = NULL, $ranking,
                                             array $outputColumns = NULL ) {
        return MpUserNav::fetchRowsWithCount( [ '*' ], $condition, NULL, $ranking, $paging,
            $outputColumns );
    }


    //编辑
    public static function update($mpUserId, $mpUserNavId, $title, $picUrl, $sortNo,
                                  $url, $navigationType ) {
        $mpUserNav = new MpUserNav([MpUserNav::MP_USER_ID => $mpUserId,
                                   MpUserNav::MP_USER_NAV_ID => $mpUserNavId]);

        if ($mpUserNav->isEmpty()) {
            log_debug( "Could not find MpUserNav($mpUserNavId)" );

            return FALSE;
        }

        $mpUserNav->setTitle( $title )->setPicUrl( $picUrl )->setSortNo( $sortNo )->setUrl( $url )
            ->setNavigationType( $navigationType )->update();

        return TRUE;
    }

    public static function remove($mpUserId, $mpUserNavId )
    {
        $mpUserNav = new MpUserNav([MpUserNav::MP_USER_ID => $mpUserId,
                                   MpUserNav::MP_USER_NAV_ID => $mpUserNavId]);
        if ($mpUserNav->isEmpty()) {
            log_warn( "Could not find MpUserNav($mpUserNavId)" );

            return FALSE;
        }
        $mpUserNav->delete();

        return TRUE;
    }

    public static function insert( $mpUserId, $title, $picUrl, $sortNo, $url, $navigationType )
    {
        $mpUserNav = new MpUserNav();

        $mpUserNav->setMpUserID( $mpUserId )->setTitle( $title )->setPicUrl( $picUrl )->setSortNo( $sortNo )
            ->setUrl( $url )->setNavigationType( $navigationType );

        try {
            return $mpUserNav->insert();
        } catch (\Exception $e) {
            return 0;
        }
    }

}