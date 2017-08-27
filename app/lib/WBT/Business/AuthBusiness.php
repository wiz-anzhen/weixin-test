<?php

namespace WBT\Business;

use Bluefin\App;
use WBT\Model\Weibotui\UserWithRole;
use WBT\Model\Weibotui\UserLoginRecord;
use WBT\Model\Weibotui\LoginType;
use WBT\Model\Weibotui\OAuthToken;
use WBT\Model\Weibotui\User;

class AuthBusiness
{

    /**
     * 通过微博推账号登录系统。
     *
     * @param $postOrUserID
     * @param string $loginType
     * @return int
     */
    public static function login($postOrUserID, $loginType = LoginType::WEIBOTUI)
    {
        self::logout();

        $auth = App::getInstance()->auth('weibotui');

        if (is_array($postOrUserID))
        {
            $flag = $auth->authenticate($postOrUserID);

            if ($flag !== \Bluefin\Auth\AuthHelper::SUCCESS)
            {
                return $flag;
            }
        }
        else
        {
            $auth->setIdentity([ 'user_id' => $postOrUserID ]);
        }


        self::recordWeibotuiLogin($loginType);

        return \Bluefin\Auth\AuthHelper::SUCCESS;
    }

    /**
     * 保存微博推账号登录记录。
     * @param $sourceType 登录来源类型，可选值来自[WBT\Model\Weibotui\LoginType]。
     */
    public static function recordWeibotuiLogin($sourceType)
    {
        $userLoginRecord = new UserLoginRecord();
        $userLoginRecord->setType($sourceType);
        $userLoginRecord->insert();
    }

    public static function isLoggedIn()
    {
        return App::getInstance()->session('auth.weibotui')->has('user_id');
    }

    public static function getLoggedInUserId()
    {
        return App::getInstance()->session('auth.weibotui')->get('user_id');
    }

    public static function refreshLoggedInProfile()
    {
        $auth = App::getInstance()->auth('weibotui');
        $auth->refresh();
    }

    public static function refreshLoggedInUserRoles()
    {
        $auth = App::getInstance()->auth('weibotui');
        App::getInstance()->role('weibotui')->reset(UserWithRole::fetchColumn(UserWithRole::ROLE, [UserWithRole::USER => $auth->getUniqueID()]));
    }

    /**
     * 注销所有账号。
     */
    public static function logout()
    {
        App::getInstance()->auth('weibotui')->clearIdentity();
        App::getInstance()->role('weibotui')->clear();
    }
}
