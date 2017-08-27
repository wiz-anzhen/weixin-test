<?php

namespace WBT\Controller\External;

use Bluefin\Controller;
use MP\Model\Mp\WxUser;

class CertifyEmailController extends Controller
{
    public function confirmAction()
    {
        $wxUserId = $this->_request->get(WxUser::WX_USER_ID);
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);
        if ($wxUser->isEmpty()) {
            exit('找不到网页');
        }
        $email = $wxUser->getEmail();
        if (empty($email)) {
            exit('找不到网页');
        }
        $uncertifiedEmailPrefix = _C('config.uncertified_email_prefix');
        if (strpos( $email,$uncertifiedEmailPrefix) !== 0) {
            exit('找不到网页');
        }

        $urlnum = $this->_request->get('urlnum');
        if ($urlnum == 0) {
            $wxUser->setEmail(substr($wxUser->getEmail(), strlen($uncertifiedEmailPrefix)))->update();
            exit('验证成功，感谢您的使用.');
        } else {
            $wxUser->setEmail('')->update();
            exit('您的邮箱信息已被清除.');
        }
    }
}