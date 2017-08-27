<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\CustomerSpecialistGroup;
use MP\Model\Mp\Community;
use MP\Model\Mp\CommunityType;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\ProcurementPowerType;
use MP\Model\Mp\TopDirectory;
use Bluefin\HTML\CheckGroup;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\IndustryType;
use MP\Model\Mp\Part;
class HouseDialogController extends Controller{
    //添加
    public function addAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->getQueryParam( TopDirectory::COMMUNITY_ID );

        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $industry = $mpUser->getIndustry();
        $memberType = HouseMemberType::getDictionary();
        if($industry != IndustryType::PROCUREMENT)
        {
            $memberType = array_slice($memberType,0,5);
        }
        else
        {
            $memberType = array_slice($memberType,6);
        }
        $fields   = [
            HouseMember::HOUSE_NO => [Form::FIELD_LABEL => "用户/房间号"],
            HouseMember::HOUSE_ADDRESS,

            HouseMember::HOUSE_AREA  =>
            [
                Form::FIELD_LABEL =>'面积',
                Form::FIELD_VALUE => 0,
            ],

            HouseMember::NAME ,
            HouseMember::MEMBER_TYPE => [ Form::FIELD_LABEL => "用户类型" ,
                Form::FIELD_DATA => $memberType, ],

            HouseMember::BIRTHDAY =>
            [
                Form::FIELD_LABEL =>'生日',
                Form::FIELD_HINT => _DICT_('birthday'),
            ],

            HouseMember::PHONE1  => [Form::FIELD_REQUIRED => true],
            HouseMember::PHONE2,
            HouseMember::PHONE3,
            HouseMember::COMMENT,
        ];

