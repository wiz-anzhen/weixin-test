<?php

use MP\Model\Mp\WxUser;
use WBT\Business\MailBusiness;

require_once 'ServiceBase.php';

class SettingService extends ServiceBase
{
    public function wxUser()
    {
        $wxUserId = $this->_app->request()->get('wx_user_id');
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);
        if ($wxUser->isEmpty()) {
            return ['errno' => 1, 'error' => '找不到用户'];
        }

        $field = $this->_app->request()->get('field');
        $value = $this->_app->request()->get('value');
        $error = '';
        switch($field) {
            case 'idiograph':
                $wxUser->setIdiograph($value);
                break;
            case 'address';
                $wxUser->setAddress($value);
                break;
            case 'nick';
                $wxUser->setNick($value);
                break;
            case 'card_id':
                $wxUser->setCardID($value);
                break;
            case 'phone':
                $wxUser->setPhone($value);
                break;
            case 'email':
                $uncertifiedEmail = _C('config.uncertified_email_prefix') . $value;
                $wxUser->setEmail($uncertifiedEmail);
                $error = "邮箱设置成功！\n\n为确保您的邮箱真实有效，系统已向{$value}发送一封确认邮件，请登陆邮箱，点击确认链接，完成最后一步，谢谢。";
                break;
            default:
                return ['errno' => 1, 'error' => '找不到该字段'];
                break;
        }

        try {
            $wxUser->update();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }

        if ($field == 'email') {
            $this->sendCertifyEmail($value);
        }

        return ['errno' => 0, 'error' => $error];
    }

    private function sendCertifyEmail($to)
    {
        $subject = '验证用于城市展望物业微信的电子邮件地址';
        $wxUserId = $this->_app->request()->get('wx_user_id');
        $host = get_host();
        $date = date('Y-m-d H:i:s');
        $htmlContent = <<<EOF
{$to}，您好！


感谢您使用城市展望物业微信公众号！

若要使用和电子邮件相关的功能，需要确认这是您的电子邮件地址。这可以帮助我们阻止自动化程序创建帐户并发送垃圾邮件。

使用此链接验证帐户：

{$host}/external/certify_email/confirm?wx_user_id={$wxUserId}&urlnum=0

如果您没有在城市展望物业微信公众号使用自己的电子邮件地址，请使用此链接取消帐户：

{$host}/external/certify_email/confirm?wx_user_id={$wxUserId}&urlnum=1


谢谢！

城市展望物业 尊重您的隐私。若要了解详细信息，请阅读我们的联机隐私声明，网址为：http://www.kingcores.com/

金果创新（北京）科技有限公司

{$date}
EOF;
        $htmlContent = str_replace("\n", "<br/>", $htmlContent);

        MailBusiness::sendMailAsyn($to, $subject, $htmlContent);
    }

    public function headPic()
    {
        $wxUserId = $this->_app->request()->get('wx_user_id');
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);
        if ($wxUser->isEmpty()) {
            return ['errno' => 1, 'error' => '找不到用户'];
        }

        try {
            $wxUser->setHeadPic($wxUser->getLastPic())->update();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }

    public function weixinQrcode()
    {
        $wxUserId = $this->_app->request()->get('wx_user_id');
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);
        if ($wxUser->isEmpty()) {
            return ['errno' => 1, 'error' => '找不到用户'];
        }

        try {
            $wxUser->setWeixinQrcode($wxUser->getLastPic())->update();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }

    public function gender()
    {
        $wxUserId = $this->_app->request()->get('wx_user_id');
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);
        if ($wxUser->isEmpty()) {
            return ['errno' => 1, 'error' => '找不到用户'];
        }

        $value = $this->_app->request()->get(WxUser::GENDER);
        if (!in_array($value, ['male', 'female'])) {
            return ['errno' => 1, 'error' => '非法的值'];
        }

        try {
            $wxUser->setGender($value)->update();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }

    public function receiveWeatherForecast()
    {
        $wxUserId = $this->_app->request()->get('wx_user_id');
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);

        if ($wxUser->isEmpty()) {
            return ['errno' => 1, 'error' => '找不到用户'];
        }

        $flag = $this->_app->request()->get('flag');
        $flag = $flag == 1 ? 1 : 0;

        $wxUser->setReceiveWeatherForcast($flag)->update();

        return ['errno' => 0, 'receive_weather_forecast' => $wxUser->getReceiveWeatherForcast()];
    }
}