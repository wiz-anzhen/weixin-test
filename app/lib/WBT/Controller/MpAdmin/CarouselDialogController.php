<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\Album;
use MP\Model\Mp\Carousel;
use MP\Model\Mp\Picture;

class CarouselDialogController extends Controller
{
    public function carouselAddAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $fields   = [ Carousel::TITLE, Carousel::COMMENT, ];

        $form = Form::fromModelMetadata( Carousel::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加轮播';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/carousel/carousel_insert?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function carouselUpdateAction()
    {
        $id  = $this->_request->getQueryParam( Carousel::CAROUSEL_ID );
        $communityId = $this->_request->get('community_id');
        $obj = new Carousel([ Carousel::CAROUSEL_ID => $id ,Carousel::COMMUNITY_ID => $communityId]);

        $data   = $obj->data();
        $fields = [ Carousel::TITLE, Carousel::COMMENT, ];

        $form = Form::fromModelMetadata( Carousel::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改轮播';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/carousel/carousel_update?carousel_id={$id}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function albumAddAction()
    {
        $mpUserId   = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $carouselId = $this->_request->getQueryParam( Album::CAROUSEL_ID );
        $fields     = [ Album::TITLE,
                        Album::COVER_IMG => [ Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD ],
                        Album::SORT_NO,
                        Album::COMMENT, ];

        $form = Form::fromModelMetadata( Album::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加相册';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/carousel/album_insert?carousel_id={$carouselId}&mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function albumUpdateAction()
    {
        $id  = $this->_request->getQueryParam( Album::ALBUM_ID );
        $communityId = $this->_request->get('community_id');
        $obj = new Album([ Album::ALBUM_ID => $id,Album::COMMUNITY_ID => $communityId ]);

        $data   = $obj->data();
        $fields = [ Album::TITLE, Album::COVER_IMG => [ Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD ], Album::SORT_NO, Album::COMMENT, ];

        $form = Form::fromModelMetadata( Album::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改相册';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/carousel/album_update?album_id={$id}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function pictureAddAction()
    {
        $mpUserId   = $this->_request->getQueryParam( 'mp_user_id' );
        $carouselId = $this->_request->getQueryParam( Picture::CAROUSEL_ID );
        $communityId = $this->_request->get('community_id');
        $albumId    = $this->_request->getQueryParam( Picture::ALBUM_ID );
        $fields     = [ Picture::IMG_URL => [ Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD ],
                        Picture::SORT_NO,
                        Picture::COMMENT, ];

        $form = Form::fromModelMetadata( Picture::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加图片';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/carousel/picture_insert?carousel_id={$carouselId}&album_id={$albumId}&mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function pictureUpdateAction()
    {
        $id  = $this->_request->getQueryParam( Picture::PICTURE_ID );
        $communityId = $this->_request->get('community_id');
        $obj = new Picture([ Picture::PICTURE_ID => $id,Picture::COMMUNITY_ID => $communityId ]);

        $data   = $obj->data();
        $fields = [ Picture::IMG_URL => [ Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD ], Picture::SORT_NO, Picture::COMMENT, ];

        $form = Form::fromModelMetadata( Picture::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改图片';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/carousel/picture_update?picture_id={$id}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
}