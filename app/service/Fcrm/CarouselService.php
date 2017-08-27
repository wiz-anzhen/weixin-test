<?php

require_once 'MpUserServiceBase.php';

use MP\Model\Mp\Carousel;
use MP\Model\Mp\Album;
use MP\Model\Mp\Picture;
use WBT\Business\Weixin\CarouselBusiness;

class CarouselService extends MpUserServiceBase
{
    // 轮播
    public function carouselUpdate()
    {
        $id   = $this->_app->request()->getQueryParam( Carousel::CAROUSEL_ID );
        $communityId = $this->_app->request()->getQueryParam( Carousel::COMMUNITY_ID );
        $data = $this->_app->request()->getArray( [ Carousel::TITLE, Carousel::COMMENT, ] );

        return CarouselBusiness::carouselUpdate( $communityId,$id, $data );
    }

    public function carouselInsert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ Carousel::COMMUNITY_ID,Carousel::MP_USER_ID, Carousel::TITLE, Carousel::COMMENT, ] );

        return CarouselBusiness::carouselInsert( $data );
    }

    public function carouselDelete()
    {
        $id = $this->_app->request()->get( Carousel::CAROUSEL_ID );
        $communityId = $this->_app->request()->getQueryParam( Carousel::COMMUNITY_ID );

        return CarouselBusiness::carouselDelete( $communityId,$id );
    }

    // 相册
    public function albumInsert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ Album::COMMUNITY_ID,Album::MP_USER_ID, Album::CAROUSEL_ID, Album::TITLE,
                                       Album::COVER_IMG, Album::SORT_NO, Album::COMMENT, ] );

        return CarouselBusiness::albumInsert( $data );
    }

    public function albumUpdate()
    {
        $id   = $this->_app->request()->getQueryParam( Album::ALBUM_ID );
        $communityId = $this->_app->request()->getQueryParam( Carousel::COMMUNITY_ID );
        $data = $this->_app->request()->getArray( [ Album::TITLE, Album::COVER_IMG, Album::SORT_NO, Album::COMMENT, ] );

        return CarouselBusiness::albumUpdate( $communityId,$id, $data );
    }

    public function albumDelete()
    {
        $id = $this->_app->request()->get( Album::ALBUM_ID );
        $communityId = $this->_app->request()->getQueryParam( Carousel::COMMUNITY_ID );

        return CarouselBusiness::albumDelete( $communityId,$id );
    }

    // 图片
    public function pictureInsert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ Picture::COMMUNITY_ID,Picture::MP_USER_ID, Picture::CAROUSEL_ID, Picture::ALBUM_ID,
                                       Picture::IMG_URL, Picture::SORT_NO, Picture::COMMENT, ] );

        return CarouselBusiness::pictureInsert( $data );
    }

    public function pictureUpdate()
    {
        $id   = $this->_app->request()->getQueryParam( Picture::PICTURE_ID );
        $communityId = $this->_app->request()->getQueryParam( Carousel::COMMUNITY_ID );
        $data = $this->_app->request()->getArray( [ Picture::IMG_URL, Picture::SORT_NO, Picture::COMMENT, ] );

        return CarouselBusiness::pictureUpdate( $communityId,$id, $data );
    }

    public function pictureUpdateCover()
    {
        $img_url = $this->_app->request()->getQueryParam( 'img_url');
        $albumId = $this->_app->request()->getQueryParam( 'album_id');
        return CarouselBusiness::albumUpdateCover( $albumId , $img_url);
    }

    public function pictureDelete()
    {
        $id = $this->_app->request()->get( Picture::PICTURE_ID );
        $communityId = $this->_app->request()->getQueryParam( Carousel::COMMUNITY_ID );
        return CarouselBusiness::pictureDelete( $communityId,$id );
    }
}