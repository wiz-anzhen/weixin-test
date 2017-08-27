<?php

namespace WBT\Controller\App;

use Common\Helper\BaseController;
use MP\Model\Mp\Album;
use MP\Model\Mp\Carousel;
use MP\Model\Mp\Picture;
use WBT\Business\Weixin\CarouselBusiness;


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
        $album = new Album([Album::ALBUM_ID => $albumId]);
        $this->_view->set('album', $album->data());

        $pictures = CarouselBusiness::getPictureList($albumId);
        $this->_view->set('pictures', $pictures);

        // 模板
        $name = $this->_request->get('name');
        if ($name)
        {
            $this->changeView('WBT/WxUser/Carousel.albumWithLabel.html');
        }
    }
}