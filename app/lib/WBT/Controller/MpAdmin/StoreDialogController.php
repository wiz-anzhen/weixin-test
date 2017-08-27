<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;

use MP\Model\Mp\Store;
use MP\Model\Mp\Category;
use MP\Model\Mp\Product;
use MP\Model\Mp\Community;

class StoreDialogController extends Controller
{
    public function storeAddAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $fields   = [ Store::TITLE, Store::COMMENT, ];

        $form = Form::fromModelMetadata( Store::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加商城';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/store_insert?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function storeCopyAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $fields   = [ Store::STORE_ID, ];

        $form = Form::fromModelMetadata( Store::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '复制商城';
        $form->ajaxForm = true;

        $successMessage     = '复制成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/store_copy?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('复制', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function storeUpdateAction()
    {
        $id  = $this->_request->getQueryParam( Store::STORE_ID );
        $obj = new Store([ Store::STORE_ID => $id ]);

        $data   = $obj->data();
        $fields = [ Store::TITLE, Store::COMMENT, ];

        $form = Form::fromModelMetadata( Store::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改商城';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/store_update?store_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function categoryAddAction()
    {
        $mpUserId   = $this->_request->getQueryParam( 'mp_user_id' );
        $storeId = $this->_request->getQueryParam( Category::STORE_ID );
        $communityId = $this->_request->get('community_id');
        $fields     = [ Category::TITLE, Category::COVER_IMG=> [ Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD,
        Form::FIELD_MESSAGE => "长宽比为：2:1"]
            , Category::DESCRIPTION, Category::SORT_NO, Category::COMMENT,Category::IS_ON_SHELF, ];

        $form = Form::fromModelMetadata( Category::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加分类';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/category_insert?store_id={$storeId}&mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function categoryCopyAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $storeId = $this->_request->getQueryParam( Category::STORE_ID );
        $fields   = [ Category::CATEGORY_ID ];

        $form = Form::fromModelMetadata( Category::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '复制分类';
        $form->ajaxForm = true;

        $successMessage     = '复制成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/category_copy?store_id={$storeId}&mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('复制', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function categoryUpdateAction()
    {
        $id  = $this->_request->getQueryParam( Category::CATEGORY_ID );
        $obj = new Category([ Category::CATEGORY_ID => $id ]);

        $data   = $obj->data();
        $fields = [ Category::TITLE, Category::COVER_IMG =>[ Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD ,Form::FIELD_MESSAGE => "长宽比为：2:1" ] , Category::DESCRIPTION, Category::SORT_NO, Category::COMMENT,Category::IS_ON_SHELF, ];

        $form = Form::fromModelMetadata( Category::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改分类';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/category_update?category_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function productAddAction()
    {
        $mpUserId   = $this->_request->getQueryParam( 'mp_user_id' );
        $storeId = $this->_request->getQueryParam( Product::STORE_ID );
        $categoryId    = $this->_request->getQueryParam( Product::CATEGORY_ID );
        $communityId = $this->_request->get('community_id');

        $fields     = [ Product::TITLE,
            Product::PRICE,
            Product::COST_PRICE,
            Product::REFERENCE_PRICE,
            Product::PROFIT,
            Product::COMMISSIONS ,
            Product::IS_ON_SHELF,
            Product::IMG_URL => [ Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD ],
            Product::BIG_IMG_URL,
            Product::DETAIL => [ Form::FIELD_TAG => Form::COM_RICH_TEXT, ],
            Product::SORT_NO,
            Product::DETAIL_URL,
            Product::COMMENT, ];

        $form = Form::fromModelMetadata( Product::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );
        $form->legend   = '添加产品';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/product_insert?store_id={$storeId}&category_id={$categoryId}&mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function productUpdateAction()
    {
        $id  = $this->_request->getQueryParam( Product::PRODUCT_ID );
        $obj = new Product([ Product::PRODUCT_ID => $id ]);
        $data   = $obj->data();
        $fields = [ Product::TITLE,
            Product::PRICE,
            Product::COST_PRICE,
            Product::REFERENCE_PRICE,
            Product::PROFIT,
            Product::COMMISSIONS ,
            Product::IS_ON_SHELF,
            Product::IMG_URL => [ Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD ],
            Product::BIG_IMG_URL,
            Product::DETAIL => [ Form::FIELD_TAG => Form::COM_RICH_TEXT, ],
            Product::SORT_NO,
            Product::DETAIL_URL,
            Product::COMMENT, ];

        $form = Form::fromModelMetadata( Product::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改产品';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/product_update?product_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function storeAddProcurementAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityType = $community->getCommunityType();
        if($communityType == "procurement_supply")
        {
            $communityTypeName = "餐厅";
        }
        else
        {
            $communityTypeName = "供应商";
        }

        $fields   = [ Store::TITLE => [ Form::FIELD_LABEL => $communityTypeName ], Store::COMMENT, ];

        $form = Form::fromModelMetadata( Store::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加'.$communityTypeName;
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/store_insert?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function storeCopyProcurementAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityType = $community->getCommunityType();
        if($communityType == "procurement_supply")
        {
            $communityTypeName = "餐厅";
        }
        else
        {
            $communityTypeName = "供应商";
        }
        $fields   = [ Store::STORE_ID ];

        $form = Form::fromModelMetadata( Store::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '复制'.$communityTypeName;
        $form->ajaxForm = true;

        $successMessage     = '复制成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/store_copy?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('复制', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function storeUpdateProcurementAction()
    {
        $id  = $this->_request->getQueryParam( Store::STORE_ID );
        $obj = new Store([ Store::STORE_ID => $id ]);
        $community = new Community([Community::COMMUNITY_ID => $obj->getCommunityID()]);
        $communityType = $community->getCommunityType();
        if($communityType == "procurement_supply")
        {
            $communityTypeName = "餐厅";
        }
        else
        {
            $communityTypeName = "供应商";
        }
        $data   = $obj->data();
        $fields = [ Store::TITLE => [ Form::FIELD_LABEL => $communityTypeName ], Store::COMMENT, ];

        $form = Form::fromModelMetadata( Store::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改'.$communityTypeName;
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/store_update?store_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function categoryAddProcurementAction()
    {
        $mpUserId   = $this->_request->getQueryParam( 'mp_user_id' );
        $storeId = $this->_request->getQueryParam( Category::STORE_ID );
        $communityId = $this->_request->get('community_id');
        $fields     = [ Category::TITLE => [Form::FIELD_LABEL => "报价单名称"],
            Category::DESCRIPTION=> [Form::FIELD_LABEL => "报价单描述"],
            Category::SHELF_TIME =>
                [
                    Form::FIELD_LABEL =>'上架时间',
                    Form::FIELD_TAG => Form::COM_YMD,
                ],
            Category::SORT_NO, Category::COMMENT, ];

        $form = Form::fromModelMetadata( Category::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加报价单';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/category_insert?store_id={$storeId}&mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function categoryCopyProcurementAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $storeId = $this->_request->getQueryParam( Category::STORE_ID );
        $fields   = [ Category::CATEGORY_ID ];

        $form = Form::fromModelMetadata( Category::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '复制报价单';
        $form->ajaxForm = true;

        $successMessage     = '复制成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/category_copy?store_id={$storeId}&mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('复制', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }


    public function categoryUpdateProcurementAction()
    {
        $id  = $this->_request->getQueryParam( Category::CATEGORY_ID );
        $obj = new Category([ Category::CATEGORY_ID => $id ]);

        $data   = $obj->data();
        $fields     = [
            Category::TITLE => [Form::FIELD_LABEL => "报价单名称"],
            Category::DESCRIPTION=> [Form::FIELD_LABEL => "报价单描述"],
            Category::SHELF_TIME =>
            [
                Form::FIELD_LABEL =>'上架时间',
                Form::FIELD_TAG => Form::COM_YMD,
            ],
            Category::SORT_NO,
            Category::COMMENT, ];

        $form = Form::fromModelMetadata( Category::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改报价单';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/category_update?category_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function productAddProcurementAction()
    {
        $mpUserId   = $this->_request->getQueryParam( 'mp_user_id' );
        $storeId = $this->_request->getQueryParam( Product::STORE_ID );
        $categoryId    = $this->_request->getQueryParam( Product::CATEGORY_ID );
        $communityId = $this->_request->get('community_id');

        $fields     = [ Product::TITLE,
            Product::PRICE => [Form::FIELD_LABEL => "价格"],
            Product::PRODUCT_UNIT => [Form::FIELD_LABEL => "单位"],
            Product::SORT_NO,
            Product::COMMENT, ];

        $form = Form::fromModelMetadata( Product::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );
        $form->legend   = '添加产品';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/product_insert?store_id={$storeId}&category_id={$categoryId}&mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function productUpdateProcurementAction()
    {
        $id  = $this->_request->getQueryParam( Product::PRODUCT_ID );
        $obj = new Product([ Product::PRODUCT_ID => $id ]);
        $data   = $obj->data();
        $fields = [ Product::TITLE,
            Product::PRICE => [Form::FIELD_LABEL => "价格"],
            Product::PRODUCT_UNIT => [Form::FIELD_LABEL => "单位"],
            Product::SORT_NO,
            Product::COMMENT, ];

        $form = Form::fromModelMetadata( Product::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改产品';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/store/product_update?product_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
}