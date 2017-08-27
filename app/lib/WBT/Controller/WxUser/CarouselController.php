<?php

namespace WBT\Controller\WxUser;

use Common\Helper\BaseController;
use MP\Model\Mp\Album;
use MP\Model\Mp\Carousel;
use MP\Model\Mp\Picture;
use WBT\Business\Weixin\CarouselBusiness;
use WBT\Business\Weixin\WxApiBusiness;


class CarouselController extends BaseController
{
    public function carouselAction()
    {
        $carouselId = $this->_request->get(Carousel::CAROUSEL_ID);
        $carousel = new Carousel([Carousel::CAROUSEL_ID => $carouselId]);
        $this->_view->set('carousel', $carousel->data());

        $albums = CarouselBusiness::getAlbumList($carouselId);
        $this->_view->set('albums', $albums);

        // 模板
        $name = $this->_request->get('name');
        if ($name)
        {
            $this->changeView('WBT/WxUser/Carousel.carouselWithLabel.html');
        }
    }

    public function pictureAction()
    {
        $albumId   = $this->_request->get( Picture::ALBUM_ID );
        $album = new Album([Album::ALBUM_ID => $albumId]);
        $this->_view->set('album', $album->data());

        $fields    = [ Picture::IMG_URL, Picture::COMMENT ];
        $condition = [ Picture::ALBUM_ID => $albumId ];
        $ranking   = [ Picture::SORT_NO ];
        $pictures  = Picture::fetchRows( $fields, $condition, null, $ranking );

        $show = [];
        if (count($pictures) > 0)
        {
            foreach($pictures as $picture)
            {
                $show[] = [ 'url' => $picture[Picture::IMG_URL], 'caption' => $picture[Picture::COMMENT], ];
            }
        }
        else
        {
            $show[] = ['url' => '#', 'caption' => 'Nothing to show'];
        }
        $this->_view->set('showPics', json_encode($show));
    }

    public function albumAction()
    {
        $albumId = $this->_request->get(Album::ALBUM_ID);
        $mpUserId = $this->_request->get(Album::MP_USER_ID);
        $album = new Album([Album::ALBUM_ID => $albumId]);
        $this->_view->set('album', $album->data());
        $pictures = CarouselBusiness::getPictureList($albumId);
        $signPackage = CarouselBusiness::getSignPackage($mpUserId);

        /*foreach($pictures as $key => $value)
        {
            if(empty($pictures[$key]['comment']))
            {
                $pictures[$key]['comment'] ='no';
            }

        }*/
        $this->_view->set('pictures', $pictures);
        $this->_view->set('pictures_count', count($pictures));
        $this->_view->set('signPackage', $signPackage);
        log_debug('========================',$signPackage);
        // 模板
        $name = $this->_request->get('name');

        if ($name == "horizontal")
        {
            $this->changeView('WBT/WxUser/Carousel.albumHorizontal.html');
        }
        elseif($name == "vertical")
        {
            $this->changeView('WBT/WxUser/Carousel.albumVertical.html');
        }
        elseif($name == 1)
        {
            $this->changeView('WBT/WxUser/Carousel.albumWithLabel.html');
        }
        elseif($name == "music")
        {
            $this->changeView('WBT/WxUser/Carousel.albumWithMusic.html');
        }

    }
}