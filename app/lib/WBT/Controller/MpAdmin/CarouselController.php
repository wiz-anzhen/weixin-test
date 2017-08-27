<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\Album;
use MP\Model\Mp\Carousel;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\Picture;
use MP\Model\Mp\Community;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\CarouselBusiness;
use WBT\Controller\CommunityControllerBase;

class CarouselController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "img_carousel";
        parent::_init();
    }
    public function carouselAction()
    {
        $mpUserID = $this->_request->get( 'mp_user_id' );
        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserID ]);
        $communityId = $this->_request->get( Carousel::COMMUNITY_ID );
        $this->_view->set( 'community_id', $communityId);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );

        $condition     = [ Carousel::MP_USER_ID => $mpUserID,Carousel::COMMUNITY_ID => $communityId ];
        $paging        = array();
        $outputColumns = Carousel::s_metadata()->getFilterOptions();
        $ranking       = [ Carousel::CAROUSEL_ID ];
        $data          = CarouselBusiness::getCarouselList( $condition, $paging, $ranking, $outputColumns );

        $power = $this->checkChangePower("img_carousel_rw","img_carousel_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('img_carousel_rw', $checkReadPower);
        $shownColumns = [
                Carousel::TITLE,
                Carousel::COMMENT,
                Table::COLUMN_OPERATIONS =>
                    [
                    Table::COLUMN_TITLE => "操作",
                    Table::COLUMN_FUNCTION => function (array $row)use($power)
                        {
                            $carouselID = $row[Carousel::CAROUSEL_ID];
                            $communityID =  $row[Carousel::COMMUNITY_ID];
                            $mpUserID= $row[Carousel::MP_USER_ID];
                            $album = new Link("相册", "/mp_admin/carousel/album?carousel_id={$carouselID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                            $carousel = new Link("轮播", "/wx_user/carousel/carousel?carousel_id={$carouselID}&community_id={$communityID}", [ 'target' => '_blank' ]);
                            $carouselWithWords = new Link("有字轮播", "/wx_user/carousel/carousel?carousel_id={$carouselID}&community_id={$communityID}&name=1", [ 'target' => '_blank' ]);
                            $ret = $album."<br>".$carousel."<br>".$carouselWithWords;

                            return $ret;
                        }
                    ], ];

        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] = [
                    Table::COLUMN_CELL_STYLE => 'width:10%',
                    Table::COLUMN_TITLE => "操作",
                    Table::COLUMN_FUNCTION => function (array $row)use($power)
                        {
                            $carouselID = $row[Carousel::CAROUSEL_ID];
                            $communityID =  $row[Carousel::COMMUNITY_ID];
                            $mpUserID= $row[Carousel::MP_USER_ID];
                            $update =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/carousel_dialog/carousel_update?carousel_id={$carouselID}&community_id={$communityID}');");
                            $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该轮播下的相册及所含的图片都将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/carousel/carousel_delete?carousel_id={$carouselID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");
                            $album = new Link("相册", "/mp_admin/carousel/album?carousel_id={$carouselID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                            $carousel = new Link("轮播", "/wx_user/carousel/carousel?carousel_id={$carouselID}&community_id={$communityID}", [ 'target' => '_blank' ]);
                            $carouselWithWords = new Link("有字轮播", "/wx_user/carousel/carousel?carousel_id={$carouselID}&community_id={$communityID}&name=1", [ 'target' => '_blank' ]);
                            $ret = $update."<br>".$album."<br>".$carousel."<br>".$carouselWithWords;
                            if($power["delete"])
                            {
                                $ret .= "<br>".$delete;
                            }
                            return $ret;
                        }
                     ];
        }

        $table               = Table::fromDbData( $data, $outputColumns,
            Carousel::CAROUSEL_ID, null, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }

    public function albumAction()
    {
        $mpUserId = $this->_mpUserID;
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $carouselId = $this->_request->getQueryParam( Carousel::CAROUSEL_ID );
        $carousel = new Carousel([Carousel::CAROUSEL_ID => $carouselId,Carousel::COMMUNITY_ID => $communityId]);
        $this->_view->set('carousel', $carousel->data());
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $data          = CarouselBusiness::getAlbumList( $carouselId );

        $outputColumns = Album::s_metadata()->getFilterOptions();
        $power = $this->checkChangePower("img_carousel_rw","img_carousel_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('img_carousel_rw', $checkReadPower);
        $showColumns = [ Album::TITLE,
            Album::COVER_IMG   => [ Table::COLUMN_FUNCTION => function ( array $row )
                {
                    return sprintf( '<img src="%s" width="100px" height="100px" alt="没有图片"/>',
                        $row[Album::COVER_IMG] );
                } ],

            Album::SORT_NO,
            Album::COMMENT,

            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_CELL_STYLE => 'width:15%',
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_FUNCTION => function (array $row)use($power)
                    {
                        $albumID = $row[Album::ALBUM_ID];
                        $communityID =  $row[Album::COMMUNITY_ID];
                        $mpUserID= $row[Album::MP_USER_ID];
                        $carouselID = $row[Album::CAROUSEL_ID];
                        $pic = new Link("图片", "/mp_admin/carousel/picture?carousel_id={$carouselID}&album_id={$albumID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                        $carousel = new Link("轮播", "/wx_user/carousel/album?album_id={$albumID}&community_id={$communityID}&mp_user_id={$mpUserID}", ['target' => '_blank']);
                        $carouselWithWords = new Link("有字轮播", "/wx_user/carousel/album?album_id={$albumID}&community_id={$communityID}&name=1&mp_user_id={$mpUserID}", ['target' => '_blank']);
                        $pictureCarousel = new Link("点击直接进入轮播", "/wx_user/carousel/picture?album_id={$albumID}&community_id={$communityID}&name=1&mp_user_id={$mpUserID}", ['target' => '_blank']);
                        $carouselWithHorizontal = new Link("全屏横向轮播", "/wx_user/carousel/album?album_id={$albumID}&community_id={$communityID}&name=horizontal&mp_user_id={$mpUserID}", [ 'target' => '_blank' ]);
                        $carouselWithVertical = new Link("全屏纵向轮播", "/wx_user/carousel/album?album_id={$albumID}&community_id={$communityID}&name=vertical&mp_user_id={$mpUserID}", [ 'target' => '_blank' ]);
                        $carouselWithTest = new Link("带音乐图片轮播", "/wx_user/carousel/album?album_id={$albumID}&community_id={$communityID}&mp_user_id={$mpUserID}&name=music", [ 'target' => '_blank' ]);
                        $ret = $pic."<br>".$carousel."<br>".$carouselWithWords."<br>".$pictureCarousel."<br>".$carouselWithHorizontal."<br>".$carouselWithVertical."<br>".$carouselWithTest;
                        return $ret;
                    } ], ];
        if($checkReadPower)
        {
            $showColumns[Table::COLUMN_OPERATIONS] = [
                    Table::COLUMN_CELL_STYLE => 'width:15%',
                    Table::COLUMN_TITLE => "操作",
                    Table::COLUMN_FUNCTION => function (array $row)use($power)
                        {
                            $albumID = $row[Album::ALBUM_ID];
                            $communityID =  $row[Album::COMMUNITY_ID];
                            $mpUserID= $row[Album::MP_USER_ID];
                            $carouselID = $row[Album::CAROUSEL_ID];
                            $update =   new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/carousel_dialog/album_update?album_id={$albumID}&community_id={$communityID}');");
                            $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该相册下的图片都将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/carousel/album_delete?album_id={$albumID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");
                            $pic = new Link("图片", "/mp_admin/carousel/picture?carousel_id={$carouselID}&album_id={$albumID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                            $carousel = new Link("轮播", "/wx_user/carousel/album?album_id={$albumID}&community_id={$communityID}&mp_user_id={$mpUserID}", ['target' => '_blank']);
                            $carouselWithWords = new Link("有字轮播", "/wx_user/carousel/album?album_id={$albumID}&community_id={$communityID}&name=1&mp_user_id={$mpUserID}", ['target' => '_blank']);
                            $pictureCarousel = new Link("点击直接进入轮播", "/wx_user/carousel/picture?album_id={$albumID}&community_id={$communityID}&name=1&mp_user_id={$mpUserID}", ['target' => '_blank']);
                            $carouselWithHorizontal = new Link("全屏横向轮播", "/wx_user/carousel/album?album_id={$albumID}&community_id={$communityID}&name=horizontal&mp_user_id={$mpUserID}", [ 'target' => '_blank' ]);
                            $carouselWithVertical = new Link("全屏纵向轮播", "/wx_user/carousel/album?album_id={$albumID}&community_id={$communityID}&name=vertical&mp_user_id={$mpUserID}", [ 'target' => '_blank' ]);
                            $carouselWithTest = new Link("带音乐图片轮播", "/wx_user/carousel/album?album_id={$albumID}&community_id={$communityID}&mp_user_id={$mpUserID}&name=music", [ 'target' => '_blank' ]);
                            $ret = $update."<br>".$pic."<br>".$carousel."<br>".$carouselWithWords."<br>".$pictureCarousel."<br>".$carouselWithHorizontal."<br>".$carouselWithVertical."<br>".$carouselWithTest;
                            if($power["delete"])
                            {
                                $ret .= "<br>".$delete;
                            }
                            return $ret;
                        }
                     ];
        }

        $table               = Table::fromDbData( $data, $outputColumns, Album::ALBUM_ID, null,
            $showColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }

    public function pictureAction()
    {
        $mpUserId = $this->_mpUserID;
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $communityId = $this->_request->getQueryParam( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $carouselId = $this->_request->getQueryParam( Picture::CAROUSEL_ID );
        $albumId = $this->_request->getQueryParam( Picture::ALBUM_ID );
        $carousel = new Carousel([Carousel::CAROUSEL_ID => $carouselId]);
        $album = new Album([Album::ALBUM_ID => $albumId]);
        $this->_view->set('carousel', $carousel->data());
        $this->_view->set('album', $album->data());
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $data          = CarouselBusiness::getPictureList( $albumId );

        $outputColumns = Picture::s_metadata()->getFilterOptions();
        $power = $this->checkChangePower("img_carousel_rw","img_carousel_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('img_carousel_rw', $checkReadPower);
        $showColumns = [ Picture::IMG_URL => [Table::COLUMN_FUNCTION => function ( array $row )
            {
                return sprintf( '<img src="%s" width="100px" height="100px" alt="没有图片"/>',
                    $row[Picture::IMG_URL] );
            } ],
            Picture::SORT_NO,
            Picture::COMMENT,];
        if($checkReadPower)
        {
            $showColumns[Table::COLUMN_OPERATIONS] =
                [
                     Table::COLUMN_CELL_STYLE => 'width:10%',
                     Table::COLUMN_TITLE => "操作",
                     Table::COLUMN_FUNCTION => function (array $row)use($power)
                       {
                            $pictureID = $row[Picture::PICTURE_ID];
                            $communityID =  $row[Album::COMMUNITY_ID];
                            $update =    new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/carousel_dialog/picture_update?picture_id={$pictureID}&community_id={$communityID}');");
                            $delete =  new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/carousel/picture_delete?picture_id={$pictureID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");

                            $ret = $update;
                            if($power["delete"])
                            {
                               $ret .= "<br>".$delete;
                            }
                            return $ret;
                            }
             ];
        }

        $table               = Table::fromDbData( $data, $outputColumns, Picture::PICTURE_ID, null,
            $showColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }
}