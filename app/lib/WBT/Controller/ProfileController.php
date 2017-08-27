<?php

namespace WBT\Controller;

use Bluefin\Exception\InvalidRequestException;
use Bluefin\HTML\Button;
use Bluefin\HTML\Form;
use Common\Helper\BaseController;
use WBT\Business\AuthBusiness;
use WBT\Business\UserBusiness;
use WBT\Model\Weibotui\User;

class ProfileController extends BaseController
{
    public function pwdAction() {
        $userName = UserBusiness::getLoginUser()->getUsername();
        $this->_view->set('username', $this->getSimpleUsername($userName));

        //表单的字段
        $fields = [
            '_from' => [
                Form::FIELD_TAG => Form::COM_HIDDEN,
                Form::FIELD_VALUE => $this->_requestSource
            ],
            'current_password' => [
                Form::FIELD_LABEL => '当前密码',
                Form::FIELD_LABEL_ICON => 'icon-asterisk',
                Form::FIELD_TAG => Form::COM_INPUT,
                Form::FIELD_TYPE => 'password',
                Form::FIELD_REQUIRED => true,
                Form::FIELD_CLASS => 'input-medium',
                Form::FIELD_INLINE => false,
                'autocomplete' => 'off'
            ],
            'password' => [
                Form::FIELD_LABEL => '新密码',
                Form::FIELD_ALT_NAME => '新密码',
                Form::FIELD_LABEL_ICON => 'icon-asterisk',
                Form::FIELD_CLASS => 'input-medium',
                Form::FIELD_CONFIRM => true,
                Form::FIELD_INLINE => true,
                'autocomplete' => 'off'
            ]
        ];

        $form = Form::fromModelMetadata(
            User::s_metadata(),
            $fields,
            $this->_request->getPostParams()
        );

        $form->showCloseBtn = false;

        $form->addButtons([
            new Button('修改', null, ['id' => 'buttonSubmit', 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success']),
            new Button('清除', null, ['class' => 'btn-cancel', 'type' => 'reset']),
        ]);

        $this->_view->set('form', (string)$form);


        if($this->_request->isPost())
        {
            try
            {
                $inputs = Form::filterFormInputs(User::s_metadata(), $fields, $this->_request->getPostParams());

                $currentPassword = array_try_get($inputs, 'current_password');
                $password = array_try_get($inputs, 'password');
                $passwordConfirm = $this->_request->get('password_confirm');
                if( UserBusiness::isPasswordRight($userName, $currentPassword) )
                {
                    if( $password === $passwordConfirm )
                    {
                        UserBusiness::changeUserPassword($userName, $password);
                        $this->_view->set('_eventMessage', "修改成功");
                        $this->_view->set('_eventAlertClass', 'alert-success');
                    }
                    else
                    {
                        $this->_view->set('_eventMessage', "两次输入的密码不一致，请重新设置");
                        $this->_view->set('_eventAlertClass', 'alert-error');
                    }
                }
                else
                {
                    $this->_view->set('_eventMessage', "原密码不正确");
                    $this->_view->set('_eventAlertClass', 'alert-error');
                    return;
                }
            }
            catch(InvalidRequestException $e)
            {
                $this->_view->set('_eventMessage', $e->getMessage());
                $this->_view->set('_eventAlertClass', ' alert-error');
            }
        }
    }

    protected function getSimpleUsername($username)
    {
        $pos = strpos($username,'@');
        if($pos)
        {
            return substr($username,0,$pos);
        }

        return $username;
    }
}