        $form = Form::fromModelMetadata( HouseMember::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '录入用户信息';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/house/insert?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,
        function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";


        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    //修改
    public function editAction()
    {
        $id  = $this->_request->getQueryParam( HouseMember::HOUSE_MEMBER_ID );
        $communityId = $this->_request->getQueryParam( HouseMember::COMMUNITY_ID );
        $obj = new HouseMember([ HouseMember::HOUSE_MEMBER_ID => $id ]);

        $data   = $obj->data();

        $mpUser = new MpUser([MpUser::MP_USER_ID => $obj->getMpUserID()]);
        $industry = $mpUser->getIndustry();
        $memberType = HouseMemberType::getDictionary();
        if($industry != IndustryType::PROCUREMENT)
        {
            $memberType = array_slice($memberType,0,5);
        }
        else
        {
            $memberType = array_slice($memberType,6);
        }
        $fields   = [
            HouseMember::HOUSE_NO=> [Form::FIELD_LABEL => "用户/房间号"],
            HouseMember::HOUSE_ADDRESS,
            HouseMember::HOUSE_AREA,
            HouseMember::NAME,
            HouseMember::MEMBER_TYPE => [ Form::FIELD_LABEL => "用户类型" ,
                Form::FIELD_DATA => $memberType, ],
            HouseMember::BIRTHDAY =>[Form::FIELD_LABEL =>'生日',
                                     Form::FIELD_HINT => _DICT_('birthday'),],
            HouseMember::PHONE1  => [Form::FIELD_REQUIRED => true],
            HouseMember::PHONE2,
            HouseMember::PHONE3,
            HouseMember::COMMENT,
        ];

        $form = Form::fromModelMetadata( HouseMember::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改已录入用户信息';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/house/update?house_member_id={$id}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons(
            [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
              new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );

        echo $form;
        echo SimpleComponent::$scripts;
    }
    //认证
    public function checkAction()
    {
        $id  = $this->_request->getQueryParam( HouseMember::HOUSE_MEMBER_ID );
        $communityId = $this->_request->getQueryParam( HouseMember::COMMUNITY_ID );
        $obj = new HouseMember([ HouseMember::HOUSE_MEMBER_ID => $id ]);

        $data   = $obj->data();

        $mpUser = new MpUser([MpUser::MP_USER_ID => $obj->getMpUserID()]);
        $industry = $mpUser->getIndustry();
        $memberType = HouseMemberType::getDictionary();
        if($industry != IndustryType::PROCUREMENT)
        {
            $memberType = array_slice($memberType,0,5);
        }
        else
        {
            $memberType = array_slice($memberType,6);
        }

        $csGroup= [];
        $csGroup['0']= "请选择";
        $csGroupList = CustomerSpecialistGroup::fetchRows([ '*' ], $condition = [CustomerSpecialistGroup::COMMUNITY_ID => $communityId]);
        foreach($csGroupList as $value)
        {
            $csGroup[$value[CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID]]= $value[CustomerSpecialistGroup::GROUP_NAME];
        }

        $cs['0']= "请选择";
        $fields   =
            [
                HouseMember::HOUSE_ADDRESS ,
                HouseMember::NAME,
                HouseMember::MEMBER_TYPE => [ Form::FIELD_LABEL => "用户类型" ,
                    Form::FIELD_DATA => $memberType, ],
                HouseMember::PHONE1 => [Form::FIELD_REQUIRED => true] ,
                'vip_no' =>
                [
                    Form::FIELD_LABEL => "会员号",
                    Form::FIELD_TAG => Form::COM_INPUT,
                    Form::FIELD_CLASS => "input-xlarge",
                    Form::FIELD_REQUIRED => true,
                ],

                'cs_group' =>
                [
                    Form::FIELD_LABEL => "客服组",
                    Form::FIELD_TAG => Form::COM_COMBO_BOX,
                    'onChange = "selectCheck();"',
                    Form::FIELD_DATA => $csGroup ,
                ],

                'cs' =>
                [
                    Form::FIELD_LABEL => "客服专员",
                    Form::FIELD_TAG => Form::COM_COMBO_BOX,
                    Form::FIELD_DATA => $cs ,
                ]
            ];

        $form = Form::fromModelMetadata( HouseMember::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '业主认证';
        $form->ajaxForm = true;

        $successMessage     = '认证成功';
        $form->submitAction = "wbtAPI.call('../fcrm/house/check?house_member_id={$id}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons(
            [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );

        echo $form;
        echo SimpleComponent::$scripts;
    }
    //录入并认证
    public function addCheckAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->getQueryParam( TopDirectory::COMMUNITY_ID );
        $csGroup= [];
        $csGroup['0']= "请选择";
        $csGroupList = CustomerSpecialistGroup::fetchRows([ '*' ], $condition = [CustomerSpecialistGroup::COMMUNITY_ID => $communityId]);
        foreach($csGroupList as $value)
        {
            $csGroup[$value[CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID]]= $value[CustomerSpecialistGroup::GROUP_NAME];
        }

        $cs['0']= "请选择";

        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $industry = $mpUser->getIndustry();
        $memberType = HouseMemberType::getDictionary();
        if($industry != IndustryType::PROCUREMENT)
        {
            $memberType = array_slice($memberType,0,5);
        }
        else
        {
            $memberType = array_slice($memberType,6);
        }
        $fields   = [
            HouseMember::HOUSE_NO => [Form::FIELD_LABEL => "用户/房间号"],
            HouseMember::HOUSE_ADDRESS,
            HouseMember::HOUSE_AREA  =>[
                Form::FIELD_LABEL =>'面积',
                Form::FIELD_VALUE => 0,
            ],

            HouseMember::NAME,
            HouseMember::MEMBER_TYPE => [ Form::FIELD_LABEL => "用户类型" ,
                Form::FIELD_DATA => $memberType, ],
            HouseMember::BIRTHDAY =>[Form::FIELD_LABEL =>'生日',
                                     Form::FIELD_HINT => _DICT_('birthday'),],
            HouseMember::PHONE1  => [Form::FIELD_REQUIRED => true],
            HouseMember::PHONE2,
            HouseMember::PHONE3,
            HouseMember::COMMENT,
            'vip_no' =>
                [
                    Form::FIELD_LABEL => "会员号",
                    Form::FIELD_TAG => Form::COM_INPUT,Form::FIELD_CLASS => "input-xlarge",
                    Form::FIELD_REQUIRED => true,
                ],
            'cs_group' => [Form::FIELD_LABEL => "客服组",
                           Form::FIELD_TAG => Form::COM_COMBO_BOX,
                          'onChange = "selectCheck();"',
                           Form::FIELD_DATA => $csGroup ,
            ],
            'cs' => [Form::FIELD_LABEL => "客服专员",
                     Form::FIELD_TAG => Form::COM_COMBO_BOX,
                     Form::FIELD_DATA => $cs ,]
        ];

        $form = Form::fromModelMetadata( HouseMember::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '录入用户并认证';
        $form->ajaxForm = true;

        $successMessage     = '添加并认证成功';
        $form->submitAction = "wbtAPI.call('../fcrm/house/insert_check?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,
        function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";


        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }


    //添加
    public function addProcurementAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->getQueryParam( TopDirectory::COMMUNITY_ID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $industry = $mpUser->getIndustry();
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityType = $community->getCommunityType();
        $memberType = HouseMemberType::getDictionary();
        if($industry != IndustryType::PROCUREMENT)
        {
            $memberType = array_slice($memberType,0,5);
        }
        else
        {
            if($communityType == CommunityType::PROCUREMENT_SUPPLY)
            {
                $memberType = array_slice($memberType,6,2);
            }
            else
            {
                $memberType = array_slice($memberType,6);
            }

        }
        $power =  ProcurementPowerType::getDictionary();
        $part =  Part::fetchRows(['*'],[Part::COMMUNITY_ID => $communityId]);
        $partData = [];
        foreach($part as $key => $value)
        {
            $partData[$value[Part::TITLE]."_".$value[Part::PART_ID]] = $value[Part::TITLE];
        }
        if($communityType != CommunityType::PROCUREMENT_RESTAURANT)
        {
            $fields   = [
                HouseMember::NAME ,
                HouseMember::MEMBER_TYPE => [ Form::FIELD_LABEL => "用户类型" ,
                    Form::FIELD_DATA => $memberType, ],
                HouseMember::PROCUREMENT_POWER_TYPE =>
                    [
                        Form::FIELD_LABEL => "员工权限",
                        Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                        CheckGroup::COUNT_PER_LINE => 1,
                        Form::FIELD_DATA =>  $power,
                    ],
                HouseMember::PHONE1  => [Form::FIELD_REQUIRED => true],
                HouseMember::PHONE2,
                HouseMember::PHONE3,
                HouseMember::COMMENT,
            ];

        }
        else
        {
            $fields   = [
                HouseMember::NAME ,
                HouseMember::MEMBER_TYPE => [ Form::FIELD_LABEL => "用户类型" ,
                    Form::FIELD_DATA => $memberType, ],
                HouseMember::PROCUREMENT_POWER_TYPE =>
                    [
                        Form::FIELD_LABEL => "员工权限",
                        Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                        CheckGroup::COUNT_PER_LINE => 1,
                        Form::FIELD_DATA =>  $power,
                    ],
                HouseMember::PART_ID =>
                    [
                        Form::FIELD_LABEL => "所在档口",
                        Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                        CheckGroup::COUNT_PER_LINE => 1,
                        Form::FIELD_DATA =>  $partData,
                    ],

                HouseMember::PHONE1  => [Form::FIELD_REQUIRED => true],
                HouseMember::PHONE2,
                HouseMember::PHONE3,
                HouseMember::COMMENT,
            ];

        }

        $form = Form::fromModelMetadata( HouseMember::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '录入用户信息';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/house/insert_procurement?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,
        function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";


        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    //修改
    public function editProcurementAction()
    {
        $id  = $this->_request->getQueryParam( HouseMember::HOUSE_MEMBER_ID );
        $communityId = $this->_request->getQueryParam( HouseMember::COMMUNITY_ID );
        $obj = new HouseMember([ HouseMember::HOUSE_MEMBER_ID => $id ]);
        $power =  ProcurementPowerType::getDictionary();
        $data   = $obj->data();

        $mpUser = new MpUser([MpUser::MP_USER_ID => $obj->getMpUserID()]);
        $industry = $mpUser->getIndustry();

        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityType = $community->getCommunityType();
        $memberType = HouseMemberType::getDictionary();
        if($industry != IndustryType::PROCUREMENT)
        {
            $memberType = array_slice($memberType,0,5);
        }
        else
        {
            if($communityType == CommunityType::PROCUREMENT_SUPPLY)
            {
                $memberType = array_slice($memberType,6,2);
            }
            else
            {
                $memberType = array_slice($memberType,6);
            }

        }
        $part =  Part::fetchRows(['*'],[Part::COMMUNITY_ID => $communityId]);
        $partData = [];
        foreach($part as $key => $value)
        {
            $partData[$value[Part::TITLE]."_".$value[Part::PART_ID]] = $value[Part::TITLE];
        }
        if($communityType != CommunityType::PROCUREMENT_RESTAURANT)
        {
            $fields   = [
                HouseMember::NAME ,
                HouseMember::MEMBER_TYPE => [ Form::FIELD_LABEL => "用户类型" ,
                    Form::FIELD_DATA => $memberType, ],
                HouseMember::PROCUREMENT_POWER_TYPE =>
                    [
                        Form::FIELD_LABEL => "员工权限",
                        Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                        CheckGroup::COUNT_PER_LINE => 1,
                        Form::FIELD_DATA =>  $power,
                    ],
                HouseMember::PHONE1  => [Form::FIELD_REQUIRED => true],
                HouseMember::PHONE2,
                HouseMember::PHONE3,
                HouseMember::COMMENT,
            ];
        }
        else
        {
            $fields   = [
                HouseMember::NAME ,
                HouseMember::MEMBER_TYPE => [ Form::FIELD_LABEL => "用户类型" ,
                    Form::FIELD_DATA => $memberType, ],
                HouseMember::PROCUREMENT_POWER_TYPE =>
                    [
                        Form::FIELD_LABEL => "员工权限",
                        Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                        CheckGroup::COUNT_PER_LINE => 1,
                        Form::FIELD_DATA =>  $power,
                    ],
                HouseMember::PART_ID =>
                    [
                        Form::FIELD_LABEL => "所在档口",
                        Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                        CheckGroup::COUNT_PER_LINE => 1,
                        Form::FIELD_DATA =>  $partData,
                    ],
                HouseMember::PHONE1  => [Form::FIELD_REQUIRED => true],
                HouseMember::PHONE2,
                HouseMember::PHONE3,
                HouseMember::COMMENT,
            ];
        }


        $form = Form::fromModelMetadata( HouseMember::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改已录入用户信息';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/house/update_procurement?house_member_id={$id}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons(
            [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );

        echo $form;
        echo SimpleComponent::$scripts;
    }
    //认证
    public function checkProcurementAction()
    {
        $id  = $this->_request->getQueryParam( HouseMember::HOUSE_MEMBER_ID );
        $communityId = $this->_request->getQueryParam( HouseMember::COMMUNITY_ID );
        $obj = new HouseMember([ HouseMember::HOUSE_MEMBER_ID => $id ]);
        $power =  ProcurementPowerType::getDictionary();
        $data   = $obj->data();

        $mpUser = new MpUser([MpUser::MP_USER_ID => $obj->getMpUserID()]);
        $industry = $mpUser->getIndustry();
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityType = $community->getCommunityType();
        $memberType = HouseMemberType::getDictionary();
        if($industry != IndustryType::PROCUREMENT)
        {
            $memberType = array_slice($memberType,0,5);
        }
        else
        {
            if($communityType == CommunityType::PROCUREMENT_SUPPLY)
            {
                $memberType = array_slice($memberType,6,2);
            }
            else
            {
                $memberType = array_slice($memberType,6);
            }

        }
        $csGroup= [];
        $csGroup['0']= "请选择";
        $csGroupList = CustomerSpecialistGroup::fetchRows([ '*' ], $condition = [CustomerSpecialistGroup::COMMUNITY_ID => $communityId]);
        foreach($csGroupList as $value)
        {
            $csGroup[$value[CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID]]= $value[CustomerSpecialistGroup::GROUP_NAME];
        }

        $cs['0']= "请选择";
        $part =  Part::fetchRows(['*'],[Part::COMMUNITY_ID => $communityId]);
        $partData = [];
        foreach($part as $key => $value)
        {
            $partData[$value[Part::TITLE]."_".$value[Part::PART_ID]] = $value[Part::TITLE];
        }
        if($communityType != CommunityType::PROCUREMENT_RESTAURANT)
        {
            $fields   =
                [
                    HouseMember::NAME ,
                    HouseMember::MEMBER_TYPE => [ Form::FIELD_LABEL => "用户类型" ,
                        Form::FIELD_DATA => $memberType, ],
                    HouseMember::PROCUREMENT_POWER_TYPE =>
                        [
                            Form::FIELD_LABEL => "员工权限",
                            Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                            CheckGroup::COUNT_PER_LINE => 1,
                            Form::FIELD_DATA =>  $power,
                        ],
                    HouseMember::PHONE1 => [Form::FIELD_REQUIRED => true] ,
                    'vip_no' =>
                        [
                            Form::FIELD_LABEL => "会员号",
                            Form::FIELD_TAG => Form::COM_INPUT,
                            Form::FIELD_CLASS => "input-xlarge",
                            Form::FIELD_REQUIRED => true,
                        ],
                ];

        }
        else
        {
            $fields   =
                [
                    HouseMember::NAME ,
                    HouseMember::MEMBER_TYPE => [ Form::FIELD_LABEL => "用户类型" ,
                        Form::FIELD_DATA => $memberType, ],
                    HouseMember::PROCUREMENT_POWER_TYPE =>
                        [
                            Form::FIELD_LABEL => "员工权限",
                            Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                            CheckGroup::COUNT_PER_LINE => 1,
                            Form::FIELD_DATA =>  $power,
                        ],
                    HouseMember::PART_ID =>
                        [
                            Form::FIELD_LABEL => "所在档口",
                            Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                            CheckGroup::COUNT_PER_LINE => 1,
                            Form::FIELD_DATA =>  $partData,
                        ],
                    HouseMember::PHONE1 => [Form::FIELD_REQUIRED => true] ,
                    'vip_no' =>
                        [
                            Form::FIELD_LABEL => "会员号",
                            Form::FIELD_TAG => Form::COM_INPUT,
                            Form::FIELD_CLASS => "input-xlarge",
                            Form::FIELD_REQUIRED => true,
                        ],
                ];

        }

        $form = Form::fromModelMetadata( HouseMember::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '业主认证';
        $form->ajaxForm = true;

        $successMessage     = '认证成功';
        $form->submitAction = "wbtAPI.call('../fcrm/house/check_procurement?house_member_id={$id}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons(
            [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );

        echo $form;
        echo SimpleComponent::$scripts;
    }
    //录入并认证
    public function addCheckProcurementAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->getQueryParam( TopDirectory::COMMUNITY_ID );
        $power =  ProcurementPowerType::getDictionary();

        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $industry = $mpUser->getIndustry();
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityType = $community->getCommunityType();
        $memberType = HouseMemberType::getDictionary();
        if($industry != IndustryType::PROCUREMENT)
        {
            $memberType = array_slice($memberType,0,5);
        }
        else
        {
            if($communityType == CommunityType::PROCUREMENT_SUPPLY)
            {
                $memberType = array_slice($memberType,6,2);
            }
            else
            {
                $memberType = array_slice($memberType,6);
            }

        }
        $part =  Part::fetchRows(['*'],[Part::COMMUNITY_ID => $communityId]);
        $partData = [];
        foreach($part as $key => $value)
        {
            $partData[$value[Part::TITLE]."_".$value[Part::PART_ID]] = $value[Part::TITLE];
        }
        if($communityType != CommunityType::PROCUREMENT_RESTAURANT)
        {
            $fields   = [
                HouseMember::NAME ,
                HouseMember::MEMBER_TYPE => [ Form::FIELD_LABEL => "用户类型" ,
                    Form::FIELD_DATA => $memberType, ],
                HouseMember::PROCUREMENT_POWER_TYPE =>
                    [
                        Form::FIELD_LABEL => "员工权限",
                        Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                        CheckGroup::COUNT_PER_LINE => 1,
                        Form::FIELD_DATA =>  $power,
                    ],
                HouseMember::PHONE1  => [Form::FIELD_REQUIRED => true],
                HouseMember::PHONE2,
                HouseMember::PHONE3,
                HouseMember::COMMENT,
                'vip_no' =>
                    [
                        Form::FIELD_LABEL => "会员号",
                        Form::FIELD_TAG => Form::COM_INPUT,Form::FIELD_CLASS => "input-xlarge",
                        Form::FIELD_REQUIRED => true,
                    ],

            ];

        }
        else
        {
            $fields   = [
                HouseMember::NAME ,
                HouseMember::MEMBER_TYPE => [ Form::FIELD_LABEL => "用户类型" ,
                    Form::FIELD_DATA => $memberType, ],
                HouseMember::PROCUREMENT_POWER_TYPE =>
                    [
                        Form::FIELD_LABEL => "员工权限",
                        Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                        CheckGroup::COUNT_PER_LINE => 1,
                        Form::FIELD_DATA =>  $power,
                    ],
                HouseMember::PART_ID =>
                    [
                        Form::FIELD_LABEL => "所在档口",
                        Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                        CheckGroup::COUNT_PER_LINE => 1,
                        Form::FIELD_DATA =>  $partData,
                    ],
                HouseMember::PHONE1  => [Form::FIELD_REQUIRED => true],
                HouseMember::PHONE2,
                HouseMember::PHONE3,
                HouseMember::COMMENT,
                'vip_no' =>
                    [
                        Form::FIELD_LABEL => "会员号",
                        Form::FIELD_TAG => Form::COM_INPUT,Form::FIELD_CLASS => "input-xlarge",
                        Form::FIELD_REQUIRED => true,
                    ],

            ];

        }

        $form = Form::fromModelMetadata( HouseMember::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '录入用户并认证';
        $form->ajaxForm = true;

        $successMessage     = '添加并认证成功';
        $form->submitAction = "wbtAPI.call('../fcrm/house/insert_check_procurement?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,
        function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";


        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

}
