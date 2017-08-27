<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\Directory;
use MP\Model\Mp\TopDirectory;

class DirectoryDialogController extends Controller
{
    public function addTopAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->getQueryParam( TopDirectory::COMMUNITY_ID );
        $fields   = [
            TopDirectory::TITLE, TopDirectory::TOP_DIR_NO,TopDirectory::URL_TYPE,
            TopDirectory::DIRECTORY_BACKGROUND_IMG => [Form::FIELD_LABEL => "目录背景图片",
               Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD,Form::FIELD_MESSAGE => "长宽比为：3:5"],
            TopDirectory::DIRECTORY_TOP_IMG => [Form::FIELD_LABEL => "目录顶部图片一",
               Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD,Form::FIELD_MESSAGE => "长宽比为：3:2"],
            TopDirectory::DIRECTORY_TOP_IMG_SECOND => [Form::FIELD_LABEL => "目录顶部图片二",
                   Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD,Form::FIELD_MESSAGE => "长宽比为：3:2"],
            TopDirectory::DIRECTORY_TOP_IMG_THIRD => [Form::FIELD_LABEL => "目录顶部图片三", Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD,Form::FIELD_MESSAGE => "长宽比为：3:2"],
            TopDirectory::POWER_TYPE
        ];

        $form = Form::fromModelMetadata( TopDirectory::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加目录';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/directory/insert_top?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,
        function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";


        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function copyTopAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->getQueryParam( TopDirectory::COMMUNITY_ID );
        $fields   = [
            TopDirectory::TOP_DIRECTORY_ID
        ];

        $form = Form::fromModelMetadata( TopDirectory::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '复制一级目录';
        $form->ajaxForm = true;

        $successMessage     = '复制成功';
        $form->submitAction = "wbtAPI.call('../fcrm/directory/copy_top?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,
        function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";


        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function editTopAction()
    {
        $id  = $this->_request->getQueryParam( TopDirectory::TOP_DIRECTORY_ID );
        $communityId = $this->_request->getQueryParam( TopDirectory::COMMUNITY_ID );
        $obj = new TopDirectory([ TopDirectory::TOP_DIRECTORY_ID => $id ]);

        $data   = $obj->data();
        $fields   = [ TopDirectory::TITLE, TopDirectory::TOP_DIR_NO,TopDirectory::URL_TYPE,
            TopDirectory::DIRECTORY_BACKGROUND_IMG => [Form::FIELD_LABEL => "目录背景图片",
                Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD,Form::FIELD_MESSAGE => "长宽比为：3:5"],
            TopDirectory::DIRECTORY_TOP_IMG => [Form::FIELD_LABEL => "目录顶部图片一",
                Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD,Form::FIELD_MESSAGE => "长宽比为：3:2"],
            TopDirectory::DIRECTORY_TOP_IMG_SECOND => [Form::FIELD_LABEL => "目录顶部图片二", Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD,Form::FIELD_MESSAGE => "长宽比为：3:2"],
            TopDirectory::DIRECTORY_TOP_IMG_THIRD => [Form::FIELD_LABEL => "目录顶部图片三", Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD,Form::FIELD_MESSAGE => "长宽比为：3:2"],
            TopDirectory::POWER_TYPE
        ];

        $form = Form::fromModelMetadata( TopDirectory::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改二级目录';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/directory/update_top?top_directory_id={$id}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function addAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $topDirectoryId = $this->_request->getQueryParam( Directory::TOP_DIRECTORY_ID );
        $communityId = $this->_request->getQueryParam( Directory::COMMUNITY_ID );
        $fields = [
            Directory::COMMON_TYPE => ['onChange' => 'directory_common_type_on_change()' ],
            Directory::ICON  => [ Form::FIELD_LABEL => '图标',
                                 Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD, ],
            Directory::TITLE,
            Directory::SORT_NO,
            Directory::POWER_TYPE,
            Directory::COMMON_CONTENT,
            Directory::HEAD_DESC,
            Directory::TAIL_DESC=> [ Form::FIELD_LABEL => '组尾说明',
                Form::FIELD_TAG   => Form::COM_RICH_TEXT, ],
            Directory::GROUP_END,
            Directory::SHOW_SMALL_FLOW =>
                ['onClick' => 'small_flow_show()',
                  Form::FIELD_LABEL => "是否开通小流量",
                  Form::FIELD_MESSAGE => "<span style=\"font-size:8px;\">点击开通小流量</span>"
                ],
            Directory::SMALL_FLOW_TYPE =>
                ['onChange' =>  'directory_small_flow_type_on_change()' ,
                 Form::FIELD_LABEL => "小流量类型",
                ],
            Directory::SMALL_FLOW_CONTENT => [Form::FIELD_LABEL => "小流量链接",],
            ];

        $form = Form::fromModelMetadata( Directory::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加目录';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/directory/insert?mp_user_id={$mpUserId}&top_directory_id={$topDirectoryId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $form->bodyScript = $this->getJavaScript();
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function editAction()
    {
        $directoryId = $this->_request->getQueryParam( Directory::DIRECTORY_ID );
        $directory   = new Directory([ Directory::DIRECTORY_ID => $directoryId ]);
        $communityId = $this->_request->getQueryParam( Directory::COMMUNITY_ID );
        $data   = $directory->data();
        $fields = [
            Directory::COMMON_TYPE =>  ['onChange' => 'directory_common_type_on_change()' ],
            Directory::ICON  => [
                                  Form::FIELD_LABEL => '图标',
                                  Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD, ],
            Directory::TITLE,
            Directory::SORT_NO,
            Directory::POWER_TYPE,
            Directory::COMMON_CONTENT,
            Directory::HEAD_DESC,
            Directory::TAIL_DESC=> [ Form::FIELD_LABEL => '组尾说明',
                                      Form::FIELD_TAG   => Form::COM_RICH_TEXT, ],
            Directory::GROUP_END,
            Directory::SHOW_SMALL_FLOW =>
                ['onClick' => 'small_flow_show("change")',
                 Form::FIELD_LABEL => "是否开通小流量",
                 Form::FIELD_MESSAGE => "<span style=\"font-size:8px;\">点击开通小流量</span>"
            ],
            Directory::SMALL_FLOW_TYPE =>
                ['onChange' =>  'directory_small_flow_type_on_change()' ,
                 Form::FIELD_LABEL => "小流量类型",
                ],
            Directory::SMALL_FLOW_CONTENT => [Form::FIELD_LABEL => "小流量链接",],
        ];

        $form = Form::fromModelMetadata( Directory::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改目录';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/directory/update?directory_id={$directoryId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $form->bodyScript = $this->getJavaScript();
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function smallFlowSetAction()
    {
        $directoryId = $this->_request->getQueryParam( Directory::DIRECTORY_ID );
        $directory   = new Directory([ Directory::DIRECTORY_ID => $directoryId ]);
        $communityId = $this->_request->getQueryParam( Directory::COMMUNITY_ID );
        $data   = $directory->data();
        $fields = [
           Directory::SMALL_FLOW_NO,
        ];

        $form = Form::fromModelMetadata( Directory::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改目录';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/directory/small_flow_set?directory_id={$directoryId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $form->bodyScript = $this->getJavaScript();
        echo $form;
        echo SimpleComponent::$scripts;
    }

    private function  getJavaScript()
    {
        $bodyScript = <<<JS

            var isInit = false;

            directory_common_type_on_change();//普通目录类型选择后展示内容变化
            directory_small_flow_type_on_change();//小流量目录类型选择后展示内容变化
            small_flow_show("null");//小流量展示内容

            function directory_common_type_on_change()
            {
                var commonType = $("[name='common_type']").val();
                var commonContent = $("[name='common_content']").parent().parent();
                var icon = $("#form1Icon_mainImg").parent().parent();
                var iconLabel = $("#form1Icon_myModal").parent().parent();
                var title = $("#form1Title").parent().parent();
                var showSmallFlow = $("[name='show_small_flow']").parent().parent();
                //展示所有选项
                icon.show();
                title.show();
                iconLabel.show();
                commonContent.show();
                showSmallFlow.show();
                //根据条件判断展示选项内容
                switch (commonType)
                  {
                     case 'text':
                     showSmallFlow.hide();
                     break;
                     case 'user_bill_list':
                     commonContent.hide();
                     break;
                     case 'user_setting':
                     commonContent.hide();
                     break;
                     case 'user_order':
                     commonContent.hide();
                     break;
                     case 'user_vip_card':
                     commonContent.hide();
                     break;
                     case 'user_cs_certify':
                     commonContent.hide();
                     break;
                  }

                if(isInit)
                {
                   var dialogBody  = commonContent.parent().parent().parent();

                   bluefinBH.updateDialogSize(dialogBody);
                }
                else
                {
                   isInit = true;
                }
            }

            function directory_small_flow_type_on_change()
            {
                var smallFlowType = $("[name='small_flow_type']").val();
                var smallFlowContent = $("[name='small_flow_content']").parent().parent();
                if(smallFlowType == 'link')
                {
                    smallFlowContent.show();
                }
                else
                {
                    smallFlowContent.hide();
                }
                switch (smallFlowType)
                  {
                     case 'link':
                     smallFlowContent.show();
                     break;
                     case 'user_bill_list':
                     smallFlowContent.hide();
                     break;
                     case 'user_setting':
                      smallFlowContent.hide();
                     break;
                     case 'user_order':
                      smallFlowContent.hide();
                     break;
                     case 'user_vip_card':
                      smallFlowContent.hide();
                     break;
                     case 'user_cs_certify':
                     smallFlowContent.hide();
                     break;
                     default:
                     smallFlowContent.show();
                  }

                if(isInit)
                {
                   var dialogBody  = smallFlowContent.parent().parent().parent();

                   bluefinBH.updateDialogSize(dialogBody);
                }
                else
                {
                   isInit = true;
                }
            }


          function small_flow_show(check)
          {
                var  showSmallFlow = $("[name='show_small_flow']").val();
                var smallFlowType = $("[name='small_flow_type']").parent().parent();
                var smallFlowContent = $("[name='small_flow_content']").parent().parent();

                if(check == "null")
                {
                    if(showSmallFlow == 0)
                    {
                       smallFlowType.hide();
                       smallFlowContent.hide();
                    }
                    else
                    {
                        smallFlowType.show();
                        smallFlowContent.show();
                    }
                }
                else if(check = "change")
                {
                  // 转化取得的showSmallFlow的值
                    if(showSmallFlow == 1)
                    {
                       showSmallFlow = 0;
                    }
                    else if(showSmallFlow == 0)
                    {
                       showSmallFlow = 1;
                    }

                   if(showSmallFlow == 0)
                   {
                       smallFlowType.hide();
                       smallFlowContent.hide();
                    }
                    else
                    {
                        smallFlowType.show();
                        smallFlowContent.show();
                    }
                 }
                 if(isInit)
                {
                   var dialogBody  = smallFlowContent.parent().parent().parent();

                   bluefinBH.updateDialogSize(dialogBody);
                }
                else
                {
                   isInit = true;
                }

          }
JS;
        return $bodyScript;
    }
}