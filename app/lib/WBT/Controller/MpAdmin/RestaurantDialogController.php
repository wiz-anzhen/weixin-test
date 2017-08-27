<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;

use MP\Model\Mp\Store;
use MP\Model\Mp\Restaurant;
use MP\Model\Mp\Community;

class RestaurantDialogController extends Controller
{
    public function restaurantAddAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $fields   = [ Restaurant::TITLE, Restaurant::COMMENT, ];

        $form = Form::fromModelMetadata( Restaurant::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加餐厅';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/restaurant/restaurant_insert?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }



    public function restaurantUpdateAction()
    {
        $id  = $this->_request->getQueryParam( Restaurant::RESTAURANT_ID );
        $obj = new Restaurant([ Restaurant::RESTAURANT_ID => $id ]);

        $data   = $obj->data();
        $fields = [ Restaurant::TITLE, Restaurant::COMMENT, ];

        $form = Form::fromModelMetadata( Restaurant::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改餐厅';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/restaurant/restaurant_update?restaurant_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

}