<?php

namespace WBT\Controller;

use Common\Data\Event;
use WBT\Model\Weibotui\User;
use WBT\Business\UserBusiness;
use WBT\Business\AuthBusiness;

use Bluefin\HTML\Form;
use Bluefin\HTML\Button;

class RegisterController extends WBTControllerBase
{
    public function indexAction()
    {
        AuthBusiness::logout();

        //表单的字段
        $fields = [
            '_from' => [
                Form::FIELD_TAG => Form::COM_HIDDEN,
                Form::FIELD_VALUE => $this->_requestSource
            ],
            '_guide' => [
                Form::FIELD_TAG => Form::COM_CUSTOM,
                Form::FIELD_VALUE => <<<'HTML'
            <div class="progress">
                <div class="bar bar-info" style="width: 33%;">第1步：填写信息</div>
                <div class="bar" style="width: 33%;">第2步：验证邮箱</div>
                <div class="bar" style="width: 34%;">第3步：注册成功</div>
            </div>
HTML
            ],
            'username' => [
                Form::FIELD_LABEL_ICON => 'icon-envelope',
                Form::FIELD_LABEL => '用户帐号',
                Form::FIELD_ALT_NAME => _DICT_('email'),
                'autocomplete' => 'off'
            ],
            'password' => [
                Form::FIELD_LABEL_ICON => 'icon-asterisk',
                Form::FIELD_LABEL => '密码',
                Form::FIELD_ALT_NAME => '密码',
                Form::FIELD_CONFIRM => true,
                Form::FIELD_INLINE => true,
                'autocomplete' => 'off'
            ],
            '_eula' => [
                Form::FIELD_LABEL => _DICT_('eula'),
                Form::FIELD_LABEL_ICON => 'icon-file',
                Form::FIELD_TAG => Form::COM_TEXT_AREA,
                Form::FIELD_ID => 'textEula',
                Form::FIELD_EXCLUDED => true,
                Form::FIELD_MESSAGE => <<<'HTML'
&nbsp;&nbsp;<i class="icon-info-sign"></i><a href="javascript:bluefinBH.ajaxDialog('/register/eula', {closeButton: true});">点击全文阅读</a>
HTML
            ,
                'style' => "width: 97%;",
                'rows' => "5",
                'readonly'
            ],
            '_checkEula' => [
                Form::FIELD_TAG => Form::COM_CHECK_BOX,
                Form::FIELD_ID => 'checkboxEula',
                Form::FIELD_LABEL => '我同意以上协议',
            ]
        ];

        if ($this->_request->isPost())
        {
            try
            {
                $inputs = Form::filterFormInputs(User::s_metadata(), $fields, $this->_request->getPostParams());

                $username = array_try_get($inputs, 'username');
                $password = array_try_get($inputs, 'password');


                UserBusiness::registerWeibotui($username, $password);

                $this->_gateway->redirect($this->_signUrl('register/verify_email', ['email' => $username], true));
            }
            catch (\Bluefin\Exception\InvalidRequestException $e)
            {
                $this->_view->set('_eventMessage', $e->getMessage());
                $this->_view->set('_eventAlertClass', 'alert-error');
            }
        }

        $form = Form::fromModelMetadata(
            User::s_metadata(),
            $fields,
            $this->_request->getPostParams()
        );

        $form->legend = '<h4 class="status-title">注册微博推帐号</h4>';
        $form->bodyScript = <<<'JS'
            function checkEula() {
                if ($('#checkboxEula').attr('checked') == 'checked')
                {
                    $('#buttonSubmit').removeAttr('disabled');
                }
                else
                {
                    $('#buttonSubmit').attr('disabled', 'disabled');
                }
            }
JS;
        $form->initScript = <<<'JS'
            $.get('/register/eula', function(data) {
                $('#textEula').text($(data).text());
            });
            $('#checkboxEula').click(checkEula);
            checkEula();
JS;

        //设置表单按钮
        $form->addButtons([
            new Button('注册', null, ['id' => 'buttonSubmit', 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success']),
            new Button('取消', null, ['class' => 'btn-cancel']),
        ]);

        $this->_view->set('form', (string)$form);
    }

    public function eulaAction()
    {
    }

    public function verifyEmailAction()
    {
        $email = $this->getRequest()->getQueryParam('email');

        if (isset($email))
        {
            $this->_requireSignedRequest();
            $this->_view->set('email', $email);
            return;
        }

        log_debug("[email:$email]");


        $token = $this->getRequest()->getQueryParam('token');
        if (!isset($token))
        {
            throw new \Bluefin\Exception\InvalidRequestException();
        }

        log_debug("[token:$token]");

        $eventCode = UserBusiness::activateUser($token);
        if (Event::getEventLowerCode($eventCode) == Event::S_ACTIVATE_SUCCESS)
        {
            AuthBusiness::refreshLoggedInProfile();
            $this->_view->set('succeeded', true);
            return;
        }

        $this->_view->set('_eventMessage', Event::getMessage($eventCode));
    }


}