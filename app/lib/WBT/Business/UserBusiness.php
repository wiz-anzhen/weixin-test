<?php

namespace WBT\Business;

use Bluefin\App;
use WBT\Model\Weibotui\User;
use WBT\Model\Weibotui\Tuike;
use WBT\Model\Weibotui\UserRole;
use WBT\Model\Weibotui\UserWithRole;
use WBT\Model\Weibotui\UserStatus;
use WBT\Model\Weibotui\Weibo;
use WBT\Model\Weibotui\PersonalProfile;
use WBT\Model\Weibotui\UserDepositRecord;
use WBT\Model\Weibotui\UserExpenseRecord;
use WBT\Model\Weibotui\UserIncomeRecord;
use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\CommunityAdminPowerType;
use MP\Model\Mp\SuperAdmin;
use MP\Model\Mp\MpAdmin;
use MP\Model\Mp\CompanyAdmin;
use Common\Data\Event;

class UserBusiness
{

    /**
     * [REVIEWED]
     * @param null $sessionID
     * @return array|null
     */
    public static function getUserProfileFromSession($sessionID = null)
    {
        if (isset($sessionID))
        {
            $sessionData = App::getInstance()->cache('session')->get($sessionID);
            session_decode($sessionData);
        }

        $userProfile = App::getInstance()->session('auth.weibotui')->get();

        return self::extractCommonUsedUserProfiles($userProfile);
    }

    /**
     * [REVIEWED]
     * @param array $userProfile
     * @return array|null
     */
    public static function extractCommonUsedUserProfiles(array $userProfile)
    {
        if (empty($userProfile))
        {
            return null;
        }

        $profile = array_get_all($userProfile, [
            'user_id', 'username', 'preferences',
            'profile_nick_name', 'profile_display_name',
            'profile_photo', 'profile_avatar',
            'profile_gender', 'profile_homepage',
            'profile_description',
        ]);

        $profile['preferences'] = json_decode($profile['preferences'], true);

        return $profile;
    }


    /**
     * [REVIEWED]
     * @param $email
     */
    public static function sendVerificationEmail($email)
    {
        $user = new User([User::USERNAME => $email]);
        _NON_EMPTY($user);

        if ($user->getStatus() !== UserStatus::NONACTIVATED)
        {
            return;
        }

        $token = \Common\Helper\UniqueIdentity::generate(32);
        App::getInstance()->cache('l1')->set('uat:' . $token, $user->getUserID(), 24*3600);

        $activateUrl =  _U("register/verify_email?token={$token}");
        $subject = '微博推（www.weibotui.com）用户邮件地址确认';
        $content = "尊敬的微博推用户<hr>欢迎您注册成为微博推(www.weibotui.com)的用户。<br>请您在24小时内点击以下地址来激活您的帐户：<br><a href='{$activateUrl}'>$activateUrl</a><br>如果以上链接无法点击，请将它复制到浏览器的地址栏中打开。<br>如果您有任何问题，请通过<a href='http://e.weibotui.com/weibotui'>@微博推</a> 与我们联系。<br>感谢您的支持！";

        log_debug($content);
        MailBusiness::sendMail($email, $subject, $content);
    }

