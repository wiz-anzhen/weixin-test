<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;

use MP\Model\Mp\Community;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\IndustryType;


class CommunityDialogController extends Controller
{
    public function communityAddAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $industry = $mpUser->getIndustry();

        if($industry == IndustryType::PROCUREMENT)
        {
            $fields   = [Community::NAME, Community::PHONE,
                Community::ADMIN_EMAIL    => [Form::COM_RICH_TEXT => '主送管理员email',
                    Form::FIELD_HINT    => _DICT_("adminEmail")],
                Community::ADMIN_CC_EMAIL => [Form::COM_RICH_TEXT => '抄送管理员email',
                    Form::FIELD_HINT    => _DICT_("adminEmail")],
                Community::IS_VIRTUAL,
                Community::IS_APP,
                Community::BILL_NAME,
                Community::BILL_COMMENT=> [Form::FIELD_LABEL => '收费通知单温馨提示',
                    Form::FIELD_TAG   => Form::COM_RICH_TEXT,],
                Community::PROVINCE ,//=>[Form::FIELD_LABEL=>'所在省份',
                    //Form::FIELD_TAG => Form::COM_PCA],
                Community::CITY,
                Community::AREA,
                Community::ADDRESS,

                Community::COMMENT,
                Community::COMMUNITY_TYPE,

            ];
        }
       else
       {
           $fields   = [Community::NAME, Community::PHONE,
               Community::ADMIN_EMAIL    => [Form::COM_RICH_TEXT => '主送管理员email',
                   Form::FIELD_HINT    => _DICT_("adminEmail")],
               Community::ADMIN_CC_EMAIL => [Form::COM_RICH_TEXT => '抄送管理员email',
                   Form::FIELD_HINT    => _DICT_("adminEmail")],
               Community::IS_VIRTUAL,
               Community::IS_APP,
               Community::BILL_NAME,
               Community::BILL_COMMENT=> [Form::FIELD_LABEL => '收费通知单温馨提示',
                   Form::FIELD_TAG   => Form::COM_RICH_TEXT,],
               Community::PROVINCE ,//=>[Form::FIELD_LABEL=>'所在省/市/区县',Form::FIELD_TAG => Form::COM_PCA],
               Community::CITY,
               Community::AREA,
               Community::ADDRESS,

               Community::COMMENT,

           ];
       }

        $form = Form::fromModelMetadata( Community::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加社区';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/community/community_insert?mp_user_id={$mpUserId}',PARAMS,
        function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function communityUpdateAction()
    {
        $id  = $this->_request->getQueryParam( Community::COMMUNITY_ID );
        $obj = new Community([ Community::COMMUNITY_ID => $id ]);

        $data   = $obj->data();
        $mpUserID = $obj->getMpUserID();
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $industry = $mpUser->getIndustry();

        if($industry == IndustryType::PROCUREMENT)
        {
            $fields = [Community::NAME, Community::PHONE,
                Community::ADMIN_EMAIL    => [Form::COM_RICH_TEXT => '主送管理员email',
                    Form::FIELD_HINT => _DICT_("adminEmail")],
                Community::ADMIN_CC_EMAIL => [Form::COM_RICH_TEXT => '抄送管理员email',
                    Form::FIELD_HINT    => _DICT_("adminEmail")],
                Community::IS_VIRTUAL,
                Community::IS_APP,
                Community::VALID,
                Community::BILL_NAME,
                Community::BILL_COMMENT=> [Form::FIELD_LABEL => '收费通知单温馨提示',
                    Form::FIELD_TAG   => Form::COM_RICH_TEXT,],
                Community::PROVINCE =>[Form::FIELD_LABEL=>'所在省份'],
                Community::CITY,
                Community::AREA,
                Community::ADDRESS,

                Community::COMMENT,
                Community::COMMUNITY_TYPE,

            ];
        }
        else
        {
            $fields = [Community::NAME, Community::PHONE,
                Community::ADMIN_EMAIL    => [Form::COM_RICH_TEXT => '主送管理员email',
                    Form::FIELD_HINT => _DICT_("adminEmail")],
                Community::ADMIN_CC_EMAIL => [Form::COM_RICH_TEXT => '抄送管理员email',
                    Form::FIELD_HINT    => _DICT_("adminEmail")],
                Community::IS_VIRTUAL,
                Community::IS_APP,
                Community::VALID,
                Community::BILL_NAME,
                Community::BILL_COMMENT=> [Form::FIELD_LABEL => '收费通知单温馨提示',
                    Form::FIELD_TAG   => Form::COM_RICH_TEXT,],
                Community::PROVINCE =>[Form::FIELD_LABEL=>'所在省份'],
                Community::CITY,
                Community::AREA,
                Community::ADDRESS,

                Community::COMMENT,
            ];
        }


        $form = Form::fromModelMetadata(Community::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改社区';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/community/community_update?community_id={$id}&mp_user_id={$mpUserID}',PARAMS,
        function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
}