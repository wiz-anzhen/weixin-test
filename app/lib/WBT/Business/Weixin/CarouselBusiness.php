<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\Album;
use MP\Model\Mp\Carousel;
use MP\Model\Mp\Picture;
use WBT\Business\Weixin\MpUserBusiness;
require_once LIB.'/JSSDK/jssdk.php';
class CarouselBusiness extends BaseBusiness
{
    public static function getCarouselList( array $condition, array &$paging = null, $ranking,
                                           array $outputColumns = null )
    {
        return Carousel::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function carouselInsert( $data )
    {
        $obj = new Carousel();
        $obj->apply( $data );
        try {
            $obj->insert();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }

        return ['errno' => 0];
    }

    public static function carouselUpdate( $communityId,$id, $data )
    {
        $obj = new Carousel([ Carousel::CAROUSEL_ID => $id ,Carousel::COMMUNITY_ID => $communityId]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Carousel($id)" );

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

    public static function carouselDelete( $communityId,$id )
    {
        $obj = new Carousel([ Carousel::CAROUSEL_ID => $id ,Carousel::COMMUNITY_ID => $communityId]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Carousel($id)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }
        try {
            $obj->delete();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }

    public static function getAlbumList( $carouselId )
    {
        $condition     = [ Album::CAROUSEL_ID => $carouselId ];
        $ranking       = [ Album::SORT_NO ];
        $paging        = [ ];
        $outputColumns = Album::s_metadata()->getFilterOptions();

        return Album::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function albumInsert( $data )
    {
        $obj = new Album();
        $obj->apply( $data );
        try {
            $obj->insert();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }

        return ['errno' => 0];
    }

    public static function albumUpdate( $communityId,$id, $data )
    {
        $obj = new Album([ Album::ALBUM_ID => $id,Album::COMMUNITY_ID => $communityId ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Album($id)" );

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

    public static function albumUpdateCover( $albumId, $img_url )
    {
        $obj = new Album([ Album::ALBUM_ID => $albumId ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Album($albumId)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }

        try {
            $obj->setCoverImg($img_url )->update();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }
        return ['errno' => 0];
    }


    public static function albumDelete( $communityId,$id )
    {
        $obj = new Album([ Album::ALBUM_ID => $id,Album::COMMUNITY_ID => $communityId ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Album($id)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }
        try {
            $obj->delete();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }
    public static function getPictureList( $albumId )
    {
        $condition     = [ Picture::ALBUM_ID => $albumId ];
        $ranking       = [ Picture::SORT_NO ];
        $paging        = [ ];
        $outputColumns = Picture::s_metadata()->getFilterOptions();

        return Picture::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
    public static function getSignPackage($mpUserId)
    {
        $mpUser = MpUserBusiness::getMpUser($mpUserId);
        $appId = $mpUser->getAppID();
        $appSecret = $mpUser->getAppSecret();
        $jssdk = new \JSSDK($appId, $appSecret);
        return $signPackage = $jssdk->GetSignPackage();
    }

    public static function pictureInsert( $data )
    {
        $obj = new Picture();
        $obj->apply( $data );
        try {
            $obj->insert();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }

        return ['errno' => 0];
    }

    public static function pictureUpdate( $communityId,$id, $data )
    {
        $obj = new Picture([ Picture::PICTURE_ID => $id,Picture::COMMUNITY_ID => $communityId ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Picture($id)" );

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

    public static function pictureDelete( $communityId,$id )
    {
        $obj = new Picture([ Picture::PICTURE_ID => $id,Picture::COMMUNITY_ID => $communityId ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Picture($id)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }
        try {
            $obj->delete();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }

}