    /**
     * [REVIEWED]
     * @static
     * @param $token 用户令牌
     * @return bool  返回ture表示正常结果，false表示激活失败
     * @throws \Exception
     */
    public static function activateUser($token)
    {
        $userId = App::getInstance()->cache('l1')->get('uat:' . $token);
        App::getInstance()->cache('l1')->remove('uat:' . $token);

        if (!isset($userId))
        {
            return Event::error(Event::SRC_REG, Event::E_INVALID_TOKEN);
        }

        $db = App::getInstance()->db('weibotui')->getAdapter();
        $db->beginTransaction();

        try
        {
            // 激活用户
            $user = new User([User::USER_ID => $userId]);
            _NON_EMPTY($user);

            if ($user->getStatus() === UserStatus::ACTIVATED)
            {
                $db->rollback();
                return Event::info(Event::SRC_REG, Event::I_ACCOUNT_ALREADY_ACTIVATED);
            }

            if ($user->getStatus() === UserStatus::DISABLED)
            {
                $db->rollback();
                return Event::error(Event::SRC_AUTH, Event::E_ACCOUNT_DISABLED);
            }

            $user->setStatus(UserStatus::ACTIVATED);
            $user->setActivatedTime(time());
            $user->save();

            $profile = new PersonalProfile($user->getProfile());
            if ($profile->getEmail() == $user->getUsername())
            {
                $profile->setEmailVerified(true)->save();
            }

            $db->commit();
        }
        catch (\Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        return Event::success(Event::SRC_REG, Event::S_ACTIVATE_SUCCESS);
    }

    public static function isUserExists($username)
    {
        $user = new User([User::USERNAME => $username]);
        return !$user->isEmpty();
    }

    /**
     * [REVIEWED]
     * @param $username
     * @param $password
     * @param $lastName
     * @param $firstName
     * @throws \Exception
     */
    public static function registerWeibotui($username, $password)
    {
        $db = App::getInstance()->db('weibotui')->getAdapter();
        $db->beginTransaction();

        try
        {
            $user = new User([User::USERNAME => $username]);
            if (!$user->isEmpty())
            {
                throw new \Bluefin\Exception\DataException(_APP_('Duplicate entry "%value%" as [%name%].', [  '%name%' => _META_('weibotui.user.username'), '%value%' => $username ]));
            }

            $user->setUsername($username)
                ->setPassword($password)
                ->insert();

            $profile = new PersonalProfile();
            $profile->setUser($user->getUserID())
                ->setDisplayName($username)
                ->setEmail($username)
                ->insert();

            $user->setProfile($profile->getPersonalProfileID())
                ->update();

            $db->commit();
        }
        catch (\Exception $e)
        {
            $db->rollback();

            throw $e;
        }

        AuthBusiness::login($user->getUserID());

        self::sendVerificationEmail($username);
    }

    /**
     * 绑定微博到微博推账号。
     * [REVIEWED]
     *
     * @param $userID
     * @param $type
     * @param $weiboUID
     * @return \WBT\Model\Weibotui\Weibo
     * @throws \Exception
     */
    public static function bindWeiboToWeibotui($userID, $type, $weiboUID)
    {
        $db = App::getInstance()->db('weibotui')->getAdapter();
        $db->beginTransaction();

        try
        {
            $weibo = new Weibo([Weibo::TYPE => $type, Weibo::UID => $weiboUID]);
            _NON_EMPTY($weibo);

            $weibo->setUser($userID);
            $weibo->save();

            $userWithRole = new UserWithRole();
            $userWithRole->setUser($userID)
                ->setRole(UserRole::SN_USER)
                ->insert(true);

            $db->commit();
        }
        catch (\Exception $e)
        {
            $db->rollback();

            throw $e;
        }

        return $weibo;
    }

    /**
     * 通过新浪微博注册微博推账号。
     * [REVIEWED]
     *
     * @param $username 用户名
     * @param $password 密码
     * @param $type
     * @param $weiboUID 微博ID
     * @return \WBT\Model\Weibotui\Weibo
     * @throws \Bluefin\Exception\InvalidRequestException
     * @throws \Exception
     */
    public static function registerWeibotuiFromSocial($username, $password, $type, $weiboUID)
    {
        $db = App::getInstance()->db('weibotui')->getAdapter();
        $db->beginTransaction();

        try
        {
            $weibo = new Weibo([Weibo::TYPE => $type, Weibo::UID => $weiboUID]);
            _NON_EMPTY($weibo);

            $originalUser = $weibo->getUser();
            if (isset($originalUser))
            {
                throw new \Bluefin\Exception\InvalidRequestException(
                    _APP_('The social networking account has already bound to a weibotui account.')
                );
            }

            $user = new User();
            $user->setUsername($username)
                ->setPassword($password)
                ->setPreferences(json_encode_cn(['home_role' => UserRole::SN_USER]))
                ->insert();

            $profile = new PersonalProfile();
            $profile->setUser($user->getUserID())
                ->setNickName($weibo->getDisplayName())
                ->setAvatar($weibo->getAvatarS())
                ->setPhoto($weibo->getAvatarL())
                ->setHomepage($weibo->getUrl())
                ->setGender($weibo->getGender())
                ->setEmail($username)
                ->setDescription($weibo->getDescription())
                ->insert();

            $user->setProfile($profile->getPersonalProfileID())
                ->update();

            $weibo->setUser($user->getUserID())
                ->update();

            $userWithRole = new UserWithRole();
            $userWithRole->setUser($user->getUserID())
                ->setRole(UserRole::SN_USER)
                ->insert();

            $db->commit();
        }
        catch (\Exception $e)
        {
            $db->rollback();

            throw $e;
        }

        AuthBusiness::login($user->getUserID());

        self::sendVerificationEmail($username);

        return $weibo;
    }

    public static function isPasswordRight($username, $password)
    {
        $user = new User([User::USERNAME => $username]);

        if ($user->isEmpty())
        {
            return false;
        }

        $tmpUser = new User();
        $tmpUser->reset($user->data(), true);
        $tmpUser->setPassword($password);

        $record = $tmpUser->filter();

        if ($user->getPassword() != $record['password'])
        {
            return false;
        }
        return true;
    }

    /**
     * @static 修改用户密码
     * @param $username
     * @param $password
     * @return bool 返回true表示设置成功，返回false表示设置失败
     */
    public static function changeUserPassword($username, $password)
    {
        $user = new User([User::USERNAME => $username]);
        if ($user->isEmpty())
        {
            return false;
        }

        $user->setPassword($password)->save();
        return true;
    }

    public static function getLoginUsername()
    {
        $username = null;
        try
        {
            $weibotuiAuth = App::getInstance()->auth('weibotui');

            $username =  $weibotuiAuth->getData('username');
        }
        catch(\Exception $e)
        {
            $username = null;
        }
        return $username;
    }

    public static function getLoginUserID()
    {
        $userID = null;
        try
        {
            $weibotuiAuth = App::getInstance()->auth('weibotui');

            $userID =  $weibotuiAuth->getData('user_id');
        }
        catch(\Exception $e)
        {
            $userID = null;
        }
        return $userID;
    }



    public static function getLoginUser()
    {
        $username = self::getLoginUsername();
        return new User([User::USERNAME => $username]);
    }


    public static function isAdvertiser()
    {
        $roles = App::getInstance()->role('weibotui')->get();
        return in_array(UserRole::ADVERTISER, $roles);
    }

    public static function isTuike()
    {
        $roles = App::getInstance()->role('weibotui')->get();
        return in_array(UserRole::TUIKE, $roles);
    }

    public static function getUserAsset($userID)
    {
        $userAsset = new \WBT\Model\Weibotui\UserAsset([\WBT\Model\Weibotui\UserAsset::USER =>  $userID]);
        return $userAsset->data();
    }

    public static function getUserDepositRecordList($userID, array $condition, array &$paging = null, array &$outputColumns = null)
    {
        //过滤查询条件
        $condition = array_get_all($condition, [UserDepositRecord::STATUS, UserDepositRecord::UNPAID_TIME]);
        $condition[UserDepositRecord::USER] = $userID;

        $selection = ['*', 'transaction.*'];

        return UserDepositRecord::fetchRowsWithCount(
            $selection,
            $condition,
            null,
            [UserDepositRecord::UNPAID_TIME => true],
            $paging,
            $outputColumns
        );
    }

    public static function getUserExpenseRecordList($userID, array $condition, array &$paging = null, array &$outputColumns = null)
    {
        //过滤查询条件
        $condition = array_get_all($condition, [UserExpenseRecord::STATUS, UserExpenseRecord::PENDING_TIME]);
        $condition[UserExpenseRecord::USER] = $userID;

        $selection = ['*'];

        return UserExpenseRecord::fetchRowsWithCount(
            $selection,
            $condition,
            null,
            [UserExpenseRecord::PENDING_TIME => true],
            $paging,
            $outputColumns
        );
    }

    public static function getUserIncomeRecordList($userID, array $condition, array &$paging = null, array &$outputColumns = null)
    {
        //过滤查询条件
        // bugtofix : UserIncomeRecord::STATUS  未定义
        $condition = array_get_all($condition, [UserIncomeRecord::STATUS, UserDepositRecord::UNPAID_TIME]);
        $condition[UserDepositRecord::USER] = $userID;

        $selection = ['*', 'transaction.*'];

        return UserDepositRecord::fetchRowsWithCount(
            $selection,
            $condition,
            null,
            [UserDepositRecord::UNPAID_TIME => true],
            $paging,
            $outputColumns
        );
    }
    public static function checkPower($userName,$communityID,$mpUserID)
    {
        //管理权限
        $powerType = CommunityAdminPowerType::getDictionary();
        $powerArr = [];
        foreach($powerType as $key => $value)
        {
            $powerArr[] = $key;
        }
        $superAdmin = new SuperAdmin([ SuperAdmin::USERNAME => $userName ]);
        $communityIdArray = CommunityAdmin::fetchColumn(CommunityAdmin::COMMUNITY_ID,[CommunityAdmin::USERNAME => $userName]);
        //如果是超级管理员或公共账号管理员可以拥有所有权限
        if (!$superAdmin->isEmpty())
        {
            $powerArr['user'] = "super_admin";
            return $powerArr;
        }
        elseif (MpAdmin::fetchCount([MpAdmin::MP_USER_ID => $mpUserID,
                                     MpAdmin::USERNAME => UserBusiness::getLoginUsername()]) > 0)
        {
            $powerArr['user'] = "mp_admin";
            return $powerArr;
        }
        elseif (CompanyAdmin::fetchCount([CompanyAdmin::MP_USER_ID => $mpUserID,
                CompanyAdmin::USERNAME => UserBusiness::getLoginUsername()]) > 0)
        {
            $companyAdmin = new CompanyAdmin([CompanyAdmin::USERNAME=>$userName,CompanyAdmin::MP_USER_ID=>$mpUserID]);
            $power = $companyAdmin->getPower();
            $powerArr = explode(',',$power);
            $newPowerArr = [];
            foreach($powerArr as $val)
            {
                if(!empty($val))
                {
                    $newPowerArr[] = $val."_r";
                    $newPowerArr[] = $val."_rw";
                    $newPowerArr[] = $val."_d";
                    $newPowerArr[] = $val;
                }

            }
            $newPowerArr['user'] = "company_admin";
            return $newPowerArr;
        }
        elseif(in_array($communityID ,$communityIdArray))
        {
            //小区管理员权限判断
            $communityAdmin = new CommunityAdmin([CommunityAdmin::USERNAME => $userName,CommunityAdmin::COMMUNITY_ID => $communityID]);
            $power  = $communityAdmin->getPower();
            $powerArr = explode(',', $power);
            $powerArr['user'] = "community_admin";
            return $powerArr;
        }
        else
        {
            $powerArr['user'] = "no_admin";
            return $powerArr;
        }

    }

}
